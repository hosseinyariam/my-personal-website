<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\EmploymentType;
use App\Enums\PublicationStatus;
use App\Filament\Resources\ExperienceResource\Pages;
use App\Filament\Support\PublishesContent;
use App\Filament\Support\Ui;
use App\Models\Experience;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * سابقه‌ی کاری.
 *
 * `company_id` با `nullOnDelete` بسته شده، پس حذف شرکت سابقه را پاک
 * نمی‌کند — ولی در فرم، انتخاب شرکت اختیاری است تا سابقه‌ی بدون شرکت
 * (مثلاً پروژه‌ی مستقل) هم ثبت شود.
 */
class ExperienceResource extends Resource
{
    use PublishesContent;

    protected static ?string $model = Experience::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'هویت';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'سابقه‌ی کاری';

    protected static ?string $pluralModelLabel = 'سوابق کاری';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('اطلاعات شغلی')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('عنوان شغلی')
                        ->required()
                        ->maxLength(180),
                    Forms\Components\Select::make('company_id')
                        ->label('شرکت')
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false),
                    Forms\Components\Select::make('employment_type')
                        ->label('نوع همکاری')
                        ->options(EmploymentType::options())
                        ->native(false),
                    Forms\Components\TextInput::make('location_label')
                        ->label('موقعیت مکانی')
                        ->maxLength(160),
                    Forms\Components\Textarea::make('description')
                        ->label('توضیحات')
                        ->rows(4)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('بازه و انتشار')
                ->schema([
                    Forms\Components\DatePicker::make('started_at')
                        ->label('تاریخ شروع'),
                    Forms\Components\DatePicker::make('ended_at')
                        ->label('تاریخ پایان'),
                    Forms\Components\Toggle::make('is_current')
                        ->label('همکاری فعلی'),
                    Forms\Components\Select::make('status')
                        ->label('وضعیت انتشار')
                        ->options(PublicationStatus::options())
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('ترتیب نمایش')
                        ->numeric()
                        ->default(0),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان شغلی')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('شرکت')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('employment_type')
                    ->label('نوع همکاری')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => Ui::enumLabel($state))
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_current')
                    ->label('فعلی')
                    ->boolean(),
                Ui::statusColumn('status', 'وضعیت', 'publication'),
                Ui::dateColumn('started_at', 'شروع'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(PublicationStatus::options()),
                Tables\Filters\SelectFilter::make('employment_type')
                    ->label('نوع همکاری')
                    ->options(EmploymentType::options()),
                Tables\Filters\TernaryFilter::make('is_current')->label('فعلی'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                self::publishAction(),
                self::unpublishAction(),
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
            'index' => Pages\ListExperiences::route('/'),
            'create' => Pages\CreateExperience::route('/create'),
            'edit' => Pages\EditExperience::route('/{record}/edit'),
        ];
    }
}
