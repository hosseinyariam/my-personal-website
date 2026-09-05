<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ContactPurpose;
use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

/**
 * صندوق پیام‌ها — دیتابیس منبع حقیقت است.
 *
 * ستون «ایمیل ارسال‌شده» ریسکِ شکست SMTP را قابل‌مشاهده می‌کند:
 * پیامی که ایمیلش نرفته، اینجا با نشان هشدار دیده می‌شود (US-M5-3).
 */
class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'پیام‌ها';

    protected static ?string $modelLabel = 'پیام';

    protected static ?string $pluralModelLabel = 'پیام‌های تماس';

    protected static ?int $navigationSort = 1;

    /** شمارنده‌ی پیام‌های خوانده‌نشده روی آیتم ناوبری. */
    public static function getNavigationBadge(): ?string
    {
        $unread = static::getModel()::query()->where('is_read', false)->count();

        return $unread > 0 ? fa_digits($unread) : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('فرستنده')->schema([
                TextEntry::make('name')->label('نام'),
                TextEntry::make('email')->label('ایمیل')->copyable(),
                TextEntry::make('purpose')->label('هدف تماس')->badge()
                    ->formatStateUsing(fn (ContactPurpose $s) => $s->label()),
                TextEntry::make('created_at')->label('زمان')
                    ->formatStateUsing(fn ($s) => fa_date($s).' · '.$s->format('H:i')),
            ])->columns(2),

            Section::make('پیام')->schema([
                TextEntry::make('message')->label('')->prose()->columnSpanFull(),
            ]),

            Section::make('فنی')->schema([
                TextEntry::make('ip')->label('IP')->placeholder('—'),
                TextEntry::make('user_agent')->label('مرورگر')->placeholder('—')->columnSpanFull(),
                TextEntry::make('mail_sent')->label('ایمیل اعلان')
                    ->formatStateUsing(fn (bool $s) => $s ? 'ارسال شد' : 'ارسال نشد')
                    ->badge()->color(fn (bool $s) => $s ? 'success' : 'danger'),
            ])->columns(2)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('is_read')
                    ->label('خوانده')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-s-envelope')
                    ->trueColor('gray')
                    ->falseColor('primary'),

                TextColumn::make('name')->label('فرستنده')->searchable()->weight('bold')
                    ->description(fn (ContactMessage $r) => $r->email),

                TextColumn::make('purpose')->label('هدف')->badge()
                    ->formatStateUsing(fn (ContactPurpose $s) => $s->label())
                    ->color(fn (ContactPurpose $s) => match ($s) {
                        ContactPurpose::Job => 'success',
                        ContactPurpose::Service => 'primary',
                        ContactPurpose::Other => 'gray',
                    }),

                TextColumn::make('message')->label('پیام')->limit(60)->wrap(),

                IconColumn::make('mail_sent')
                    ->label('ایمیل')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->falseColor('danger')
                    ->tooltip(fn (ContactMessage $r) => $r->mail_sent
                        ? 'ایمیل اعلان ارسال شد'
                        : 'ایمیل ارسال نشد — پیام فقط اینجا ثبت است'),

                TextColumn::make('created_at')->label('زمان')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('purpose')->label('هدف تماس')->options(ContactPurpose::options()),
                Filter::make('unread')->label('فقط خوانده‌نشده')
                    ->query(fn (Builder $q) => $q->where('is_read', false)),
                Filter::make('mail_failed')->label('ایمیل ارسال‌نشده')
                    ->query(fn (Builder $q) => $q->where('mail_sent', false)),
            ])
            ->actions([
                // مشاهده، پیام را خوانده‌شده علامت می‌زند
                ViewAction::make()->after(fn (ContactMessage $record) => $record->is_read
                    ? null
                    : $record->update(['is_read' => true])),

                Action::make('reply')
                    ->label('پاسخ با ایمیل')
                    ->icon('heroicon-m-arrow-uturn-right')
                    ->color('gray')
                    ->url(fn (ContactMessage $r) => 'mailto:'.$r->email.'?subject='.rawurlencode('پاسخ به پیام شما')),

                DeleteAction::make(),
            ])
            ->emptyStateHeading('هنوز پیامی نرسیده')
            ->emptyStateDescription('پیام‌های فرم تماس اینجا ثبت می‌شوند — حتی اگر ارسال ایمیل اعلان شکست بخورد.');
    }

    /** ثبت پیام فقط از فرم عمومی انجام می‌شود؛ ساختِ دستی معنا ندارد. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
        ];
    }
}
