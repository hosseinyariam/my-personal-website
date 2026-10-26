<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NoteType;
use App\Enums\PublicationStatus;
use App\Enums\Visibility;
use App\Filament\Resources\NoteResource\Pages;
use App\Filament\Support\PublishesContent;
use App\Filament\Support\Ui;
use App\Models\Note;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * یادداشت / مقاله.
 *
 * ── زمان مطالعه ──
 * `reading_time_minutes` روی مدل **ذخیره نمی‌شود** و از متن محاسبه
 * می‌گردد (کامنت مدل و CMS:150-160). پس فیلدی برای آن در فرم نیست؛
 * فقط در جدول به‌صورت محاسبه‌شده نمایش داده می‌شود.
 *
 * ── پاک‌سازی HTML ──
 * ویرایشگر متن از `Ui::richEditor()` می‌آید که مقدار را در زمان ذخیره
 * پاک‌سازی می‌کند. دلیل تفصیلی در همان متد آمده. یادداشت فرم صریحاً
 * به مدیر می‌گوید که HTML ورودی پاک‌سازی می‌شود.
 */
class NoteResource extends Resource
{
    use PublishesContent;

    protected static ?string $model = Note::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'محتوا';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'یادداشت';

    protected static ?string $pluralModelLabel = 'یادداشت‌ها';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('یادداشت')
                ->columnSpanFull()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('محتوا')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('عنوان')
                                ->required()
                                ->maxLength(220),
                            Ui::slugField(),
                            Forms\Components\Textarea::make('excerpt')
                                ->label('خلاصه')
                                ->helperText('اختیاری. اگر خالی بماند، هنگام ذخیره از متن ساخته می‌شود.')
                                ->rows(2)
                                ->columnSpanFull(),
                            Ui::richEditor('body', 'متن')
                                ->required(),
                            Forms\Components\Placeholder::make('sanitize_note')
                                ->hiddenLabel()
                                ->content('توجه: HTML ورودی هنگام ذخیره پاک‌سازی می‌شود تا محتوای ناایمن حذف گردد. اگر بخشی از قالب‌بندی پس از ذخیره ناپدید شد، علتش همین پاک‌سازی است.')
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Tabs\Tab::make('دسته‌بندی')
                        ->schema([
                            Forms\Components\Select::make('type')
                                ->label('نوع')
                                ->options(NoteType::options())
                                ->required()
                                ->native(false),
                            Forms\Components\Select::make('categories')
                                ->label('دسته‌ها')
                                ->relationship('categories', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false),
                            Forms\Components\Select::make('tags')
                                ->label('برچسب‌ها')
                                ->relationship('tags', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('روابط')
                        ->schema([
                            Forms\Components\Select::make('projects')
                                ->label('پروژه‌ها')
                                ->relationship('projects', 'title')
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
                            Forms\Components\Select::make('companies')
                                ->label('شرکت‌ها')
                                ->relationship('companies', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false),
                            Forms\Components\Select::make('author_id')
                                ->label('نویسنده')
                                ->relationship('author', 'name')
                                ->searchable()
                                ->preload()
                                ->native(false),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('انتشار')
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
                                ->helperText('تا وقتی خالی باشد، یادداشت در سایت عمومی دیده نمی‌شود.')
                                ->seconds(false),
                            Forms\Components\Toggle::make('is_featured')
                                ->label('ویژه'),
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
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => Ui::enumLabel($state))
                    ->color('info'),
                Ui::statusColumn('status', 'وضعیت', 'publication'),
                Ui::statusColumn('visibility', 'دسترسی', 'visibility'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('ویژه')
                    ->boolean(),
                Ui::dateColumn('published_at', 'انتشار'),
                Tables\Columns\TextColumn::make('reading_time')
                    ->label('زمان مطالعه')
                    ->state(fn (Note $record): string => $record->readingTimeLabel()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع')
                    ->options(NoteType::options()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(PublicationStatus::options()),
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
            'index' => Pages\ListNotes::route('/'),
            'create' => Pages\CreateNote::route('/create'),
            'edit' => Pages\EditNote::route('/{record}/edit'),
        ];
    }
}
