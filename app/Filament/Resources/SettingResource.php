<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\SettingType;
use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Support\Ui;
use App\Models\Setting;
use App\Services\SettingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * تنظیمات عمومی سایت.
 *
 * ── چرا نوشتن از `SettingService::set()` عبور می‌کند ──
 * این جدول **هرگز راز نگه نمی‌دارد** (قاعده‌ی سخت Blueprint:545-548).
 * سرویس `assertKeyIsSafe()` نام‌های حساس (password/token/api_key/…)
 * را رد می‌کند. اگر اینجا مستقیم `Setting::create()` صدا زده شود،
 * آن محافظ دور زده می‌شود. پس ذخیره از سرویس انجام می‌گیرد.
 *
 * ── چرا اکشن حذف وجود ندارد ──
 * `SettingPolicy::delete` همیشه `false` برمی‌گرداند. اگر دکمه‌ی حذف
 * رندر شود، هر بار ۴۰۳ می‌دهد و برای مدیر گیج‌کننده است. عملیات
 * درست، پاک‌کردن مقدار است نه حذف رکورد. به همین دلیل اینجا حذف
 * (هم تک‌رکورد و هم گروهی) عمداً حذف شده است.
 */
class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'تنظیمات';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'تنظیم';

    protected static ?string $pluralModelLabel = 'تنظیمات';

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('تنظیم')
                ->schema([
                    Forms\Components\TextInput::make('key')
                        ->label('کلید')
                        ->required()
                        ->maxLength(160)
                        ->unique(ignoreRecord: true)
                        // کلید شناسه‌ی فنی است که کد از آن می‌خواند؛
                        // تغییر آن روی ویرایش یعنی رکورد جدید و کلید
                        // قدیمی بی‌استفاده. پس روی ویرایش قفل می‌شود.
                        ->disabledOn('edit')
                        ->dehydrated()
                        // ── اعتبارسنجی قبل از ذخیره ──
                        // SettingService::set() روی کلید حساس استثنا می‌زند.
                        // بدون این rule، ورودی حساس یک خطای ۵۰۰ می‌دهد.
                        // اینجا پیام فارسی روشن نشان داده می‌شود.
                        ->rules([
                            fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                if (filled($value) && ! app(SettingService::class)->isKeySafe((string) $value)) {
                                    $fail('این کلید حاوی واژه‌ی حساس است و در جدول تنظیمات ذخیره نمی‌شود. کلیدهای حساس باید در «فراهم‌کننده‌های AI» با رمزنگاری ذخیره شوند.');
                                }
                            },
                        ])
                        ->helperText('شناسه‌ی فنی که کد از آن می‌خواند (مثل site.tagline). کلیدهای حاوی password/token/secret/api_key پذیرفته نمی‌شوند — این جدول راز نگه نمی‌دارد.'),
                    Forms\Components\TextInput::make('group')
                        ->label('گروه')
                        ->maxLength(80)
                        ->helperText('گروه‌های متداول: general، seo، contact.'),
                    Forms\Components\Select::make('type')
                        ->label('نوع مقدار')
                        ->options(SettingType::options())
                        ->required()
                        ->native(false),
                    Forms\Components\Toggle::make('is_public')
                        ->label('عمومی')
                        ->helperText('اگر خاموش باشد، مقدار در نماهای عمومی سریالایز نمی‌شود.'),
                    Forms\Components\Textarea::make('value')
                        ->label('مقدار')
                        ->rows(4)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('کلید')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('group')
                    ->label('گروه')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('مقدار')
                    ->limit(50)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => Ui::enumLabel($state))
                    ->color('gray'),
                Tables\Columns\IconColumn::make('is_public')
                    ->label('عمومی')
                    ->boolean(),
                Ui::dateColumn('updated_at', 'آخرین ویرایش'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('گروه')
                    ->options(fn (): array => Setting::query()
                        ->whereNotNull('group')
                        ->distinct()
                        ->pluck('group', 'group')
                        ->all()),
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع')
                    ->options(SettingType::options()),
                Tables\Filters\TernaryFilter::make('is_public')->label('عمومی'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                // ── هیچ DeleteAction اینجا نیست ──
                // SettingPolicy::delete همیشه false است؛ رندر دکمه فقط
                // ۴۰۳ تولید می‌کند. دلیل کامل در کامنت کلاس.
            ])
            ->bulkActions([
                // حذف گروهی هم عمداً حذف شده — همان دلیل بالا.
            ])
            ->defaultSort('group');
    }

    /**
     * ذخیره از سرویس عبور می‌کند تا محافظ کلیدهای حساس اجرا شود.
     *
     * @param  array<string, mixed>  $data
     */
    public static function persistSetting(array $data, ?Setting $record = null): Setting
    {
        /** @var SettingService $service */
        $service = app(SettingService::class);

        $type = $data['type'] instanceof SettingType
            ? $data['type']
            : SettingType::tryFrom((string) ($data['type'] ?? 'string')) ?? SettingType::String;

        return $service->set(
            key: (string) $data['key'],
            value: $data['value'] ?? null,
            type: $type,
            group: $data['group'] ?? null,
            isPublic: (bool) ($data['is_public'] ?? false),
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
