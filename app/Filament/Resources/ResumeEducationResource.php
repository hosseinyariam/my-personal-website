<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ResumeEducationResource\Pages;
use App\Models\ResumeEducation;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ResumeEducationResource extends Resource
{
    protected static ?string $model = ResumeEducation::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'رزومه';

    protected static ?string $modelLabel = 'تحصیلات';

    protected static ?string $pluralModelLabel = 'تحصیلات';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                TextInput::make('degree')->label('مقطع و رشته')->required()->maxLength(100),
                TextInput::make('institution')->label('دانشگاه / مؤسسه')->required()->maxLength(100),
                TextInput::make('field')->label('گرایش')->maxLength(100),
                TextInput::make('started_year')
                    ->label('سال شروع (شمسی)')
                    ->numeric()->required()->minValue(1300)->maxValue(1500),
                TextInput::make('ended_year')
                    ->label('سال پایان (شمسی)')
                    ->numeric()->minValue(1300)->maxValue(1500)
                    ->helperText('خالی = در حال تحصیل'),
            ]),

            Textarea::make('description')->label('توضیح')->rows(3)->columnSpanFull(),

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
                TextColumn::make('degree')->label('مقطع')->searchable()->weight('bold')
                    ->description(fn (ResumeEducation $r) => $r->institution),
                TextColumn::make('period')->label('بازه')->state(fn (ResumeEducation $r) => $r->period()),
                ToggleColumn::make('is_active')->label('فعال'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResumeEducations::route('/'),
            'create' => Pages\CreateResumeEducation::route('/create'),
            'edit' => Pages\EditResumeEducation::route('/{record}/edit'),
        ];
    }
}
