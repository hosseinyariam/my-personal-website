<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\CompanyRelation;
use App\Enums\PublicationStatus;
use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Support\PublishesContent;
use App\Filament\Support\Ui;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * شرکت — موجودیت سازمانی مستقل.
 *
 * شرکت ستون `visibility` ندارد؛ انتشار با `status` (draft/published/
 * archived) و `published_at` کنترل می‌شود. نکته‌ی امنیتی CMS:146-147:
 * اطلاعات محرمانه‌ی شرکتی نباید در بخش عمومی وارد شود — مدیر باید
 * خودش مراقب متن باشد، چون سیستم نمی‌تواند «محرمانه» را تشخیص دهد.
 */
class CompanyResource extends Resource
{
    use PublishesContent;

    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'نمونه‌کار';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'شرکت';

    protected static ?string $pluralModelLabel = 'شرکت‌ها';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('شرکت')
                ->columnSpanFull()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('اطلاعات پایه')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('نام')
                                ->required()
                                ->maxLength(180),
                            Ui::slugField('name'),
                            Forms\Components\Textarea::make('summary')
                                ->label('خلاصه')
                                ->rows(3)
                                ->columnSpanFull(),
                            Ui::richEditor('description', 'توضیحات'),
                        ]),

                    Forms\Components\Tabs\Tab::make('رابطه و بازه')
                        ->schema([
                            Forms\Components\Select::make('relationship_type')
                                ->label('نوع رابطه')
                                ->options(CompanyRelation::options())
                                ->native(false),
                            Forms\Components\TextInput::make('company_type')
                                ->label('نوع شرکت')
                                ->helperText('مثلاً «استارتاپ»، «شرکت بزرگ»، «خوداشتغالی».')
                                ->maxLength(40),
                            Forms\Components\DatePicker::make('started_at')
                                ->label('تاریخ شروع'),
                            Forms\Components\DatePicker::make('ended_at')
                                ->label('تاریخ پایان')
                                ->helperText('خالی بماند یعنی رابطه هنوز باز است.'),
                            Forms\Components\TextInput::make('website_url')
                                ->label('وب‌سایت')
                                ->url()
                                ->maxLength(2048),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('روابط')
                        ->schema([
                            Forms\Components\Select::make('products')
                                ->label('محصولات')
                                ->relationship('products', 'name')
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

                    Forms\Components\Tabs\Tab::make('انتشار')
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('وضعیت انتشار')
                                ->options(PublicationStatus::options())
                                ->required()
                                ->native(false),
                            Forms\Components\DateTimePicker::make('published_at')
                                ->label('زمان انتشار')
                                ->helperText('تا وقتی خالی باشد، شرکت در سایت عمومی دیده نمی‌شود.')
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
                Tables\Columns\TextColumn::make('name')
                    ->label('نام')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Ui::statusColumn('status', 'وضعیت', 'publication'),
                Tables\Columns\TextColumn::make('relationship_type')
                    ->label('نوع رابطه')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => Ui::enumLabel($state))
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('ویژه')
                    ->boolean(),
                Ui::dateColumn('started_at', 'شروع'),
                Ui::dateColumn('published_at', 'انتشار'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(PublicationStatus::options()),
                Tables\Filters\SelectFilter::make('relationship_type')
                    ->label('نوع رابطه')
                    ->options(CompanyRelation::options()),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
