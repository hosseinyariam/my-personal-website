<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ContactPurpose;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * اعتبارسنجی فرم تماس — سمت سرور، طبق US-M5-1.
 */
class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:150'],
            'purpose' => ['required', Rule::enum(ContactPurpose::class)],
            'message' => ['required', 'string', 'min:10', 'max:1000'],

            // honeypot — فیلد مخفی که فقط ربات‌ها پر می‌کنند (D11).
            // اینجا فقط «باید خالی باشد» اعلام می‌شود؛ رفتار رد بی‌پیام در کنترلر است.
            'website' => ['nullable', 'prohibited'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'نام',
            'email' => 'ایمیل',
            'purpose' => 'هدف تماس',
            'message' => 'پیام',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نامتان را بنویسید.',
            'name.min' => 'نام باید حداقل ۳ حرف باشد.',
            'email.required' => 'ایمیل لازم است تا بتوانم پاسخ بدهم.',
            'email.email' => 'قالب ایمیل معتبر نیست.',
            'purpose.required' => 'هدف تماس را انتخاب کنید.',
            'message.required' => 'پیامتان را بنویسید.',
            'message.min' => 'پیام باید حداقل ۱۰ حرف باشد.',
            'message.max' => 'پیام حداکثر ۱۰۰۰ حرف باشد.',
        ];
    }
}
