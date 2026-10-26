<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TechnologyResource\Pages;
use App\Models\Technology;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * فناوری.
 *
 * فناوری توضیح‌دهنده است نه فخر‌فروش؛ در سایت عمومی در کنار پروژه‌ای
 * که با آن ساخته شده معنا پیدا می‌کند (scope `evidenced`).
 */
class TechnologyResource extends Resource
{
    protected static ?string $model = Technology::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'هویت';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'فناوری';

    protected static ?string $pluralModelLabel = 'فناوری‌ها';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('فناوری')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('نام')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\TextInput::make('slug')
                        ->label('نامک (slug)')
                        ->maxLength(140)
                        ->helperText('اختیاری. اگر خالی بماند، هنگام ذخیره به‌طور خودکار از نام ساخته می‌شود.')
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('category')
                        ->label('دسته')
                        ->maxLength(80),
                    Forms\Components\TextInput::make('website_url')
                        ->label('وب‌سایت')
                        ->url()
                        ->maxLength(2048),
                    Forms\Components\TextInput::make('icon_key')
                        ->label('کلید نماد')
                        ->helperText('شناسه‌ی نماد برای نمایش در سایت. اختیاری.')
                        ->maxLength(80),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('ترتیب نمایش')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_active')
                        ->label('فعال')
                        ->default(true),
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
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('category')
                    ->label('دسته')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('projects_count')
                    ->label('شواهد (پروژه)')
                    ->counts('projects')
                    ->badge()
                    ->color(fn (Technology $record): string => $record->projects_count > 0 ? 'success' : 'warning'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
            ])
            ->filters([
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
            'index' => Pages\ListTechnologies::route('/'),
            'create' => Pages\CreateTechnology::route('/create'),
            'edit' => Pages\EditTechnology::route('/{record}/edit'),
        ];
    }
}
