<?php

declare(strict_types=1);
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| پیکربندی Pest
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| کمک‌کننده‌های تست
|--------------------------------------------------------------------------
*/

// helper ساخت داده‌ی واقعی سایت — در `tests/helpers.php`
require_once __DIR__.'/helpers.php';
