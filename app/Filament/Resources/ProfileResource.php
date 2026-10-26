<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages;
use App\Filament\Support\Ui;
use App\Models\Media;
use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * پروفایل — هویت عمومی صاحب سایت.
 *
 * ── چرا «تقریباً تک‌رکوردی» ──
 * فقط یک پروفایل می‌تواند فعال باشد (مدل `activateExclusively()`).
 * ولی صفحه‌ی فهرست حذف نمی‌شود، چون چند رکورد می‌توانند وجود داشته
 * باشند (مثلاً نسخه‌ی انگلیسی آینده) و مدیر باید بتواند بین‌شان جابه‌جا
 * شود. دکمه‌ی «فعال‌سازی انحصاری» این کار را امن انجام می‌دهد.
 *
 * ── تصمیم درباره‌ی تصویر و رزومه ──
 * `avatar_media_id` و `resume_media_id` کلید خارجی به جدول `media`
 * هستند. در این نسخه از **Select روی رکوردهای موجود Media** استفاده
 * می‌شود، نه FileUpload مستقیم روی جدول `profiles`. دلیل: نوشتن
 * مستقیم FileUpload روی `profiles` یعنی یک فایل بدون رکورد `media`
 * بسازیم و آن‌گاه کتابخانه‌ی رسانه، شمارش استفاده و منطق حذف امن
 * (MediaService) بی‌اثر می‌شود. انتخاب از رسانه‌های موجود همان
 * چیزی است که معماری خواسته. یکپارچگی کامل «آپلود از داخل فرم
 * پروفایل» به فاز رسانه موکول می‌شود.
 */
class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'هویت';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'پروفایل';

    protected static ?string $pluralModelLabel = 'پروفایل‌ها';

    protected static ?string $recordTitleAttribute = 'display_name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('هویت پایه')
                ->schema([
                    Forms\Components\TextInput::make('display_name')
                        ->label('نام نمایشی')
                        ->required()
                        ->maxLength(160),
                    Forms\Components\TextInput::make('professional_title')
                        ->label('عنوان حرفه‌ای')
                        ->maxLength(200),
                    Forms\Components\Textarea::make('short_bio')
                        ->label('معرفی کوتاه')
                        ->maxLength(500)
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('زندگی‌نامه')
                ->schema([
                    Ui::richEditor('biography', 'متن کامل زندگی‌نامه'),
                    Forms\Components\Textarea::make('positioning_statement')
                        ->label('بیانیه‌ی جایگاه')
                        ->helperText('یک جمله که نشان می‌دهد چه مسئله‌ای را حل می‌کنید.')
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('philosophy')
                        ->label('فلسفه‌ی کاری')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('ارزش‌ها و مأموریت')
                ->schema([
                    Forms\Components\TagsInput::make('core_values')
                        ->label('ارزش‌های اصلی')
                        ->helperText('هر ارزش را جدا وارد کنید. این‌ها در بخش «رویکرد من» نمایش داده می‌شوند.')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('current_mission')
                        ->label('مأموریت فعلی')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('حضور و تماس')
                ->schema([
                    Forms\Components\TextInput::make('availability_status')
                        ->label('وضعیت همکاری')
                        ->helperText('مثلاً «آماده‌ی همکاری» یا «مشغول».')
                        ->maxLength(40),
                    Forms\Components\TextInput::make('location_label')
                        ->label('موقعیت مکانی')
                        ->maxLength(160),
                    Forms\Components\TextInput::make('email_public')
                        ->label('ایمیل عمومی')
                        ->email()
                        ->maxLength(190)
                        ->helperText('این ایمیل در صفحات عمومی نمایش داده می‌شود. ایمیل خصوصی نگذارید.'),
                ])->columns(2),

            Forms\Components\Section::make('رسانه')
                ->description('تصویر و رزومه از کتابخانه‌ی رسانه انتخاب می‌شوند. برای آپلود فایل جدید ابتدا در بخش «رسانه» آن را اضافه کنید.')
                ->schema([
                    Forms\Components\Select::make('avatar_media_id')
                        ->label('تصویر پروفایل')
                        ->options(fn (): array => self::imageOptions())
                        ->searchable()
                        ->native(false)
                        ->placeholder('بدون تصویر'),
                    Forms\Components\Select::make('resume_media_id')
                        ->label('رزومه (PDF)')
                        ->options(fn (): array => self::pdfOptions())
                        ->searchable()
                        ->native(false)
                        ->placeholder('بدون رزومه'),
                ])->columns(2),

            Forms\Components\Section::make('وضعیت')
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('پروفایل فعال')
                        ->helperText('فقط یک پروفایل می‌تواند فعال باشد. برای اطمینان از دکمه‌ی «فعال‌سازی انحصاری» در فهرست استفاده کنید.')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('نام نمایشی')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('professional_title')
                    ->label('عنوان حرفه‌ای')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('availability_status')
                    ->label('وضعیت همکاری')
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
                Ui::dateColumn('updated_at', 'آخرین ویرایش'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('فعال'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\Action::make('activateExclusively')
                    ->label('فعال‌سازی انحصاری')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('فعال‌سازی این پروفایل')
                    ->modalDescription('این پروفایل فعال می‌شود و هر پروفایل فعال دیگری غیرفعال می‌گردد. صفحه‌ی اصلی فقط همین یک هویت را نشان می‌دهد.')
                    ->visible(fn (Profile $record): bool => ! $record->is_active)
                    ->action(function (Profile $record): void {
                        // فعال‌سازی انحصاری در یک تراکنش در خود مدل انجام
                        // می‌شود تا هرگز دو پروفایل فعال نمانند.
                        $record->activateExclusively();

                        Notification::make()
                            ->title('پروفایل فعال شد')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfiles::route('/'),
            'create' => Pages\CreateProfile::route('/create'),
            'edit' => Pages\EditProfile::route('/{record}/edit'),
        ];
    }

    /**
     * گزینه‌های تصویر — فقط رسانه‌های تصویری.
     *
     * @return array<int, string>
     */
    private static function imageOptions(): array
    {
        return Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (Media $m): array => [$m->id => $m->alt()])
            ->all();
    }

    /**
     * گزینه‌های PDF — فقط رزومه‌ی قابل قبول.
     *
     * @return array<int, string>
     */
    private static function pdfOptions(): array
    {
        return Media::query()
            ->where('mime_type', 'application/pdf')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (Media $m): array => [$m->id => $m->original_name ?: $m->path])
            ->all();
    }
}
