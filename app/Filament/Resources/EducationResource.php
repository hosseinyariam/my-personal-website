<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PublicationStatus;
use App\Filament\Resources\EducationResource\Pages;
use App\Filament\Support\PublishesContent;
use App\Filament\Support\Ui;
use App\Models\Education;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * تحصیلات.
 *
 * توجه: جدول `education` مفرد است (مدل صریحاً تنظیم کرده). اینجا هیچ
 * کاری لازم نیست چون Resource از مدل پیروی می‌کند.
 */
class EducationResource extends Resource
{
    use PublishesContent;

    protected static ?string $model = Education::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'هویت';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'تحصیلات';

    protected static ?string $pluralModelLabel = 'تحصیلات';

    protected static ?string $recordTitleAttribute = 'institution';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('اطلاعات تحصیلی')
                ->schema([
                    Forms\Components\TextInput::make('institution')
                        ->label('مؤسسه')
                        ->required()
                        ->maxLength(180),
                    Forms\Components\TextInput::make('degree')
                        ->label('مدرک')
                        ->maxLength(160),
                    Forms\Components\TextInput::make('field_of_study')
                        ->label('رشته')
                        ->maxLength(180),
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
                        ->label('در حال تحصیل'),
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
                Tables\Columns\TextColumn::make('institution')
                    ->label('مؤسسه')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('degree')
                    ->label('مدرک')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('field_of_study')
                    ->label('رشته')
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_current')
                    ->label('در حال تحصیل')
                    ->boolean(),
                Ui::statusColumn('status', 'وضعیت', 'publication'),
                Ui::dateColumn('started_at', 'شروع'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(PublicationStatus::options()),
                Tables\Filters\TernaryFilter::make('is_current')->label('در حال تحصیل'),
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
            'index' => Pages\ListEducation::route('/'),
            'create' => Pages\CreateEducation::route('/create'),
            'edit' => Pages\EditEducation::route('/{record}/edit'),
        ];
    }
}
