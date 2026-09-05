<?php

declare(strict_types=1);

use App\Models\Post;

/* ── D08 · فعال‌سازی پویای وبلاگ ────────────────────────── */

it('بدون پست منتشرشده، لینک وبلاگ در ناوبری نیست', function () {
    $this->get('/')->assertOk()->assertDontSee(route('blog.index'), false);
});

it('با اولین پست منتشرشده، لینک وبلاگ ظاهر می‌شود', function () {
    Post::factory()->create();

    $this->get('/')->assertOk()->assertSee(route('blog.index'), false);
});

it('پیش‌نویس لینک ناوبری را فعال نمی‌کند', function () {
    Post::factory()->draft()->create();

    $this->get('/')->assertDontSee(route('blog.index'), false);
});

it('دسترسی مستقیم بدون پست، حالت خالی می‌دهد نه ۴۰۴', function () {
    $this->get('/blog')
        ->assertOk()
        ->assertSeeText('هنوز یادداشتی منتشر نشده است');
});

/* ── US-M6-2 · زمان‌بندی انتشار ─────────────────────────── */

it('پست زمان‌بندی‌شده‌ی آینده عمومی نمی‌شود', function () {
    $post = Post::factory()->scheduled()->create();

    $this->get('/blog')->assertDontSeeText($post->title);
    $this->get("/blog/{$post->slug}")->assertNotFound();
});

it('پیش‌نویس عمومی نیست', function () {
    $post = Post::factory()->draft()->create();

    $this->get("/blog/{$post->slug}")->assertNotFound();
});

it('پست منتشرشده با محتوا و زمان مطالعه رندر می‌شود', function () {
    $post = Post::factory()->create([
        'title' => 'معماری توکن',
        'content' => str_repeat('کلمه ', 360),
    ]);

    $this->get("/blog/{$post->slug}")
        ->assertOk()
        ->assertSeeText('معماری توکن')
        // ۳۶۰ کلمه ÷ ۱۸۰ = ۲ دقیقه، با ارقام فارسی
        ->assertSeeText('۲ دقیقه');
});

it('مارک‌داون را امن رندر می‌کند و HTML خام را حذف می‌کند', function () {
    // US-M6-1: رندر امن — هیچ اسکریپتی از محتوا به صفحه نمی‌رسد
    $post = Post::factory()->create([
        'content' => "## تیتر\n\nمتن **پررنگ**\n\n<script>alert('xss')</script>",
    ]);

    $response = $this->get("/blog/{$post->slug}")->assertOk();

    $response->assertSee('<h2>تیتر</h2>', false)
        ->assertSee('<strong>پررنگ</strong>', false)
        ->assertDontSee('<script>alert', false);
});

/* ── US-M6-3 · RSS ─────────────────────────────────────── */

it('خوراک RSS معتبر از پست‌های منتشرشده می‌دهد', function () {
    $published = Post::factory()->create(['title' => 'منتشرشده']);
    $draft = Post::factory()->draft()->create(['title' => 'پیش‌نویس']);

    $response = $this->get('/feed')->assertOk();

    $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSee('<rss version="2.0"', false)
        ->assertSee($published->title, false)
        ->assertDontSee($draft->title, false);

    // خروجی باید XML معتبر باشد
    expect(simplexml_load_string($response->getContent()))->not->toBeFalse();
});
