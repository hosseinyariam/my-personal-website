<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Filament\Support\Ui;
use App\Models\ContactMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * صندوق پیام‌های فرم تماس — فقط خواندنی.
 *
 * ── چرا ایجاد و ویرایش ممنوع است ──
 * پیام تماس از فرم عمومی می‌آید. اجازه‌ی ساخت دستی، سابقه‌ی واقعی
 * مکاتبه را آلوده می‌کند. متن پیام و فراداده‌ی فرستنده هم قابل ویرایش
 * نیستند — فقط وضعیت پردازش (خوانده/اسپم) تغییر می‌کند.
 *
 * ── چرا «بازفرست اعلان» وجود ندارد ──
 * مسیر ارسال اعلان در `ContactService::sendNotification()` **private**
 * است و برای جلوگیری از ارسال تکراری/سوءاستفاده، پارامتر عمومی ندارد.
 * صدا زدنش از پنل یعنی یا کپی‌کردن منطق (دو منبع حقیقت) یا بازکردن
 * دسترسی عمومی به آن (ریسک). هیچ‌کدام درست نیست. به‌جایش، پیام‌هایی
 * که ارسال ایمیلشان شکست خورده با یک نشان برجسته‌ی «ارسال نشده»
 * در جدول مشخص می‌شوند تا مدیر دستی پیگیری کند — دقیقاً همان‌طور
 * که کامنت مدل ContactMessage خواسته.
 */
class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'نمایش';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'پیام تماس';

    protected static ?string $pluralModelLabel = 'پیام‌های تماس';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        // فرم فقط برای نمایش در ویرایش استفاده می‌شود و همه‌ی فیلدها
        // غیرقابل ویرایش‌اند — فقط وضعیت پردازش تغییر می‌کند.
        return $form->schema([
            Forms\Components\Section::make('پیام')
                ->schema([
                    Forms\Components\TextInput::make('name')->label('نام')->disabled(),
                    Forms\Components\TextInput::make('email')->label('ایمیل')->disabled(),
                    Forms\Components\TextInput::make('purpose')->label('موضوع')->disabled(),
                    Forms\Components\Textarea::make('message')->label('متن پیام')->rows(8)->disabled()->columnSpanFull(),
                ])->columns(2),
            Forms\Components\Section::make('پردازش')
                ->schema([
                    Forms\Components\Toggle::make('is_read')->label('خوانده‌شده'),
                    Forms\Components\Toggle::make('is_spam')->label('اسپم'),
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
                Tables\Columns\TextColumn::make('email')
                    ->label('ایمیل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('موضوع')
                    ->placeholder('—'),
                Ui::dateColumn('created_at', 'زمان دریافت'),
                Tables\Columns\IconColumn::make('is_read')
                    ->label('خوانده')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_spam')
                    ->label('اسپم')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray'),
                // ── نشان «ارسال نشده» ──
                // الزام مستندات: شکست ارسال ایمیل باید قابل مشاهده باشد.
                // پیام ذخیره شده ولی ایمیل نرفته، نیازمند پیگیری دستی است.
                Tables\Columns\TextColumn::make('mail_sent')
                    ->label('ارسال ایمیل')
                    ->state(fn (ContactMessage $record): string => $record->mail_sent ? 'ارسال شد' : 'ارسال نشده')
                    ->badge()
                    ->color(fn (ContactMessage $record): string => $record->mail_sent ? 'success' : 'danger')
                    ->tooltip('اگر «ارسال نشده» است، SMTP شکست خورده و پیام نیازمند پیگیری دستی است.'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_read')->label('خوانده‌شده'),
                Tables\Filters\TernaryFilter::make('is_spam')->label('اسپم'),
                Tables\Filters\TernaryFilter::make('mail_sent')->label('ارسال ایمیل'),
            ])
            ->actions([
                Tables\Actions\Action::make('markAsRead')
                    ->label('علامت‌گذاری خوانده‌شده')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (ContactMessage $record): bool => ! $record->is_read)
                    ->action(fn (ContactMessage $record) => $record->markAsRead()),
                Tables\Actions\Action::make('toggleSpam')
                    ->label(fn (ContactMessage $record): string => $record->is_spam ? 'حذف علامت اسپم' : 'علامت‌گذاری اسپم')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('warning')
                    ->action(function (ContactMessage $record): void {
                        $record->forceFill(['is_spam' => ! $record->is_spam])->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function canCreate(): bool
    {
        // پیام تماس فقط از فرم عمومی می‌آید؛ ساخت دستی مجاز نیست.
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'edit' => Pages\EditContactMessage::route('/{record}/edit'),
        ];
    }
}
