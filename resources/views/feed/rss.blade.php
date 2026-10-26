{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
{{-- چرا رشته تکه‌تکه شده: اعلان XML باید در خط اول و بدون فاصله‌ی پیش از
     آن باشد. اگر `<?xml` یکجا در منبع بیاید و short_open_tag روشن باشد،
     توکنایزر Blade آن را تگ باز PHP می‌بیند و کامپایل می‌شکند. --}}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $profile?->display_name ?: config('app.name') }}</title>
        <link>{{ url('/') }}</link>
        <description>{{ $profile?->short_bio ?: 'یادداشت‌های حسین یاریم درباره ساختن محصول و مهندسی نرم‌افزار.' }}</description>
        <language>fa-IR</language>
        {{-- آخرین زمان ساخت فید بر اساس تازه‌ترین آیتم — RFC 822. --}}
        <lastBuildDate>{{ $updated->toRfc2822String() }}</lastBuildDate>

        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />

        @foreach ($items as $item)
            <item>
                <title>{{ $item->title }}</title>
                <link>{{ route('notes.show', $item) }}</link>
                {{-- guid با isPermaLink=true یعنی همان URL دائمی است. --}}
                <guid isPermaLink="true">{{ route('notes.show', $item) }}</guid>
                {{-- تاریخ انتشار: اگر published_at نبود، updated_at. --}}
                <pubDate>{{ ($item->published_at ?? $item->updated_at)->toRfc2822String() }}</pubDate>
                <description>{{ \App\Support\Persian::excerpt($item->excerpt ?: $item->body, 200) }}</description>
            </item>
        @endforeach
    </channel>
</rss>
