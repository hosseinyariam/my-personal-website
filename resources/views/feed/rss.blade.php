<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $siteName }}</title>
        <link>{{ url('/') }}</link>
        <description>{{ $siteDescription }}</description>
        <language>fa-IR</language>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
        @if ($posts->isNotEmpty())
            <lastBuildDate>{{ $posts->first()->published_at->toRfc2822String() }}</lastBuildDate>
        @endif
        @foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ route('blog.show', $post->slug) }}</link>
            <guid isPermaLink="true">{{ route('blog.show', $post->slug) }}</guid>
            <pubDate>{{ $post->published_at->toRfc2822String() }}</pubDate>
            @if ($post->excerpt)
            <description><![CDATA[{{ $post->excerpt }}]]></description>
            @endif
            @foreach ($post->tags as $tag)
            <category>{{ $tag->name }}</category>
            @endforeach
        </item>
        @endforeach
    </channel>
</rss>
