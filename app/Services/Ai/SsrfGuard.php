<?php

declare(strict_types=1);

namespace App\Services\Ai;

/**
 * محافظت SSRF برای درخواست‌های خارجی.
 *
 * ── چرا این کلاس لازم است ──
 * دستورالعمل بخش ۱۰ می‌گوید «اعتبارسنجی Base URL و محدودکردن مقصد
 * درخواست‌ها برای جلوگیری از SSRF». هیچ سندی از ۱۱ سند پروژه این را
 * نگفته است، ولی سطح حمله واقعی است: `base_url` هوش مصنوعی از پنل
 * قابل تنظیم است و سرور به آن درخواست می‌زند.
 *
 * سناریوی حمله: تنظیم `base_url` روی `https://169.254.169.254/...`
 * (سرویس فراداده‌ی ابری) یا `http://localhost:2083/...` (پنل cPanel
 * خود سایت). بدون محافظت، پاسخ این سرویس‌های داخلی به‌عنوان «پاسخ
 * هوش مصنوعی» برگردانده می‌شود و اطلاعات محیطی لو می‌رود.
 *
 * ── چرا allowlist دامنه کافی نیست ──
 * allowlist سخت‌گیرانه، محصول را به یک ارائه‌دهنده گره می‌زند و
 * «قابل تعویض بودن» (معماری:811) را از بین می‌برد. رویکرد اینجا
 * **deny-list هوشمند** است: هر دامنه‌ی عمومی مجاز، ولی آدرس‌های
 * داخلی، خصوصی و ویژه رد.
 * ─────────────────────────────────────
 */
final class SsrfGuard
{
    /**
     * بازه‌های IP که هرگز نباید مقصد درخواست باشند.
     *
     * @var list<string>
     */
    private const FORBIDDEN_RANGES = [
        '0.0.0.0/8',          // «این شبکه»
        '10.0.0.0/8',         // خصوصی
        '100.64.0.0/10',      // CGNAT
        '127.0.0.0/8',        // loopback
        '169.254.0.0/16',     // link-local — سرویس فراداده‌ی ابری
        '172.16.0.0/12',      // خصوصی
        '192.0.0.0/24',       // IETF protocol assignments
        '192.0.2.0/24',       // TEST-NET-1
        '192.88.99.0/24',     // 6to4 relay
        '192.168.0.0/16',     // خصوصی
        '198.18.0.0/15',      // benchmarking
        '198.51.100.0/24',    // TEST-NET-2
        '203.0.113.0/24',     // TEST-NET-3
        '224.0.0.0/4',        // multicast
        '240.0.0.0/4',        // reserved
        '255.255.255.255/32', // broadcast
        '::1/128',            // loopback IPv6
        'fc00::/7',           // unique local IPv6
        'fe80::/10',          // link-local IPv6
    ];

    /**
     * نام‌های میزبانی که همیشه رد می‌شوند.
     *
     * @var list<string>
     */
    private const FORBIDDEN_HOSTNAMES = [
        'localhost',
        'localhost.localdomain',
        'metadata.google.internal',
        'metadata',
        'host.docker.internal',
    ];

    /**
     * آیا این URL برای یک درخواست خروجی امن است؟
     *
     * بررسی‌ها به ترتیب:
     *   ۱. طرح باید `https` باشد
     *   ۲. نام میزبان در deny-list نباشد
     *   ۳. پورت باید استاندارد باشد (443 یا 80)
     *   ۴. اگر IP خالص بود، در بازه‌ی خصوصی نباشد
     *   ۵. اگر دامنه بود، رزولوشن شود و همه‌ی IPهایش بررسی شوند
     */
    public static function isSafeUrl(string $url): bool
    {
        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        // HTTPS اجباری: http ساده هم اطلاعات کلید را لو می‌دهد و هم
        // امکان downgrade در شبکه‌های آلوده را می‌دهد.
        if (mb_strtolower($parts['scheme']) !== 'https') {
            return false;
        }

        $host = mb_strtolower(trim($parts['host'], '[]'));

        if ($host === '') {
            return false;
        }

        if (in_array($host, self::FORBIDDEN_HOSTNAMES, true)) {
            return false;
        }

        // پورت غیراستاندارد میتواند به سرویس داخلی اشاره کند
        // (مثلاً 2083 = پنل cPanel، 3306 = MySQL).
        if (isset($parts['port']) && ! in_array($parts['port'], [443, 80], true)) {
            return false;
        }

        // اگر خودِ host یک IP بود، مستقیم بررسی می‌شود
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return self::isPublicIp($host);
        }

        // اگر دامنه بود، همه‌ی IPهایی که به آن اشاره می‌کنند بررسی
        // می‌شوند. DNS rebinding می‌تواند دامنه‌ی ظاهراً عمومی را در
        // زمان اجرا به یک آدرس داخلی نشان دهد؛ بررسی همه‌ی رکوردها
        // این پنجره را تنگ می‌کند.
        $ips = self::resolveHost($host);

        if ($ips === []) {
            // ناتوانی در رزولوشن: fail-closed. اگر نمی‌دانیم کجاست،
            // درخواست نمی‌زنیم.
            return false;
        }

        foreach ($ips as $ip) {
            if (! self::isPublicIp($ip)) {
                return false;
            }
        }

        return true;
    }

    /**
     * آیا این IP آدرس عمومی است؟
     */
    public static function isPublicIp(string $ip): bool
    {
        // IPهای ویژه‌ی IPv6 و IPv4 باید رد شوند
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }

        // بررسی اضافی بازه‌های link-local و متادیتا که PHP ممکن است
        // به‌عنوان «reserved» نشناسد.
        foreach (self::FORBIDDEN_RANGES as $range) {
            if (self::ipInRange($ip, $range)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private static function resolveHost(string $host): array
    {
        $ips = [];

        // رکوردهای A (IPv4)
        $ipv4 = @gethostbynamel($host);

        if (is_array($ipv4)) {
            $ips = array_merge($ips, $ipv4);
        }

        // رکوردهای AAAA (IPv6)
        $records = @dns_get_record($host, DNS_AAAA);

        if (is_array($records)) {
            foreach ($records as $record) {
                if (isset($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }

        return array_values(array_unique($ips));
    }

    /**
     * آیا IP در یک بازه‌ی CIDR قرار دارد؟
     */
    private static function ipInRange(string $ip, string $range): bool
    {
        if (! str_contains($range, '/')) {
            return false;
        }

        [$subnet, $bits] = explode('/', $range, 2);
        $bits = (int) $bits;

        $ipBinary = @inet_pton($ip);
        $subnetBinary = @inet_pton($subnet);

        if ($ipBinary === false || $subnetBinary === false) {
            return false;
        }

        // نسخه‌های IP باید یکی باشند
        if (strlen($ipBinary) !== strlen($subnetBinary)) {
            return false;
        }

        $bytes = $bits >> 3;
        $remainderBits = $bits & 7;

        // مقایسه‌ی بایت‌های کامل
        if ($bytes > 0 && substr($ipBinary, 0, $bytes) !== substr($subnetBinary, 0, $bytes)) {
            return false;
        }

        // مقایسه‌ی بیت‌های باقی‌مانده
        if ($remainderBits === 0) {
            return true;
        }

        $mask = ~((1 << (8 - $remainderBits)) - 1) & 0xFF;

        return (ord($ipBinary[$bytes]) & $mask) === (ord($subnetBinary[$bytes]) & $mask);
    }
}
