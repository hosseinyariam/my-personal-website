<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\CurrentFocusStatus;
use App\Enums\CurrentFocusType;
use App\Filament\Resources\CurrentFocusResource\Pages;
use App\Filament\Support\Ui;
use App\Models\CurrentFocus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * تمرکز فعلی — «الان روی چه چیزی کار می‌کنم؟»
 *
 * ── قاعده‌ی «حداکثر یک هدف» ──
 * تصمیم D6: سه کلید خارجی خالی، ولی حداکثر یکی باید ست شود. این قید
 * در دیتابیس نیست؛ اینجا با یک rule سطح فرم اعمال می‌شود. پیام خطا
 * فارسی و روشن است تا مدیر بداند چه باید بکند. متد `hasMultipleTargets()`
 * مدل همین را در سطح داده می‌سنجد؛ اینجا لایه‌ی اعتبارسنجی ورودی است.
 */
class CurrentFocusResource extends Resource
{
    protected static ?string $model = CurrentFocus::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'مسیر';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'تمرکز فعلی';

    protected static ?string $pluralModelLabel = 'تمرکزهای فعلی';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('تمرکز')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('عنوان')
                        ->required()
                        ->maxLength(180),
                    Forms\Components\Select::make('focus_type')
                        ->label('نوع')
                        ->options(CurrentFocusType::options())
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('status')
                        ->label('وضعیت')
                        ->options(CurrentFocusStatus::options())
                        ->required()
                        ->native(false),
                    Forms\Components\Textarea::make('description')
                        ->label('توضیحات')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\DatePicker::make('started_at')
                        ->label('تاریخ شروع'),
                    Forms\Components\DatePicker::make('target_date')
                        ->label('تاریخ هدف'),
                ])->columns(2),

            Forms\Components\Section::make('هدف مرتبط')
                ->description('حداکثر یکی از این سه را انتخاب کنید. تمرکز می‌تواند بدون هدف هم باشد.')
                ->schema([
                    Forms\Components\Select::make('project_id')
                        ->label('پروژه')
                        ->relationship('project', 'title')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        // ── قاعده‌ی «حداکثر یک هدف» (تصمیم D6) ──
                        // سه کلید خارجی می‌توانند خالی باشند، ولی بیش از
                        // یکی نباید ست شود. این rule روی اولین فیلد گروه
                        // گذاشته شده و هر سه را می‌سنجد. `$get` وضعیت
                        // زنده‌ی فرم را می‌خواند، پس مستقل از ترتیب
                        // پرکردن فیلدها درست کار می‌کند.
                        ->rules([
                            fn (Forms\Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                $count = collect([$value, $get('product_id'), $get('company_id')])
                                    ->filter()
                                    ->count();

                                if ($count > 1) {
                                    $fail('حداکثر یکی از «پروژه»، «محصول» یا «شرکت» را می‌توانید به‌عنوان هدف انتخاب کنید.');
                                }
                            },
                        ]),
                    Forms\Components\Select::make('product_id')
                        ->label('محصول')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false),
                    Forms\Components\Select::make('company_id')
                        ->label('شرکت')
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false),
                ])->columns(3),

            Forms\Components\Section::make('نمایش')
                ->schema([
                    Forms\Components\Toggle::make('is_featured')
                        ->label('ویژه (نمایش در صفحه اصلی)')
                        ->helperText('فقط تعداد محدودی تمرکز برجسته در صفحه‌ی اصلی نمایش داده می‌شود.'),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('ترتیب نمایش')
                        ->numeric()
                        ->default(0),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('focus_type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => Ui::enumLabel($state))
                    ->color('info'),
                Ui::statusColumn('status', 'وضعیت', 'focus'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('ویژه')
                    ->boolean(),
                Ui::dateColumn('started_at', 'شروع'),
                Ui::dateColumn('target_date', 'هدف'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('focus_type')
                    ->label('نوع')
                    ->options(CurrentFocusType::options()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(CurrentFocusStatus::options()),
                Tables\Filters\TernaryFilter::make('is_featured')->label('ویژه'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف'),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrentFoci::route('/'),
            'create' => Pages\CreateCurrentFocus::route('/create'),
            'edit' => Pages\EditCurrentFocus::route('/{record}/edit'),
        ];
    }
}
