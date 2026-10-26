<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ContactChannelType;
use App\Enums\ContactPurpose;
use App\Filament\Resources\ContactChannelResource\Pages;
use App\Filament\Support\Ui;
use App\Models\ContactChannel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * کانال ارتباطی عمومی.
 *
 * ── قاعده‌ی نمایش عمومی ──
 * فقط کانال‌هایی که هم `is_active` و هم `is_public` هستند در سایت
 * عمومی دیده می‌شوند (scope `public()` مدل). این قاعده در کوئری است،
 * نه در Blade — «پنهان‌کردن در نما» محافظت نیست. فرم و جدول هر دو
 * این دو پرچم را برجسته نشان می‌دهند تا مدیر بداند چرا کانالی
 * نمایش داده نمی‌شود.
 */
class ContactChannelResource extends Resource
{
    protected static ?string $model = ContactChannel::class;

    protected static ?string $navigationIcon = 'heroicon-o-at-symbol';

    protected static ?string $navigationGroup = 'نمایش';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'کانال ارتباطی';

    protected static ?string $pluralModelLabel = 'کانال‌های ارتباطی';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('کانال')
                ->schema([
                    Forms\Components\TextInput::make('label')
                        ->label('برچسب')
                        ->required()
                        ->maxLength(100)
                        ->helperText('متن قابل مشاهده، مثل «ایمیل» یا «لینکدین».'),
                    Forms\Components\Select::make('channel_type')
                        ->label('نوع کانال')
                        ->options(ContactChannelType::options())
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('url')
                        ->label('نشانی / مقدار')
                        ->required()
                        ->maxLength(2048)
                        ->helperText('برای ایمیل فقط آدرس را بنویسید (بدون mailto:). برای وب‌سایت و شبکه‌ی اجتماعی، نشانی کامل یا دامنه.'),
                    Forms\Components\TextInput::make('handle')
                        ->label('شناسه (handle)')
                        ->maxLength(180)
                        ->helperText('نام کاربری بدون @ — برای نمایش، اختیاری.'),
                    Forms\Components\Select::make('purpose')
                        ->label('هدف ارتباط')
                        ->options(ContactPurpose::options())
                        ->required()
                        ->native(false),
                ])->columns(2),

            Forms\Components\Section::make('نمایش')
                ->schema([
                    Forms\Components\Toggle::make('is_public')
                        ->label('عمومی')
                        ->default(true)
                        ->helperText('اگر خاموش باشد، این کانال هرگز در سایت عمومی دیده نمی‌شود.'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('فعال')
                        ->default(true),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('ترتیب نمایش')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Placeholder::make('visibility_note')
                        ->hiddenLabel()
                        ->content('فقط کانال‌هایی که هم «عمومی» و هم «فعال» هستند در سایت نمایش داده می‌شوند.')
                        ->columnSpanFull(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('برچسب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('channel_type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => Ui::enumLabel($state))
                    ->color('info'),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('هدف')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => Ui::enumLabel($state))
                    ->color('gray'),
                Tables\Columns\TextColumn::make('url')
                    ->label('نشانی')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label('عمومی')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتیب')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('channel_type')
                    ->label('نوع')
                    ->options(ContactChannelType::options()),
                Tables\Filters\TernaryFilter::make('is_public')->label('عمومی'),
                Tables\Filters\TernaryFilter::make('is_active')->label('فعال'),
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
            'index' => Pages\ListContactChannels::route('/'),
            'create' => Pages\CreateContactChannel::route('/create'),
            'edit' => Pages\EditContactChannel::route('/{record}/edit'),
        ];
    }
}
