<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PostStatus;
use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Services\ImageOptimizer;
use App\Services\ReadingTime;
use App\Support\Slugger;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * یادداشت‌های فنی (M6). زیرساخت در لانچ کامل است؛ محتوا در فاز ۸.۱ می‌آید.
 *
 * انتشار اولین پست، لینک وبلاگ را در ناوبری عمومی ظاهر می‌کند (D08) —
 * بدون نیاز به هیچ تغییری در کد.
 */
class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'وبلاگ';

    protected static ?string $modelLabel = 'یادداشت';

    protected static ?string $pluralModelLabel = 'یادداشت‌ها';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('نوشته')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('title')
                            ->label('عنوان')
                            ->required()
                            ->maxLength(150)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Slugger::make($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('اسلاگ')
                            ->required()
                            ->maxLength(80)
                            ->unique(ignoreRecord: true)
                            ->prefix('/blog/')
                            ->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                    ]),

                    Textarea::make('excerpt')
                        ->label('چکیده')
                        ->rows(2)
                        ->maxLength(300)
                        ->helperText('در لیست وبلاگ و به‌عنوان توضیح متای پیش‌فرض استفاده می‌شود.'),

                    MarkdownEditor::make('content')
                        ->label('محتوا')
                        ->required()
                        ->columnSpanFull()
                        ->live(onBlur: true)
                        ->helperText(fn (?string $state) => 'زمان مطالعه‌ی تخمینی: '
                            .fa_digits(ReadingTime::forMarkdown($state)).' دقیقه')
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('posts/attachments'),
                ]),

            Section::make('انتشار')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('status')
                            ->label('وضعیت')
                            ->options(PostStatus::options())
                            ->default(PostStatus::Draft->value)
                            ->required()
                            ->live()
                            ->native(false),

                        DateTimePicker::make('published_at')
                            ->label('زمان انتشار')
                            ->seconds(false)
                            ->default(now())
                            ->helperText('تاریخِ آینده = پست تا آن لحظه عمومی نمی‌شود.')
                            ->required(fn (Get $get) => in_array($get('status'), [
                                PostStatus::Published->value,
                                PostStatus::Scheduled->value,
                            ], true)),
                    ]),

                    FileUpload::make('cover')
                        ->label('تصویر جلد')
                        ->image()
                        ->disk('public')
                        ->directory('posts/covers')
                        ->maxSize(8192)
                        ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => app(ImageOptimizer::class)
                            ->store($file, 'posts/covers')),

                    Select::make('tags')
                        ->label('برچسب‌ها')
                        ->relationship('tags', 'name')
                        ->multiple()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('نام')
                                ->required()
                                ->maxLength(40)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Slugger::make((string) $state))),
                            TextInput::make('slug')->label('اسلاگ')->required()->maxLength(40)->unique('tags', 'slug'),
                        ]),
                ])
                ->collapsible(),

            Section::make('سئو')
                ->schema([
                    TextInput::make('meta_title')
                        ->label('عنوان متا')
                        ->maxLength(160)
                        ->placeholder('خالی = از عنوان یادداشت استفاده می‌شود'),

                    Textarea::make('meta_description')
                        ->label('توضیح متا')
                        ->rows(2)
                        ->maxLength(255)
                        ->placeholder('خالی = از چکیده استفاده می‌شود'),
                ])
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')->label('عنوان')->searchable()->weight('bold')->limit(60),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (PostStatus $state) => $state->label())
                    ->color(fn (PostStatus $state) => match ($state) {
                        PostStatus::Published => 'success',
                        PostStatus::Scheduled => 'warning',
                        PostStatus::Draft => 'gray',
                    }),

                TextColumn::make('published_at')
                    ->label('انتشار')
                    ->formatStateUsing(fn ($state) => $state ? fa_date($state).' · '.$state->format('H:i') : '—')
                    ->sortable()
                    ->description(fn (Post $record) => $record->published_at?->isFuture() ? 'هنوز عمومی نشده' : null),

                TextColumn::make('reading_time')
                    ->label('مطالعه')
                    ->formatStateUsing(fn ($state) => fa_digits($state).' دقیقه'),

                TextColumn::make('tags.name')->label('برچسب‌ها')->badge()->separator(','),
            ])
            ->filters([
                SelectFilter::make('status')->label('وضعیت')->options(PostStatus::options()),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->emptyStateHeading('هنوز یادداشتی نیست')
            ->emptyStateDescription('با انتشار اولین یادداشت، لینک وبلاگ به‌صورت خودکار در ناوبری سایت ظاهر می‌شود.');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            \Illuminate\Database\Eloquent\SoftDeletingScope::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
