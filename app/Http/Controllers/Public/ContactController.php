<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Services\ContactService;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * تماس — نمایش کانال‌ها و دریافت پیام.
 *
 * ── قاعده‌ی سخت ──
 * دستورالعمل و IA:321: «هیچ شماره تماس، ایمیل، نشانی یا حساب اجتماعی
 * را حدس نزن». کانال‌ها فقط از جدول `contact_channels` می‌آیند و
 * فقط آن‌هایی که هم `is_active` و هم `is_public` هستند (scope public).
 * هیچ ایمیل یا لینکی در کد یا نما هاردکد نشده است.
 *
 * ── محافظت ──
 * سه لایه: honeypot (در FormRequest)، throttle (۵ در ساعت روی مسیر)،
 * و بررسی محتوای اسپم (در ContactService).
 */
class ContactController extends Controller
{
    public function show(SeoService $seo, ContactService $contact): View
    {
        $channels = $seo->publicContactChannels();

        // مهر زمانی باز شدن فرم برای تشخیص ربات (ارسال زیر ۲ ثانیه).
        // این مقدار در سشن نگه داشته می‌شود، نه در فرم — چون فیلد
        // مخفی در فرم قابل جعل است.
        session(['contact_form_opened_at' => now()->timestamp]);

        $meta = $seo->metaFor(
            'تماس',
            'راه‌های ارتباط حرفه‌ای — برای همکاری، پروژه یا گفت‌وگو درباره محصول.',
            route('contact.show')
        );

        return view('pages.contact', compact('meta', 'channels'));
    }

    public function store(ContactRequest $request, ContactService $contact): RedirectResponse
    {
        $result = $contact->submit(
            $request->validated(),
            $contact->hashIp($request->ip()) ?? '',
            $request->userAgent(),
            $request->isSpam()
        );

        // پیام اسپم به کاربر «موفق» نشان داده می‌شود ولی ثبت نمی‌شود.
        // دلیل: اگر به ربات بگوییم «رد شد»، الگویش را اصلاح می‌کند.
        // این رفتار عمدی است و در تست تأیید شده.
        return redirect()
            ->route('contact.show')
            ->with('contact_status', $result['spam'] ? 'spam' : 'sent');
    }
}
