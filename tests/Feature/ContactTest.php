<?php

declare(strict_types=1);

use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    Mail::fake();
    RateLimiter::clear('contact');
    $this->setting('contact', 'notification_email', 'inbox@example.com');
});

/** داده‌ی معتبر پایه */
function validContact(array $overrides = []): array
{
    return array_merge([
        'name' => 'سحر محمدی',
        'email' => 'sahar@example.com',
        'purpose' => 'job',
        'message' => 'سلام، درباره‌ی یک فرصت شغلی می‌خواستم صحبت کنم.',
    ], $overrides);
}

/* ── US-M5-1 · مسیر موفق ───────────────────────────────── */

it('پیام معتبر را ثبت و ایمیل اعلان ارسال می‌کند', function () {
    $response = $this->post('/contact', validContact());

    $response->assertRedirect(route('contact.show'))->assertSessionHas('contact_sent');

    $message = ContactMessage::query()->sole();
    expect($message->name)->toBe('سحر محمدی')
        ->and($message->email)->toBe('sahar@example.com')
        ->and($message->purpose->value)->toBe('job')
        ->and($message->mail_sent)->toBeTrue()
        ->and($message->ip)->not->toBeNull();

    Mail::assertSent(ContactMessageMail::class);
});

it('حالت موفقیت را پس از ارسال نشان می‌دهد', function () {
    $this->post('/contact', validContact())
        ->assertRedirect(route('contact.show'));

    $this->followingRedirects()
        ->post('/contact', validContact(['email' => 'other@example.com']))
        ->assertSeeText('پیام‌تان رسید');
});

/* ── US-M5-1 · اعتبارسنجی سرور ─────────────────────────── */

it('نام کوتاه‌تر از ۳ حرف را رد می‌کند', function () {
    $this->post('/contact', validContact(['name' => 'ح']))->assertSessionHasErrors('name');
    expect(ContactMessage::query()->count())->toBe(0);
});

it('ایمیل نامعتبر را رد می‌کند', function () {
    $this->post('/contact', validContact(['email' => 'not-an-email']))->assertSessionHasErrors('email');
});

it('پیام کوتاه‌تر از ۱۰ یا بلندتر از ۱۰۰۰ حرف را رد می‌کند', function () {
    $this->post('/contact', validContact(['message' => 'کوتاه']))->assertSessionHasErrors('message');
    $this->post('/contact', validContact(['message' => str_repeat('ا', 1001)]))->assertSessionHasErrors('message');
});

it('هدف تماس خارج از فهرست را رد می‌کند', function () {
    $this->post('/contact', validContact(['purpose' => 'hacking']))->assertSessionHasErrors('purpose');
});

/* ── US-M5-2 · دفاع ضداسپم ─────────────────────────────── */

it('honeypot پرشده را بی‌سروصدا رد می‌کند', function () {
    // D11: ربات باید «موفق» ببیند اما هیچ رکوردی ثبت نشود
    $this->post('/contact', validContact(['website' => 'https://spam.example']))
        ->assertRedirect(route('contact.show'))
        ->assertSessionHas('contact_sent');

    expect(ContactMessage::query()->count())->toBe(0);
    Mail::assertNothingSent();
});

it('بیش از ۵ ارسال در ساعت به‌ازای IP را محدود می‌کند', function () {
    foreach (range(1, 5) as $i) {
        $this->post('/contact', validContact(['email' => "u{$i}@example.com"]))
            ->assertSessionHasNoErrors();
    }

    // ششمین ارسال مسدود می‌شود — با پیام خوانا، نه صفحه‌ی خطای خام
    $this->from(route('contact.show'))
        ->post('/contact', validContact(['email' => 'u6@example.com']))
        ->assertRedirect(route('contact.show'))
        ->assertSessionHasErrors('message');

    expect(ContactMessage::query()->count())->toBe(5);
});

/* ── US-M5-3 · اطمینان تحویل ───────────────────────────── */

it('با شکست ارسال ایمیل، پیام در دیتابیس می‌ماند و ارسال‌نشده علامت می‌خورد', function () {
    Mail::shouldReceive('to->send')->andThrow(new RuntimeException('SMTP down'));

    $this->post('/contact', validContact())->assertSessionHas('contact_sent');

    $message = ContactMessage::query()->sole();
    expect($message->mail_sent)->toBeFalse();
});

it('بدون ایمیل اعلان، پیام ثبت می‌شود ولی ایمیلی نمی‌رود', function () {
    $this->setting('contact', 'notification_email', '');

    $this->post('/contact', validContact())->assertSessionHas('contact_sent');

    expect(ContactMessage::query()->sole()->mail_sent)->toBeFalse();
    Mail::assertNothingSent();
});

/* ── ورودی از کیس‌استادی ───────────────────────────────── */

it('purpose در نشانی، گزینه‌ی فرم را از پیش انتخاب می‌کند', function () {
    $this->get('/contact?purpose=service')
        ->assertOk()
        ->assertSee('value="service" selected', false);
});
