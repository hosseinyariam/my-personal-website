<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ProjectStatus;
use App\Enums\Visibility;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Support\PublishesContent;
use App\Filament\Support\Ui;
use App\Models\Project;
use App\Support\Persian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * پروژه — سابقه‌ی ساخت یک چیز.
 *
 * ── هشدار بنیادی درباره‌ی انتشار ──
 * `status` این مدل چرخه‌ی **دامنه** است (ایده / در حال ساخت / تکمیل)،
 * نه وضعیت انتشار (تصمیم D17 و کامنت مدل). پس فرم یک فیلد وضعیت
 * انتشار جداگانه **ندارد** — چون در دیتابیس وجود ندارد. انتشار فقط
 * با `published_at` (+ `visibility`) تعیین می‌شود. اکشن‌های انتشار
 * از `PublishingService` استفاده می‌کنند که همین دو را مدیریت می‌کند.
 */
class ProjectResource extends Resource
{
    use PublishesContent;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'نمونه‌کار';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'پروژه';

    protected static ?string $pluralModelLabel = 'پروژه‌ها';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('پروژه')
                ->columnSpanFull()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('اطلاعات پایه')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('عنوان')
                                ->required()
                                ->maxLength(180),
                            Ui::slugField(),
                            Forms\Components\Textarea::make('summary')
                                ->label('خلاصه')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Tabs\Tab::make('مسئله و زمینه')
                        ->schema([
                            Forms\Components\Textarea::make('problem')
                                ->label('مسئله')
                                ->helperText('چه مسئله‌ی واقعی قرار بود حل شود؟')
                                ->rows(4)
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('context')
                                ->label('زمینه')
                                ->helperText('شرایط، محدودیت‌ها و فرض‌های شروع.')
                                ->rows(4)
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Tabs\Tab::make('نقش و مشارکت')
                        ->schema([
                            Forms\Components\TextInput::make('role')
                                ->label('نقش')
                                ->helperText('نقش واقعی شما، نه عنوان آرمانی.')
                                ->maxLength(180),
                            Forms\Components\Textarea::make('contribution')
                                ->label('مشارکت')
                                ->rows(4)
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Tabs\Tab::make('نتیجه و آموخته')
                        ->schema([
                            Forms\Components\Textarea::make('outcome')
                                ->label('نتیجه')
                                ->helperText('نتیجه‌ی قابل دفاع — نه ادعای بی‌پشتوانه.')
                                ->rows(4)
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('lessons_learned')
                                ->label('آموخته‌ها')
                                ->rows(4)
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Tabs\Tab::make('روابط')
                        ->schema([
                            Forms\Components\Select::make('companies')
                                ->label('شرکت‌ها')
                                ->relationship('companies', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false),
                            Forms\Components\Select::make('products')
                                ->label('محصولات')
                                ->relationship('products', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false),
                            Forms\Components\Select::make('skills')
                                ->label('مهارت‌ها')
                                ->relationship('skills', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false),
                            Forms\Components\Select::make('technologies')
                                ->label('فناوری‌ها')
                                ->relationship('technologies', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false),
                            Forms\Components\Select::make('notes')
                                ->label('یادداشت‌های مرتبط')
                                ->relationship('notes', 'title')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('لینک‌ها')
                        ->schema([
                            Forms\Components\TextInput::make('repository_url')
                                ->label('نشانی مخزن')
                                ->url()
                                ->maxLength(2048),
                            Forms\Components\TextInput::make('live_url')
                                ->label('نشانی زنده')
                                ->url()
                                ->maxLength(2048),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('انتشار')
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('وضعیت دامنه')
                                ->helperText('چرخه‌ی ساخت پروژه. این «وضعیت انتشار» نیست.')
                                ->options(ProjectStatus::options())
                                ->required()
                                ->native(false),
                            Forms\Components\Select::make('visibility')
                                ->label('سطح دسترسی')
                                ->options(Visibility::options())
                                ->required()
                                ->native(false),
                            Forms\Components\DateTimePicker::make('published_at')
                                ->label('زمان انتشار')
                                ->helperText('تا وقتی این مقدار خالی باشد، پروژه در سایت عمومی دیده نمی‌شود. تاریخ آینده یعنی زمان‌بندی.')
                                ->seconds(false),
                            Forms\Components\Toggle::make('is_featured')
                                ->label('ویژه (نمایش در صفحه اصلی)'),
                            Forms\Components\TextInput::make('sort_order')
                                ->label('ترتیب نمایش')
                                ->numeric()
                                ->default(0),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('تصاویر')
                        ->schema([
                            Forms\Components\Select::make('media')
                                ->label('تصاویر پیوست')
                                ->relationship('media', 'original_name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->helperText('از کتابخانه‌ی رسانه انتخاب کنید. تصویر شاخص، اولین مورد است.')
                                ->columnSpanFull(),
                        ]),
                ]),
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
                Ui::statusColumn('status', 'وضعیت', 'project'),
                Ui::statusColumn('visibility', 'دسترسی', 'visibility'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('ویژه')
                    ->boolean(),
                Ui::dateColumn('published_at', 'انتشار'),
                Tables\Columns\TextColumn::make('completeness')
                    ->label('کامل بودن')
                    ->state(fn (Project $record): string => Persian::digits($record->completeness()).'٪')
                    ->badge()
                    ->color(fn (Project $record): string => match (true) {
                        $record->completeness() >= 80 => 'success',
                        $record->completeness() >= 40 => 'warning',
                        default => 'gray',
                    })
                    ->tooltip('درصد کامل‌بودن پرونده‌ی پروژه — نمایش عمومی به آن کاری ندارد.'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(ProjectStatus::options()),
                Tables\Filters\SelectFilter::make('visibility')
                    ->label('دسترسی')
                    ->options(Visibility::options()),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
