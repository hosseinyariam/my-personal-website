<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ModuleKey;
use App\Filament\Resources\ModuleResource\Pages;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * ماژول‌ها — کنترل روشن/خاموش بخش‌های سایت.
 *
 * ── معنای «خاموش» ──
 * خاموش‌کردن ماژول: مسیر عمومی را ۴۰۴ می‌کند، لینک ناوبری را حذف
 * می‌کند، ولی داده را حذف نمی‌کند و دسترسی پنل را تغییر نمی‌دهد.
 * مدل `Module::isEnabled()` در نبود رکورد «روشن» فرض می‌کند؛ به همین
 * دلیل حذف رکورد ماژول، آن را دوباره روشن می‌کند — یعنی حذف معکوسِ
 * نیت مدیر عمل می‌کند. پس اکشن حذف وجود ندارد.
 *
 * ── چرا اکشن حذف وجود ندارد ──
 * `ModulePolicy::delete` همیشه `false` است. علاوه بر آن، حذف رکورد
 * ماژول باعث بازگشت آن به حالت پیش‌فرض «روشن» می‌شود که با قصد مدیر
 * در تضاد است. عملیات درست، `is_enabled = false` است.
 */
class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'نمایش';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'ماژول';

    protected static ?string $pluralModelLabel = 'ماژول‌ها';

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ماژول')
                ->schema([
                    Forms\Components\Select::make('key')
                        ->label('کلید ماژول')
                        ->options(ModuleKey::options())
                        ->required()
                        // کلید ثابت است: کد به آن اشاره می‌کند
                        // (middleware، ناوبری، کوئری عمومی).
                        ->disabledOn('edit')
                        ->dehydrated()
                        ->helperText('کلید ثابت است و پس از ساخت قابل تغییر نیست.'),
                    Forms\Components\TextInput::make('name')
                        ->label('نام')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\Textarea::make('description')
                        ->label('توضیحات')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('نمایش')
                ->schema([
                    Forms\Components\Toggle::make('is_enabled')
                        ->label('فعال')
                        ->default(true)
                        ->helperText('خاموش‌کردن، مسیر عمومی ماژول را ۴۰۴ می‌کند ولی داده را حذف نمی‌کند و دسترسی پنل را تغییر نمی‌دهد.'),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('ترتیب نمایش')
                        ->numeric()
                        ->default(0),
                    Forms\Components\KeyValue::make('settings')
                        ->label('تنظیمات')
                        ->keyLabel('کلید')
                        ->valueLabel('مقدار')
                        ->helperText('تنظیمات غیرحساس مخصوص این ماژول.')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نام')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->label('کلید')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_enabled')
                    ->label('فعال'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتیب')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')->label('فعال'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                // هیچ DeleteAction — ModulePolicy::delete همیشه false است و
                // حذف رکورد، ماژول را دوباره «روشن» می‌کند.
            ])
            ->bulkActions([])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
            'create' => Pages\CreateModule::route('/create'),
            'edit' => Pages\EditModule::route('/{record}/edit'),
        ];
    }
}
