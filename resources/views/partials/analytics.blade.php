@php
    $matomoUrl = rtrim((string) $matomoUrl, '/');
@endphp

@if (filled($matomoUrl) && filled($matomoSiteId))
    {{--
        Matomo سلف‌هاست، پیکربندی بدون کوکی (D07):
        disableCookies یعنی نیازی به بنر رضایت نیست و وزن صفحه هم کمتر می‌ماند.
    --}}
    <script>
        var _paq = (window._paq = window._paq || []);
        _paq.push(['disableCookies']);
        _paq.push(['enableLinkTracking']);
        _paq.push(['trackPageView']);
        (function () {
            var u = @json($matomoUrl . '/');
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId', @json((string) $matomoSiteId)]);
            var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
            g.async = true; g.src = u + 'matomo.js'; s.parentNode.insertBefore(g, s);
        })();

        /*
         | رویدادهای سنجه‌ی محصول (فاز ۵):
         |   engagement/resume_download · engagement/contact_submit · platform/demo_click
         | با data-track روی هر عنصر اعلام می‌شوند تا کد ردیابی در Blade پخش نشود.
         */
        document.addEventListener('click', function (e) {
            var el = e.target.closest('[data-track]');
            if (!el) return;

            var action = el.dataset.track;
            var category = action === 'demo_click' ? 'platform' : 'engagement';

            _paq.push(['trackEvent', category, action, el.dataset.trackLabel || '']);
        });
    </script>
@endif
