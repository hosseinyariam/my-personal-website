<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * ویترین (M2) و کیس‌استادی هر پلتفرم (M3).
 */
class PlatformController extends Controller
{
    public function index(): View
    {
        $platforms = Cache::rememberForever(
            CacheKeys::PLATFORMS_ACTIVE,
            fn () => Platform::query()->active()->ordered()->get(),
        );

        return view('pages.platforms.index', [
            'platforms' => $platforms,
        ]);
    }

    public function show(Platform $platform): View
    {
        // رکورد غیرفعال از بیرون قابل مشاهده نیست — همان ۴۰۴ سفارشی (US-M3-3)
        if (! $platform->is_active) {
            throw new NotFoundHttpException;
        }

        $platform->load('activeScreenshots');

        return view('pages.platforms.show', [
            'platform' => $platform,
        ]);
    }
}
