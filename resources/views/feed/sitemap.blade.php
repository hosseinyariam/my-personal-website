{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
{{-- چرا رشته تکه‌تکه شده: اعلان XML باید در خط اول و بدون هیچ فاصله‌ی
     پیش از آن باشد، وگرنه فید نامعتبر می‌شود. اما اگر `<?xml` به‌صورت
     یک رشته‌ی یکپارچه در منبع بیاید و `short_open_tag` روشن باشد،
     توکنایزر Blade آن را به‌عنوان تگ باز PHP می‌بیند و کامپایل می‌شکند.
     پس «<?» با concat ساخته می‌شود تا در هر تنظیمی امن باشد. --}}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($entries as $entry)
    <url>
        {{-- مقادیر با htmlspecialchars امن escape می‌شوند: یک کاراکتر
             & یا < در URL وگرنه سند را نامعتبر می‌کند. --}}
        <loc>{{ htmlspecialchars($entry['url'], ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</loc>
        <lastmod>{{ htmlspecialchars($entry['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</lastmod>
        <priority>{{ htmlspecialchars($entry['priority'], ENT_XML1 | ENT_QUOTES, 'UTF-8') }}</priority>
    </url>
@endforeach
</urlset>
