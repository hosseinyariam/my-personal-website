<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Setting;
use App\Services\Settings;

class SettingObserver
{
    public function __construct(private readonly Settings $settings) {}

    public function saved(Setting $setting): void
    {
        $this->settings->forget($setting->group);
    }

    public function deleted(Setting $setting): void
    {
        $this->settings->forget($setting->group);
    }
}
