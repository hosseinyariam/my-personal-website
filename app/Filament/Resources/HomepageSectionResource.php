<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ModuleKey;
use App\Filament\Resources\HomepageSectionResource\Pages;
use App\Models\HomepageSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * بخش‌های صفحه اصلی.
 *
 * ── چرا این «صفحه‌ساز» نیست ──
 * Blueprint:521-533 و محدودیت MVP: مدیر فقط می‌تواند ترتیب و
 * روشن/خاموشی بخش‌های **از پیش طراحی‌شده** را تغییر دهد. کلیدها از
 * allowlist ثابت `HomepageSection::ALLOWED_KEYS` می‌آیند و روی ویرایش
 * غیرقابل‌تغییرند، چون Composer صفحه اصلی فقط همان کلیدها را می‌شناسد.
 *
 * ── چرا اکشن حذف وجود ندارد ──
 * `HomepageSectionPolicy::delete` همیشه `false` است. بخش‌ها فقط ترتیب
 * و پیکربندی نگه می‌دارند؛ عملیات درست برای کنار گذاشتن یک بخش،
 * خاموش‌کردن `is_enabled` است که هیچ داده‌ای را حذف نمی‌کند.
 */
class HomepageSectionResource extends Resource
{
    protected static ?string $model = HomepageSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'نمایش';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'بخش صفحه اصلی';

    protected static ?string $pluralModelLabel = 'بخش‌های صفحه اصلی';

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بخش')
                ->schema([
                    Forms\Components\Select::make('key')
                        ->label('کلید بخش')
                        ->options(HomepageSection::ALLOWED_KEYS)
                        ->required()
                        // کلید بخش ثابت است: تغییر آن روی ویرایش یعنی
                        // Composer صفحه اصلی دیگر این رکورد را نمی‌شناسد.
                        ->disabledOn('edit')
                        ->dehydrated()
                        ->helperText('کلید بخش ثابت است و پس از ساخت قابل تغییر نیست. فهرست از بخش‌های از پیش پشتیبانی‌شده می‌آید.'),
                    Forms\Components\TextInput::make('title')
                        ->label('عنوان جایگزین')
                        ->maxLength(160)
                        ->helperText('اختیاری. اگر خالی بماند، عنوان پیش‌فرض بخش استفاده می‌شود.'),
                    Forms\Components\Select::make('module_key')
                        ->label('ماژول وابسته')
                        ->options(ModuleKey::options())
                        ->native(false)
                        ->helperText('اگر ماژول مرتبط خاموش باشد، این بخش هم نمایش داده نمی‌شود.'),
                    Forms\Components\Select::make('selection_mode')
                        ->label('حالت انتخاب محتوا')
                        ->options([
                            'automatic' => 'خودکار',
                            'manual' => 'دستی',
                        ])
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('display_limit')
                        ->label('حداکثر تعداد آیتم')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(12)
                        ->helperText('بین ۱ تا ۱۲.'),
                ])->columns(2),

            Forms\Components\Section::make('نمایش')
                ->schema([
                    Forms\Components\Toggle::make('is_enabled')
                        ->label('فعال')
                        ->default(true)
                        ->helperText('خاموش‌کردن بخش، داده‌ای را حذف نمی‌کند و با روشن‌کردن دوباره برمی‌گردد.'),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('ترتیب نمایش')
                        ->numeric()
                        ->default(0),
                    Forms\Components\KeyValue::make('settings')
                        ->label('تنظیمات نمایشی')
                        ->keyLabel('کلید')
                        ->valueLabel('مقدار')
                        ->helperText('تنظیمات محدود و از پیش تعریف‌شده‌ی این بخش. ساخت layout دلخواه از پنل خارج از دامنه‌ی نسخه‌ی اول است.')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('بخش')
                    ->state(fn (HomepageSection $record): string => $record->label())
                    ->searchable(query: fn ($query, string $search) => $query->where('key', 'like', "%{$search}%")),
                Tables\Columns\TextColumn::make('key')
                    ->label('کلید')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('module_key')
                    ->label('ماژول')
                    ->placeholder('—'),
                Tables\Columns\ToggleColumn::make('is_enabled')
                    ->label('فعال'),
                Tables\Columns\TextColumn::make('selection_mode')
                    ->label('حالت')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('display_limit')
                    ->label('تعداد')
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')->label('فعال'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                // هیچ DeleteAction — HomepageSectionPolicy::delete همیشه false است.
            ])
            ->bulkActions([])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomepageSections::route('/'),
            'create' => Pages\CreateHomepageSection::route('/create'),
            'edit' => Pages\EditHomepageSection::route('/{record}/edit'),
        ];
    }
}
