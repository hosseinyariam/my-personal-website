<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\StructuredData;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * وبلاگ (M6). زیرساخت در لانچ کامل است؛ محتوا در فاز ۸.۱ می‌آید.
 * دسترسی مستقیم به URL بدون پست = حالت خالیِ طراحی‌شده، نه ۴۰۴ (D08).
 */
class BlogController extends Controller
{
    public function __construct(private readonly StructuredData $schema) {}

    public function index(): View
    {
        $posts = Post::query()
            ->published()
            ->with('tags')
            ->latestFirst()
            ->paginate(9)
            ->withQueryString();

        return view('pages.blog.index', [
            'posts' => $posts,
            // یادداشت شاخص فقط در صفحه‌ی اول، و فقط اگر بیش از یک پست باشد
            'featured' => $posts->currentPage() === 1 && $posts->total() > 1 ? $posts->first() : null,
        ]);
    }

    public function show(Post $post): View
    {
        // پیش‌نویس و پستِ زمان‌بندی‌شده‌ی آینده عمومی نیستند (US-M6-2)
        if (! $post->isPublished()) {
            throw new NotFoundHttpException;
        }

        $post->load('tags');

        return view('pages.blog.show', [
            'post' => $post,
            'schema' => [
                $this->schema->article($post),
                $this->schema->breadcrumbs([
                    ['label' => 'خانه', 'url' => route('home')],
                    ['label' => 'وبلاگ', 'url' => route('blog.index')],
                    ['label' => $post->title, 'url' => route('blog.show', $post->slug)],
                ]),
            ],
            'related' => Post::query()
                ->published()
                ->whereKeyNot($post->getKey())
                ->latestFirst()
                ->limit(3)
                ->get(),
        ]);
    }
}
