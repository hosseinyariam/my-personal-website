<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PublicationStatus;
use App\Enums\TimelineEventType;
use App\Enums\Visibility;
use App\Filament\Resources\TimelineEventResource\Pages;
use App\Filament\Support\PublishesContent;
use App\Filament\Support\Ui;
use App\Models\Company;
use App\Models\Note;
use App\Models\Product;
use App\Models\Project;
use App\Models\TimelineEvent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * رویداد مسیر حرفه‌ای.
 *
 * ── پیاده‌سازی «هدف چندریختی» ──
 * تصمیم D5: هر رویداد دقیقاً به یک موجودیت اصلی وصل می‌شود. رابط
 * به دو فیلد شکسته می‌شود: یک Select برای **نوع** هدف و یک Select
 * جست‌وجوپذیر برای **رکورد** آن نوع. این ساده و صریح است و از
 * ساخت یک select غول‌آسای ترکیبی که تجربه‌ی کاربری بدی دارد
 * پرهیز می‌کند. نوع با `getMorphClass()` ذخیره می‌شود — همان مقداری
 * که خود Eloquent برای رابطه‌ی morph استفاده می‌کند.
 */
class TimelineEventResource extends Resource
{
    use PublishesContent;

    protected static ?string $model = TimelineEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'مسیر';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'رویداد مسیر';

    protected static ?string $pluralModelLabel = 'رویدادهای مسیر';

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * نگاشت نوع هدف به کلاس مدل — allowlist صریح.
     *
     * @var array<string, class-string>
     */
    private const TARGET_TYPES = [
        'project' => Project::class,
        'product' => Product::class,
        'company' => Company::class,
        'note' => Note::class,
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('رویداد')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('عنوان')
                        ->required()
                        ->maxLength(180),
                    Forms\Components\Select::make('event_type')
                        ->label('نوع رخداد')
                        ->options(TimelineEventType::options())
                        ->required()
                        ->native(false),
                    Forms\Components\Textarea::make('summary')
                        ->label('توضیح')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\DatePicker::make('occurred_at')
                        ->label('تاریخ وقوع'),
                    Forms\Components\DatePicker::make('ended_at')
                        ->label('تاریخ پایان')
                        ->helperText('برای رویدادهای دارای بازه. اختیاری.'),
                ])->columns(2),

            Forms\Components\Section::make('اتصال به موجودیت')
                ->description('اتصال اختیاری به یک موجودیت اصلی. مسیر حرفه‌ای باید گزینش‌شده بماند، نه لاگ هر تغییر.')
                ->schema([
                    Forms\Components\Select::make('timelineable_type_key')
                        ->label('نوع هدف')
                        ->options([
                            'project' => 'پروژه',
                            'product' => 'محصول',
                            'company' => 'شرکت',
                            'note' => 'یادداشت',
                        ])
                        ->live()
                        ->afterStateHydrated(function (Forms\Components\Select $component, ?TimelineEvent $record): void {
                            // تبدیل مقدار ذخیره‌شده (کلاس کامل) به کلید کوتاه
                            if ($record !== null && filled($record->timelineable_type)) {
                                $component->state(array_search($record->timelineable_type, self::TARGET_TYPES, true) ?: null);
                            }
                        })
                        ->native(false),
                    Forms\Components\Select::make('timelineable_id')
                        ->label('رکورد هدف')
                        ->options(fn (Forms\Get $get): array => self::targetOptions($get('timelineable_type_key')))
                        ->searchable()
                        ->native(false)
                        ->visible(fn (Forms\Get $get): bool => filled($get('timelineable_type_key')))
                        ->afterStateHydrated(function (Forms\Components\Select $component, ?TimelineEvent $record): void {
                            if ($record !== null && filled($record->timelineable_id)) {
                                $component->state($record->timelineable_id);
                            }
                        }),
                ])->columns(2),

            Forms\Components\Section::make('انتشار')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('وضعیت انتشار')
                        ->options(PublicationStatus::options())
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('visibility')
                        ->label('سطح دسترسی')
                        ->options(Visibility::options())
                        ->required()
                        ->native(false),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('زمان انتشار')
                        ->helperText('تا وقتی خالی باشد، رویداد در سایت عمومی دیده نمی‌شود.')
                        ->seconds(false),
                    Forms\Components\Toggle::make('is_highlight')
                        ->label('نقطه‌ی عطف (نمایش برجسته)')
                        ->helperText('فقط موارد برجسته به‌عنوان نقطه‌ی عطف در صفحه‌ی مسیر نمایش داده می‌شوند.'),
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
                Tables\Columns\TextColumn::make('event_type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => Ui::enumLabel($state))
                    ->color('info'),
                Ui::statusColumn('status', 'وضعیت', 'publication'),
                Ui::statusColumn('visibility', 'دسترسی', 'visibility'),
                Tables\Columns\IconColumn::make('is_highlight')
                    ->label('برجسته')
                    ->boolean(),
                Ui::dateColumn('occurred_at', 'وقوع'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->label('نوع')
                    ->options(TimelineEventType::options()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(PublicationStatus::options()),
                Tables\Filters\TernaryFilter::make('is_highlight')->label('برجسته'),
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
            'index' => Pages\ListTimelineEvents::route('/'),
            'create' => Pages\CreateTimelineEvent::route('/create'),
            'edit' => Pages\EditTimelineEvent::route('/{record}/edit'),
        ];
    }

    /**
     * گزینه‌های رکورد هدف بر اساس نوع انتخاب‌شده.
     *
     * @return array<int, string>
     */
    private static function targetOptions(?string $typeKey): array
    {
        $class = self::TARGET_TYPES[$typeKey] ?? null;

        if ($class === null) {
            return [];
        }

        $titleColumn = $class === Project::class || $class === Note::class ? 'title' : 'name';

        return $class::query()
            ->orderBy($titleColumn)
            ->limit(300)
            ->pluck($titleColumn, 'id')
            ->all();
    }
}
