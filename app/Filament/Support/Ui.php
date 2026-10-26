<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Enums\CurrentFocusStatus;
use App\Enums\ProductStatus;
use App\Enums\ProjectStatus;
use App\Enums\PublicationStatus;
use App\Enums\Visibility;
use App\Support\Persian;
use Carbon\Carbon;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * کمک‌کننده‌های مشترک پنل.
 *
 * ── چرا اینجا و نه در هر Resource ──
 * انتخاب وضعیت، نمایش نشان وضعیت، و نمایش تاریخ شمسی در همه‌ی منابع
 * تکرار می‌شوند. اگر هر Resource خودش `->color()` بنویسد، دیر یا زود
 * یک جا رنگ اشتباه می‌شود و پنل ناهمگون می‌شود. یک منبع حقیقت اینجا
 * نگه داشته می‌شود تا رنگ و برچسب همه‌جا یکسان بماند.
 */
final class Ui
{
    /**
     * Select وضعیت از یک enum — گزینه‌ها همیشه از `::options()` خود enum.
     *
     * چرا از خود enum: هر enum قرارداد `options()` دارد که مقدار را به
     * برچسب فارسی نگاشت می‌کند. نوشتن دستی گزینه‌ها یعنی دو منبع حقیقت
     * که با افزودن یک case جدید ناهمخوان می‌شوند.
     *
     * @param  class-string  $enum
     */
    public static function enumSelect(string $name, string $enum, string $label, bool $required = false): Select
    {
        return Select::make($name)
            ->label($label)
            ->options($enum::options())
            ->required($required)
            ->native(false);
    }

    /**
     * ستون نشان وضعیت با برچسب فارسی و رنگ نگاشت‌شده.
     *
     * `badge()` روی TextColumn مقدار را به یک نشان رنگی تبدیل می‌کند؛
     * برچسب از `label()` خود enum می‌آید تا متن فارسی یک‌منبع باشد.
     */
    public static function statusColumn(string $name, string $label, string $colorMap = 'publication'): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->badge()
            ->formatStateUsing(fn ($state): string => self::enumLabel($state))
            ->color(fn ($state): string => self::statusColor($state, $colorMap));
    }

    /** برچسب یک enum یا رشته‌ی خام. */
    public static function enumLabel(mixed $state): string
    {
        if ($state === null || $state === '') {
            return '—';
        }

        if ($state instanceof \BackedEnum && method_exists($state, 'label')) {
            return $state->label();
        }

        return (string) $state;
    }

    /**
     * نگاشت enum وضعیت به رنگ Filament.
     *
     * نگاشت بر اساس معنای دامنه است، نه بر اساس `badgeClass()` مدل
     * (که کلاس CSS مخصوص سایت عمومی است و با پالت Filament یکی نیست).
     */
    public static function statusColor(mixed $state, string $colorMap): string
    {
        if ($state === null) {
            return 'gray';
        }

        return match ($colorMap) {
            'publication' => match ($state) {
                PublicationStatus::Draft => 'gray',
                PublicationStatus::Published => 'success',
                PublicationStatus::Archived => 'warning',
                default => 'gray',
            },
            'project' => match ($state) {
                ProjectStatus::Idea => 'gray',
                ProjectStatus::InProgress => 'info',
                ProjectStatus::Completed => 'success',
                ProjectStatus::Paused => 'warning',
                ProjectStatus::Archived => 'gray',
                default => 'gray',
            },
            'product' => match ($state) {
                ProductStatus::Idea => 'gray',
                ProductStatus::Development => 'info',
                ProductStatus::Beta => 'warning',
                ProductStatus::Live => 'success',
                ProductStatus::Paused => 'warning',
                ProductStatus::Archived => 'gray',
                default => 'gray',
            },
            'focus' => match ($state) {
                CurrentFocusStatus::Active => 'success',
                CurrentFocusStatus::Paused => 'warning',
                CurrentFocusStatus::Completed => 'info',
                CurrentFocusStatus::Archived => 'gray',
                default => 'gray',
            },
            'visibility' => match ($state) {
                Visibility::Public => 'success',
                Visibility::Unlisted => 'warning',
                Visibility::Private => 'gray',
                default => 'gray',
            },
            default => 'gray',
        };
    }

    /** ستون تاریخ شمسی خوانا. */
    public static function dateColumn(string $name, string $label): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->formatStateUsing(fn ($state): string => self::jalali($state))
            ->sortable()
            ->placeholder('—');
    }

    /** تبدیل یک تاریخ به رشته‌ی شمسی، مقاوم به انواع ورودی. */
    public static function jalali(mixed $state, string $format = 'd MMMM y'): string
    {
        if (blank($state)) {
            return '—';
        }

        if ($state instanceof \DateTimeInterface) {
            return Persian::date($state, $format);
        }

        try {
            return Persian::date(Carbon::parse((string) $state), $format);
        } catch (\Throwable) {
            return '—';
        }
    }

    /**
     * Slug اختیاری با راهنمای پرکردن خودکار.
     *
     * قاعده‌ی مدل‌ها: slug خالی در رویداد `saving` از عنوان ساخته
     * می‌شود. پس هرگز `required` نیست — ولی راهنما باید صریح بگوید
     * که خالی گذاشتن آن بی‌خطر است.
     */
    public static function slugField(string $sourceField = 'title'): TextInput
    {
        return TextInput::make('slug')
            ->label('نامک (slug)')
            ->maxLength(220)
            ->helperText('اختیاری. اگر خالی بماند، هنگام ذخیره به‌طور خودکار از عنوان فارسی ساخته می‌شود و یکتایی آن تضمین می‌گردد.')
            ->unique(ignoreRecord: true)
            ->columnSpanFull();
    }

    /**
     * ویرایشگر متن غنی با پاک‌سازی HTML در زمان ذخیره.
     *
     * ── وضعیت واقعی پاک‌سازی در Filament 3.3 (بررسی‌شده) ──
     * `RichEditor` فقط هنگام **نمایش** خروجی را با `sanitizeHtml()`
     * پاک می‌کند (blade: `{!! str($getState())->sanitizeHtml() !!}`).
     * اما مقداری که در رویداد dehydrate به مدل ذخیره می‌شود **خام**
     * است — Filament آن را پاک نمی‌کند. پس اگر مدیر HTML آلوده‌ای
     * بچسباند، در دیتابیس ذخیره می‌شود و هر جای دیگری که بدون
     * `sanitizeHtml()` رندر شود (خروجی RSS، meta description، آینده)
     * خطر XSS دارد.
     *
     * Baseline §6 صریحاً می‌گوید «تمام HTML ورودی پنل با HTMLPurifier
     * پاک‌سازی می‌شود». برای رعایت این سیاست، اینجا `dehydrateStateUsing`
     * مقدار را با همان `Str::sanitizeHtml()` خود Filament (که بر پایه‌ی
     * Symfony HtmlSanitizer است) پاک می‌کند تا **آنچه ذخیره می‌شود**
     * هم تمیز باشد، نه فقط آنچه نمایش داده می‌شود.
     */
    public static function richEditor(string $name, string $label): RichEditor
    {
        return RichEditor::make($name)
            ->label($label)
            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Str::sanitizeHtml($state) : $state)
            ->columnSpanFull();
    }

    /**
     * آیا این رکورد همین حالا برای عموم دیده می‌شود؟
     *
     * از خودِ `isPubliclyVisible()` مدل استفاده می‌کند (concern
     * Publishable) تا پنل و سایت عمومی هرگز دو تعریف متفاوت از
     * «منتشرشده» نداشته باشند.
     */
    public static function isLive(?Model $record): bool
    {
        return $record !== null
            && method_exists($record, 'isPubliclyVisible')
            && $record->isPubliclyVisible();
    }
}
