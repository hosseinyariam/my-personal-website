<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Setting;
use App\Services\Settings;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // کش تنظیمات بین تست‌ها نشت نکند
        app(Settings::class)->forget();
    }

    /** میان‌بر: مقداردهی یک تنظیم و ابطال کش آن گروه. */
    protected function setting(string $group, string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value],
        );

        app(Settings::class)->forget($group);
    }
}
