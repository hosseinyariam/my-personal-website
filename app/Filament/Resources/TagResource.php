<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Filament\Support\Ui;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * برچسب.
 *
 * برچسب با دسته فرق دارد: دسته سلسله‌مراتب موضوعی است، برچسب ابزار
 * اتصال عرضی. جدول فقط نام و نامک دارد.
 */
class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'محتوا';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'برچسب';

    protected static ?string $pluralModelLabel = 'برچسب‌ها';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('برچسب')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('نام')
                        ->required()
                        ->maxLength(100),
                    Ui::slugField('name'),
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
                Tables\Columns\TextColumn::make('slug')
                    ->label('نامک')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('notes_count')
                    ->label('یادداشت‌ها')
                    ->counts('notes')
                    ->badge()
                    ->color(fn (Tag $record): string => $record->notes_count > 0 ? 'success' : 'gray'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
