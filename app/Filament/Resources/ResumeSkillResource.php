<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\SkillCategory;
use App\Filament\Resources\ResumeSkillResource\Pages;
use App\Models\ResumeSkill;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ResumeSkillResource extends Resource
{
    protected static ?string $model = ResumeSkill::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'رزومه';

    protected static ?string $modelLabel = 'مهارت';

    protected static ?string $pluralModelLabel = 'مهارت‌ها';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                TextInput::make('name')->label('مهارت')->required()->maxLength(60),
                Select::make('category')
                    ->label('دسته')
                    ->options(SkillCategory::options())
                    ->required()
                    ->native(false)
                    ->helperText('در صفحه‌ی رزومه هر دسته جداگانه گروه‌بندی می‌شود.'),
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
            ->groups(['category'])
            ->columns([
                TextColumn::make('name')->label('مهارت')->searchable()->weight('bold'),
                TextColumn::make('category')->label('دسته')->badge()
                    ->formatStateUsing(fn (SkillCategory $s) => $s->label()),
                ToggleColumn::make('is_active')->label('فعال'),
            ])
            ->filters([
                SelectFilter::make('category')->label('دسته')->options(SkillCategory::options()),
            ])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResumeSkills::route('/'),
            'create' => Pages\CreateResumeSkill::route('/create'),
            'edit' => Pages\EditResumeSkill::route('/{record}/edit'),
        ];
    }
}
