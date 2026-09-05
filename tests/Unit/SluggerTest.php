<?php

declare(strict_types=1);

use App\Support\Slugger;

it('فارسی را به لاتین ترانویسی می‌کند', function () {
    expect(Slugger::make('وینورا'))->toBe('vynvra')
        ->and(Slugger::make('پلتفرم سلامت'))->toBe('pltfrm-slamt');
});

it('اسلاگ لاتین را دست‌نخورده و kebab نگه می‌دارد', function () {
    expect(Slugger::make('Design Tokens'))->toBe('design-tokens')
        ->and(Slugger::make('  Hello   World  '))->toBe('hello-world');
});

it('ارقام فارسی را به لاتین تبدیل می‌کند', function () {
    expect(Slugger::make('نسخه ۲'))->toBe('nskhh-2');
});

it('برای ورودی بی‌حرف هم خروجی غیرخالی می‌دهد', function () {
    expect(Slugger::make('!!!'))->toStartWith('item-')
        ->and(Slugger::make(''))->toStartWith('item-');
});
