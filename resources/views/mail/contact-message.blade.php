<x-mail::message>
# پیام جدید از فرم تماس

**نام:** {{ $contactMessage->name }}
**ایمیل:** {{ $contactMessage->email }}
**هدف تماس:** {{ $contactMessage->purpose->label() }}
**زمان:** {{ $contactMessage->created_at->format('Y-m-d H:i') }}

---

{{ $contactMessage->message }}

---

<x-mail::button :url="url('/admin')">
مشاهده در پنل مدیریت
</x-mail::button>

@if ($contactMessage->ip)
<small>IP: {{ $contactMessage->ip }}</small>
@endif
</x-mail::message>
