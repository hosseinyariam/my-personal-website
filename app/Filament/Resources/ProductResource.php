<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ProductStatus;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Support\PublishesContent;
use App\Filament\Support\Ui;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * محصول — چیزی که ساخته و عرضه شده.
 *
 * ── هشدار انتشار ──
 * `status` اینجا چرخه‌ی **عرضه** است (ایده / توسعه / بتا / فعال)، نه
 * وضعیت انتشار. جدول محصول ستون `visibility` هم ندارد. پس فرم فقط
 * `status` دامنه و `published_at` دارد و انتشار با `published_at`
 * سنجیده می‌شود. هیچ فیلد وضعیت انتشار اضافه نمی‌شود.
 *
 * ── قاعده‌ی برند ──
 * قاعده‌ی «قابلیت برنامه‌ریزی‌شده نباید به‌عنوان موجود نمایش داده شود»
 * با یک هشدار قابل مشاهده در فرم اعمال می‌شود (نه فقط در متن سند).
 */
class ProductResource extends Resource
{
    use PublishesContent;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'نمونه‌کار';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'محصول';

    protected static ?string $pluralModelLabel = 'محصولات';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('محصول')
                ->columnSpanFull()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('اطلاعات پایه')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('نام')
                                ->required()
                                ->maxLength(180),
                            Ui::slugField('name'),
                            Forms\Components\TextInput::make('tagline')
                                ->label('شعار')
                                ->maxLength(220)
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('summary')
                                ->label('خلاصه')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Tabs\Tab::make('مسئله و راه‌حل')
                        ->schema([
                            // ── هشدار قابلیت برنامه‌ریزی‌شده ──
                            // اصل IA و قاعده‌ی برند: قابلیتی که هنوز عرضه
                            // نشده نباید طوری توصیف شود که گویی در دسترس
                            // است. این هشدار همان‌جایی است که مدیر متن را
                            // می‌نویسد، پس همان‌جا یادآوری می‌شود.
                            Forms\Components\Placeholder::make('pre_release_warning')
                                ->hiddenLabel()
                                ->visible(fn (Forms\Get $get): bool => in_array($get('status'), [ProductStatus::Idea->value, ProductStatus::Development->value], true))
                                ->content('وضعیت این محصول «ایده» یا «در حال توسعه» است. در متن صریح بنویسید که این قابلیت هنوز در دسترس نیست — قابلیت برنامه‌ریزی‌شده نباید به‌عنوان موجود معرفی شود.')
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('problem')
                                ->label('مسئله')
                                ->rows(4)
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('solution')
                                ->label('راه‌حل')
                                ->rows(4)
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('current_state')
                                ->label('وضعیت فعلی')
                                ->helperText('الان چه چیزی واقعاً کار می‌کند و چه چیزی نه.')
                                ->rows(4)
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('description')
                                ->label('توضیحات کامل')
                                ->rows(5)
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
                            Forms\Components\Select::make('projects')
                                ->label('پروژه‌ها')
                                ->relationship('projects', 'title')
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
                            Forms\Components\TextInput::make('website_url')
                                ->label('وب‌سایت')
                                ->url()
                                ->maxLength(2048),
                            Forms\Components\TextInput::make('repository_url')
                                ->label('نشانی مخزن')
                                ->url()
                                ->maxLength(2048),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('انتشار')
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('وضعیت محصول')
                                ->helperText('چرخه‌ی عرضه‌ی محصول. این «وضعیت انتشار» نیست.')
                                ->options(ProductStatus::options())
                                ->required()
                                ->native(false),
                            Forms\Components\DateTimePicker::make('published_at')
                                ->label('زمان انتشار')
                                ->helperText('تا وقتی خالی باشد، محصول در سایت عمومی دیده نمی‌شود. تاریخ آینده یعنی زمان‌بندی.')
                                ->seconds(false),
                            Forms\Components\DatePicker::make('started_at')
                                ->label('تاریخ شروع'),
                            Forms\Components\DatePicker::make('launched_at')
                                ->label('تاریخ عرضه'),
                            Forms\Components\Toggle::make('is_featured')
                                ->label('ویژه (نمایش در صفحه اصلی)'),
                            Forms\Components\TextInput::make('sort_order')
                                ->label('ترتیب نمایش')
                                ->numeric()
                                ->default(0),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('رسانه')
                        ->schema([
                            Forms\Components\Select::make('media')
                                ->label('رسانه‌های پیوست')
                                ->relationship('media', 'original_name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->columnSpanFull(),
                        ]),
                ]),
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
                    ->limit(50),
                Ui::statusColumn('status', 'وضعیت', 'product'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('ویژه')
                    ->boolean(),
                Ui::dateColumn('published_at', 'انتشار'),
                Ui::dateColumn('launched_at', 'عرضه'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(ProductStatus::options()),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
