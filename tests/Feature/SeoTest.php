<?php

declare(strict_types=1);

use App\Models\Platform;
use App\Models\Post;
use App\Models\SocialLink;

/* ── sitemap ───────────────────────────────────────────── */

it('نقشه‌ی سایت XML معتبر با صفحات ثابت می‌دهد', function () {
    $response = $this->get('/sitemap.xml')->assertOk();

    $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $xml = simplexml_load_string($response->getContent());
    expect($xml)->not->toBeFalse();

    // SimpleXMLElement باید صریح به آرایه تبدیل شود تا خواهرها پیمایش شوند
    $locs = collect(iterator_to_array($xml->url, false))->map(fn ($u) => (string) $u->loc);
    expect($locs)->toContain(route('home'), route('platforms.index'), route('resume.index'));
});

it('فقط پلتفرم‌های فعال وارد نقشه‌ی سایت می‌شوند', function () {
    Platform::factory()->create(['slug' => 'live-one']);
    Platform::factory()->inactive()->create(['slug' => 'hidden-one']);

    $content = $this->get('/sitemap.xml')->getContent();

    expect($content)->toContain(route('platforms.show', 'live-one'))
        ->not->toContain(route('platforms.show', 'hidden-one'));
});

it('بدون پست، نشانی وبلاگ به نقشه‌ی سایت اضافه نمی‌شود', function () {
    // URLای که حالت خالی نشان می‌دهد نباید به موتور جست‌وجو معرفی شود
    expect($this->get('/sitemap.xml')->getContent())->not->toContain(route('blog.index'));

    Post::factory()->create();

    expect($this->get('/sitemap.xml')->getContent())->toContain(route('blog.index'));
});

/* ── robots ────────────────────────────────────────────── */

it('robots.txt پنل را می‌بندد و نقشه‌ی سایت را معرفی می‌کند', function () {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Disallow: /admin', false)
        ->assertSee('Disallow: /analytics', false)
        ->assertSee('Sitemap: '.route('sitemap'), false);
});

/* ── JSON-LD ───────────────────────────────────────────── */

it('صفحه‌ی اصلی گراف Person معتبر می‌دهد', function () {
    $this->setting('profile', 'display_name', 'حسین یاری');
    $this->setting('profile', 'headline', 'توسعه‌دهنده');
    SocialLink::query()->create([
        'platform' => 'github', 'label' => 'گیت‌هاب',
        'url' => 'https://github.com/example', 'is_active' => true,
    ]);

    $graphs = jsonLdGraphs($this->get('/')->getContent());

    expect($graphs)->toHaveCount(1)
        ->and($graphs[0]['@type'])->toBe('Person')
        ->and($graphs[0]['name'])->toBe('حسین یاری')
        ->and($graphs[0]['jobTitle'])->toBe('توسعه‌دهنده')
        ->and($graphs[0]['sameAs'])->toContain('https://github.com/example');
});

it('mailto در sameAs گراف Person نمی‌آید', function () {
    SocialLink::query()->create([
        'platform' => 'email', 'label' => 'ایمیل',
        'url' => 'mailto:hi@example.com', 'is_active' => true,
    ]);

    $graphs = jsonLdGraphs($this->get('/')->getContent());

    expect($graphs[0])->not->toHaveKey('sameAs');
});

it('صفحه‌ی پلتفرم گراف SoftwareApplication و BreadcrumbList می‌دهد', function () {
    $platform = Platform::factory()->create([
        'slug' => 'vinora', 'title' => 'وینورا',
        'version' => '1.0.0', 'external_url' => 'https://vinora.example/',
    ]);

    $graphs = jsonLdGraphs($this->get("/platforms/{$platform->slug}")->getContent())[0];

    expect($graphs[0]['@type'])->toBe('SoftwareApplication')
        ->and($graphs[0]['name'])->toBe('وینورا')
        ->and($graphs[0]['softwareVersion'])->toBe('1.0.0')
        ->and($graphs[1]['@type'])->toBe('BreadcrumbList')
        ->and($graphs[1]['itemListElement'])->toHaveCount(3);
});

it('تک‌پست گراف Article می‌دهد', function () {
    $post = Post::factory()->create(['title' => 'یادداشت من']);

    $graphs = jsonLdGraphs($this->get("/blog/{$post->slug}")->getContent())[0];

    expect($graphs[0]['@type'])->toBe('Article')
        ->and($graphs[0]['headline'])->toBe('یادداشت من')
        ->and($graphs[0])->toHaveKey('datePublished');
});

/* ── متا و OG ──────────────────────────────────────────── */

it('هر صفحه canonical و متای اختصاصی دارد', function () {
    Platform::factory()->create(['slug' => 'vinora', 'title' => 'وینورا', 'tagline' => 'تگ‌لاین تست']);

    $this->get('/platforms/vinora')
        ->assertSee('<link rel="canonical" href="'.route('platforms.show', 'vinora').'">', false)
        ->assertSee('<meta property="og:type" content="article">', false)
        ->assertSee('تگ‌لاین تست', false);
});

it('صفحات دِو و پنل noindex می‌گیرند', function () {
    $this->get('/robots.txt')->assertSee('Disallow: /dev', false);
});

/* ── کمکی ──────────────────────────────────────────────── */

function jsonLdGraphs(string $html): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

    return collect($matches[1])
        ->map(fn (string $json) => json_decode(html_entity_decode($json), true, flags: JSON_THROW_ON_ERROR))
        ->all();
}
