<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ResumeExperienceResource\Pages;
use App\Models\ResumeExperience;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ResumeExperienceResource extends Resource
{
    protected static ?string $model = ResumeExperience::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'رزومه';

    protected static ?string $modelLabel = 'سابقه‌ی کاری';

    protected static ?string $pluralModelLabel = 'سوابق کاری';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                TextInput::make('role')->label('نقش / عنوان شغلی')->required()->maxLength(100),
                TextInput::make('company')->label('سازمان')->required()->maxLength(100),

                DatePicker::make('started_at')->label('تاریخ شروع')->required()->native(false),
                DatePicker::make('ended_at')
                    ->label('تاریخ پایان')
                    ->native(false)
                    ->helperText('خالی بگذارید یعنی «تا اکنون».')
                    ->afterOrEqual('started_at'),
            ]),

            Textarea::make('achievements')
                ->label('دستاوردها')
                ->rows(5)
                ->helperText('هر خط یک دستاورد — در صفحه‌ی رزومه به فهرست نقطه‌دار تبدیل می‌شود.')
                ->columnSpanFull(),

            Grid::make(2)->schema([
                TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
                Toggle::make('is_active')->label('فعال')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('role')->label('نقش')->searchable()->weight('bold')
                    ->description(fn (ResumeExperience $record) => $record->company),
                TextColumn::make('period')->label('بازه')->state(fn (ResumeExperience $r) => $r->period()),
                ToggleColumn::make('is_active')->label('فعال'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->emptyStateHeading('هنوز سابقه‌ای ثبت نشده')
            ->emptyStateDescription('تا ورود اولین ردیف، بخش تجربه در صفحه‌ی رزومه مخفی می‌ماند.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResumeExperiences::route('/'),
            'create' => Pages\CreateResumeExperience::route('/create'),
            'edit' => Pages\EditResumeExperience::route('/{record}/edit'),
        ];
    }
}
