<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContactPurpose;
use App\Http\Requests\ContactRequest;
use App\Services\ContactService;
use App\Services\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * تماس (M5) — فرم واحد با فیلد «هدف تماس» (D10).
 */
class ContactController extends Controller
{
    public function show(Request $request, Settings $settings): View
    {
        // CTA کیس‌استادی با ?purpose=service وارد می‌شود و گزینه از پیش انتخاب می‌ماند
        $purpose = ContactPurpose::tryFrom((string) $request->query('purpose'));

        return view('pages.contact', [
            'purposes' => ContactPurpose::options(),
            'selectedPurpose' => $purpose?->value ?? ContactPurpose::Other->value,
            'responseNote' => $settings->get('contact', 'response_note'),
        ]);
    }

    public function store(ContactRequest $request, ContactService $contacts): RedirectResponse
    {
        // honeypot پر شده = ربات. بی‌سروصدا «موفق» پاسخ می‌دهیم تا ربات
        // متوجه سازوکار نشود، اما هیچ چیزی ثبت نمی‌شود (D11).
        if (filled($request->input('website'))) {
            return $this->success();
        }

        $contacts->record(
            $request->safe()->only(['name', 'email', 'purpose', 'message']),
            $request->ip(),
            $request->userAgent(),
        );

        return $this->success();
    }

    private function success(): RedirectResponse
    {
        return redirect()
            ->route('contact.show')
            ->with('contact_sent', true);
    }
}
