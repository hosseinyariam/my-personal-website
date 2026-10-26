<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PublicationStatus;
use App\Filament\Resources\AchievementResource\Pages;
use App\Filament\Support\PublishesContent;
use App\Filament\Support\Ui;
use App\Models\Achievement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * دستاورد.
 *
 * ── قاعده‌ی سخت «هیچ دستاوردی بدون شاهد» ──
 * scope `evidenced` روی مدل فقط دستاوردهایی را برای نمایش عمومی
 * می‌پذیرد که `evidence_url` داشته باشند **یا** توضیح واقعی. این
 * سخت‌گیری آگاهانه است چون «دستاورد» بیشترین ریسک ادعای بی‌پشتوانه
 * را دارد. فرم با راهنمای صریح همین را به مدیر یادآوری می‌کند.
 */
class AchievementResource extends Resource
{
    use PublishesContent;

    protected static ?string $model = Achievement::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'هویت';

    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'دستاورد';

    protected static ?string $pluralModelLabel = 'دستاوردها';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('دستاورد')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('عنوان')
                        ->required()
                        ->maxLength(180),
                    Forms\Components\DatePicker::make('achieved_at')
                        ->label('تاریخ کسب'),
                    Forms\Components\TextInput::make('evidence_url')
                        ->label('نشانی شاهد')
                        ->url()
                        ->maxLength(2048)
                        ->helperText('لینک به مدرک، خبر، مخزن یا گواهی. دستاورد بدون شاهد (یا حداقل توضیح واقعی) در سایت عمومی منتشر نمی‌شود.'),
                    Forms\Components\Textarea::make('description')
                        ->label('توضیحات')
                        ->helperText('توضیح واقعی و قابل دفاع. اگر شاهد لینکی ندارید، این متن جایگزین شاهد است.')
                        ->rows(4)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('انتشار')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('وضعیت انتشار')
                        ->options(PublicationStatus::options())
                        ->required()
                        ->native(false),
                    Forms\Components\Toggle::make('is_featured')
                        ->label('ویژه'),
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
                    ->label('عنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\IconColumn::make('evidence_url')
                    ->label('شاهد')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->tooltip('داشتن نشانی شاهد. بدون شاهد، فقط در صورت وجود توضیح واقعی منتشر می‌شود.'),
                Ui::statusColumn('status', 'وضعیت', 'publication'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('ویژه')
                    ->boolean(),
                Ui::dateColumn('achieved_at', 'تاریخ'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(PublicationStatus::options()),
                Tables\Filters\TernaryFilter::make('is_featured')->label('ویژه'),
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
            'index' => Pages\ListAchievements::route('/'),
            'create' => Pages\CreateAchievement::route('/create'),
            'edit' => Pages\EditAchievement::route('/{record}/edit'),
        ];
    }
}
