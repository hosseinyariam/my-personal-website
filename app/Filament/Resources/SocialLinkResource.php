<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SocialLinkResource\Pages;
use App\Models\SocialLink;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'تنظیمات';

    protected static ?string $modelLabel = 'لینک ارتباطی';

    protected static ?string $pluralModelLabel = 'لینک‌های ارتباطی';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                Select::make('platform')
                    ->label('کانال')
                    ->options([
                        'email' => 'ایمیل',
                        'telegram' => 'تلگرام',
                        'github' => 'گیت‌هاب',
                        'linkedin' => 'لینکدین',
                        'x' => 'ایکس (توییتر)',
                        'instagram' => 'اینستاگرام',
                        'youtube' => 'یوتیوب',
                        'phone' => 'تلفن',
                        'website' => 'وب‌سایت',
                    ])
                    ->required()
                    ->native(false)
                    ->helperText('آیکون هر کانال خودکار انتخاب می‌شود.'),

                TextInput::make('label')
                    ->label('برچسب نمایشی')
                    ->required()
                    ->maxLength(60)
                    ->placeholder('مثلاً: ایمیل'),
            ]),

            TextInput::make('url')
                ->label('نشانی')
                ->required()
                ->maxLength(255)
                ->helperText('برای ایمیل با mailto: و برای تلفن با tel: شروع کنید.')
                ->rule('regex:/^(https?:\/\/|mailto:|tel:)/'),

            Grid::make(2)->schema([
                TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
                Toggle::make('is_active')->label('فعال')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('label')->label('برچسب')->weight('bold'),
                TextColumn::make('url')->label('نشانی')->limit(45)->copyable(),
                ToggleColumn::make('is_active')->label('فعال'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->emptyStateHeading('هیچ کانال ارتباطی ثبت نشده')
            ->emptyStateDescription('حداقل یک کانال برای لانچ لازم است؛ تا آن زمان کارت‌های تماس مخفی می‌مانند.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialLinks::route('/'),
            'create' => Pages\CreateSocialLink::route('/create'),
            'edit' => Pages\EditSocialLink::route('/{record}/edit'),
        ];
    }
}
