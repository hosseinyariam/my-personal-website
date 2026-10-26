<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Filament\Support\Ui;
use App\Models\Media;
use App\Services\MediaService;
use App\Support\Persian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * کتابخانه‌ی رسانه.
 *
 * ── چرا آپلود از مسیر `MediaService` عبور می‌کند ──
 * `MediaService::store()` اعتبارسنجی واقعی MIME (نه فقط پسوند)، سقف
 * حجم، نام تصادفی و جلوگیری از اجرای فایل را تضمین می‌کند. اگر
 * FileUpload خودش فایل را مستقیم ذخیره کند، این محافظت‌ها دور زده
 * می‌شوند. پس فیلد با `storeFiles(false)` تنظیم شده تا فایل موقت را
 * نگه دارد و ذخیره‌ی واقعی در صفحه‌ی ایجاد، از سرویس انجام شود.
 *
 * ── چرا مسیرها قابل ویرایش نیستند ──
 * `disk` و `path` جزئیات ذخیره‌سازی‌اند؛ ویرایش دستی آن‌ها رکورد را
 * به فایلی ناموجود یا خارج از پوشه‌ی مجاز اشاره می‌دهد. فقط فراداده‌ی
 * قابل ویرایش (alt، caption) در فرم می‌آید.
 */
class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'رسانه';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'رسانه';

    protected static ?string $pluralModelLabel = 'رسانه‌ها';

    protected static ?string $recordTitleAttribute = 'original_name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('آپلود')
                ->visibleOn('create')
                ->schema([
                    Forms\Components\FileUpload::make('upload')
                        ->label('فایل')
                        ->required()
                        ->storeFiles(false)
                        ->acceptedFileTypes([
                            'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif',
                            'application/pdf',
                        ])
                        ->maxSize(MediaService::MAX_SIZE_KB)
                        ->helperText('انواع مجاز: JPEG، PNG، WebP، GIF، AVIF و PDF. حداکثر ۵ مگابایت. اعتبارسنجی MIME واقعی هنگام ذخیره انجام می‌شود.'),
                ]),

            Forms\Components\Section::make('فراداده')
                ->schema([
                    Forms\Components\TextInput::make('alt_text')
                        ->label('متن جایگزین (alt)')
                        ->maxLength(255)
                        ->helperText('برای دسترس‌پذیری. اگر خالی بماند، نام فایل به‌عنوان جایگزین استفاده می‌شود.'),
                    Forms\Components\TextInput::make('caption')
                        ->label('توضیح (caption)')
                        ->maxLength(255),
                ])->columns(2),

            Forms\Components\Section::make('اطلاعات فایل')
                ->visibleOn('edit')
                ->schema([
                    Forms\Components\TextInput::make('original_name')->label('نام اصلی')->disabled(),
                    Forms\Components\TextInput::make('mime_type')->label('نوع')->disabled(),
                    Forms\Components\TextInput::make('path')->label('مسیر ذخیره‌سازی')->disabled()->helperText('مسیر فایل قابل ویرایش نیست.'),
                    Forms\Components\TextInput::make('disk')->label('دیسک')->disabled(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('پیش‌نمایش')
                    ->disk('public')
                    ->height(40)
                    ->width(40)
                    ->defaultImageUrl(null),
                Tables\Columns\TextColumn::make('original_name')
                    ->label('نام فایل')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('mime_type')
                    ->label('نوع')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('size')
                    ->label('حجم')
                    ->state(fn (Media $record): string => $record->humanSize()),
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('استفاده')
                    ->state(fn (Media $record): string => Persian::digits($record->usageCount()))
                    ->badge()
                    ->color(fn (Media $record): string => $record->isShared() ? 'warning' : 'gray')
                    ->tooltip('تعداد جاهایی که این رسانه استفاده شده. بیش از یک یعنی مشترک است و حذف فایل، جای دیگر را می‌شکند.'),
                Tables\Columns\IconColumn::make('shared')
                    ->label('مشترک')
                    ->state(fn (Media $record): bool => $record->isShared())
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('heroicon-o-minus'),
                Ui::dateColumn('created_at', 'بارگذاری'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('mime_type')
                    ->label('نوع')
                    ->options([
                        'image/jpeg' => 'JPEG',
                        'image/png' => 'PNG',
                        'image/webp' => 'WebP',
                        'image/gif' => 'GIF',
                        'image/avif' => 'AVIF',
                        'application/pdf' => 'PDF',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    // ── هشدار اشتراک ──
                    // اگر رسانه در بیش از یک جا استفاده شده باشد، حذف
                    // فیزیکی آن محتوای دیگر را می‌شکند. تأیید اضافه با
                    // نمایش تعداد استفاده، تصمیم را آگاهانه می‌کند.
                    ->modalHeading('حذف رسانه')
                    ->modalDescription(fn (Media $record): string => $record->isShared()
                        ? "این رسانه در {$record->usageCount()} جا استفاده شده است. حذف آن محتوای دیگر را می‌شکند."
                        : 'این رسانه جای دیگری استفاده نشده و با حذف آن، فایل هم پاک می‌شود.')
                    ->using(function (Media $record): void {
                        // حذف از سرویس انجام می‌شود تا ابتدا پیوست‌ها و
                        // رکورد، سپس فایل فیزیکی (در صورت نبود استفاده‌ی
                        // دیگر) پاک شود. `force` فقط وقتی true است که
                        // مدیر وجود اشتراک را دیده و تأیید کرده.
                        app(MediaService::class)->delete($record, force: $record->isShared());
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
