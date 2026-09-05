<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * صفحه‌ی حسابرسی UI Kit — مرجع تطبیق بصری کامپوننت‌ها با فایل کیت (DoD فاز ۱).
 *
 * فقط در محیط محلی. محتوای این صفحه عمداً نمونه است؛ اینجا رابط کاربری
 * حسابرسی می‌شود، نه محتوای سایت — پس قاعده‌ی «محتوا از دیتابیس» شامل آن نیست.
 */
class DevKitController extends Controller
{
    public function __invoke(): View
    {
        // دفاع لایه‌ی دوم: حتی اگر مسیر به‌اشتباه ثبت شد، بیرون از local باز نمی‌شود.
        if (! app()->environment('local')) {
            throw new NotFoundHttpException;
        }

        return view('dev.kit');
    }
}
