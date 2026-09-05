<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ResumeCertificateResource\Pages;
use App\Models\ResumeCertificate;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ResumeCertificateResource extends Resource
{
    protected static ?string $model = ResumeCertificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'رزومه';

    protected static ?string $modelLabel = 'گواهی';

    protected static ?string $pluralModelLabel = 'گواهی‌ها';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                TextInput::make('title')->label('عنوان گواهی')->required()->maxLength(120),
                TextInput::make('issuer')->label('صادرکننده')->required()->maxLength(100),
                TextInput::make('year')
                    ->label('سال (شمسی)')
                    ->numeric()->required()->minValue(1300)->maxValue(1500),
                TextInput::make('url')
                    ->label('لینک تأیید')
                    ->url()->maxLength(255)
                    ->helperText('اختیاری — با وجود لینک، عنوان گواهی قابل کلیک می‌شود.'),
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
                TextColumn::make('title')->label('عنوان')->searchable()->weight('bold')
                    ->description(fn (ResumeCertificate $r) => $r->issuer),
                TextColumn::make('year')->label('سال')->formatStateUsing(fn ($s) => fa_digits($s)),
                ToggleColumn::make('is_active')->label('فعال'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResumeCertificates::route('/'),
            'create' => Pages\CreateResumeCertificate::route('/create'),
            'edit' => Pages\EditResumeCertificate::route('/{record}/edit'),
        ];
    }
}
