<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PlatformStatus;
use App\Filament\Resources\PlatformResource\Pages;
use App\Filament\Resources\PlatformResource\RelationManagers\ScreenshotsRelationManager;
use App\Models\Platform;
use App\Support\Slugger;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * منبع پلتفرم — تحقق عملی G4.
 *
 * افزودن پلتفرم بعدی (مثلاً تسکر در فاز ۸.۲) از همین فرم انجام می‌شود:
 * صفر خط کد، صفر دیپلوی.
 */
class PlatformResource extends Resource
{
    protected static ?string $model = Platform::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationGroup = 'ویترین';

    protected static ?string $modelLabel = 'پلتفرم';

    protected static ?string $pluralModelLabel = 'پلتفرم‌ها';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('هویت')
                ->description('عنوان و تگ‌لاین همان چیزی است که روی کارت ویترین دیده می‌شود.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('title')
                            ->label('عنوان')
                            ->required()
                            ->maxLength(80)
                            ->live(onBlur: true)
                            // اسلاگ فقط برای رکورد جدید خودکار ساخته می‌شود؛
                            // تغییر اسلاگِ منتشرشده لینک‌های موجود را می‌شکند.
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Slugger::make($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('اسلاگ (نشانی صفحه)')
                            ->required()
                            ->maxLength(60)
                            ->unique(ignoreRecord: true)
                            ->helperText('لاتین و با خط تیره. پس از انتشار تغییرش ندهید — لینک‌های موجود می‌شکنند.')
                            ->prefix('/platforms/')
                            ->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                    ]),

                    TextInput::make('tagline')
                        ->label('تگ‌لاین')
                        ->maxLength(160)
                        ->helperText('یک جمله‌ی کوتاه: این پلتفرم چیست؟'),

                    Grid::make(3)->schema([
                        Select::make('status')
                            ->label('وضعیت')
                            ->options(PlatformStatus::options())
                            ->default(PlatformStatus::Development->value)
                            ->required()
                            ->native(false),

                        TextInput::make('icon')
                            ->label('آیکون کاشی')
                            ->maxLength(40)
                            ->placeholder('package')
                            ->helperText('نام آیکون Tabler — فهرست در /dev/kit'),

                        TextInput::make('sort_order')
                            ->label('ترتیب نمایش')
                            ->numeric()
                            ->default(0)
                            ->helperText('کوچک‌تر = بالاتر'),
                    ]),
                ]),

            Section::make('مسئله و راه‌حل')
                ->description('هر فیلدی که خالی بماند، سکشن مربوطه در صفحه‌ی عمومی مخفی می‌شود.')
                ->schema([
                    Textarea::make('problem')
                        ->label('مسئله')
                        ->rows(4)
                        ->helperText('چه دردی را حل می‌کند؟ با زبان مخاطب بنویسید، نه با زبان فنی.'),

                    Textarea::make('solution')
                        ->label('راه‌حل')
                        ->rows(4),
                ])
                ->collapsible(),

            Section::make('امکانات و استک فنی')
                ->schema([
                    Repeater::make('features')
                        ->label('امکانات')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('title')->label('عنوان')->required()->maxLength(80),
                                TextInput::make('icon')->label('آیکون')->maxLength(40)->placeholder('circle-check'),
                            ]),
                            Textarea::make('description')->label('توضیح')->rows(2),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->collapsed()
                        ->reorderable()
                        ->addActionLabel('افزودن امکان')
                        ->default([]),

                    TagsInput::make('tech_stack')
                        ->label('استک فنی')
                        ->placeholder('Laravel، سپس Enter')
                        ->helperText('هر فناوری را جدا وارد کنید؛ به‌صورت چیپ نمایش داده می‌شود.'),
                ])
                ->collapsible(),

            Section::make('لینک‌ها و مشخصات')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('external_url')
                            ->label('لینک زنده / دمو')
                            ->url()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-m-arrow-top-right-on-square'),

                        TextInput::make('repo_url')
                            ->label('کد منبع')
                            ->url()
                            ->maxLength(255)
                            ->helperText('اختیاری — خالی بماند، دکمه‌اش رندر نمی‌شود.'),

                        TextInput::make('version')->label('نسخه')->maxLength(20)->placeholder('1.0.0'),
                        TextInput::make('license')->label('لایسنس')->maxLength(40)->placeholder('MIT'),
                    ]),
                ])
                ->collapsible(),

            Section::make('نمایش')
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('is_featured')
                            ->label('پلتفرم شاخص')
                            ->helperText('روی صفحه‌ی اصلی به‌عنوان تیزر و در ویترین با کارت عریض دیده می‌شود.'),

                        Toggle::make('is_active')
                            ->label('فعال')
                            ->default(true)
                            ->helperText('غیرفعال = از ویترین و نقشه‌ی سایت حذف می‌شود.'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Platform $record) => $record->tagline),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (PlatformStatus $state) => $state->label())
                    ->color(fn (PlatformStatus $state) => match ($state) {
                        PlatformStatus::Live => 'success',
                        PlatformStatus::Beta => 'info',
                        PlatformStatus::Development => 'gray',
                        PlatformStatus::Archived => 'gray',
                    }),

                IconColumn::make('is_featured')->label('شاخص')->boolean(),

                ToggleColumn::make('is_active')->label('فعال'),

                TextColumn::make('screenshots_count')
                    ->label('اسکرین‌شات')
                    ->counts('screenshots')
                    ->formatStateUsing(fn ($state) => fa_digits($state))
                    ->tooltip('بدون اسکرین‌شات، ماک مرورگر نمایش داده می‌شود.'),

                TextColumn::make('updated_at')
                    ->label('به‌روزرسانی')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->label('وضعیت')->options(PlatformStatus::options()),
                TernaryFilter::make('is_active')->label('فعال'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('هنوز پلتفرمی ثبت نشده')
            ->emptyStateDescription('اولین رکورد را بسازید — صفحه‌ی عمومی‌اش بدون نیاز به کدنویسی ساخته می‌شود.');
    }

    public static function getRelations(): array
    {
        return [ScreenshotsRelationManager::class];
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
            'index' => Pages\ListPlatforms::route('/'),
            'create' => Pages\CreatePlatform::route('/create'),
            'edit' => Pages\EditPlatform::route('/{record}/edit'),
        ];
    }
}
