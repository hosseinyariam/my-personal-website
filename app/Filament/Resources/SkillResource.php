<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SkillResource\Pages;
use App\Filament\Support\Ui;
use App\Models\Skill;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * مهارت.
 *
 * ── قاعده‌ی «شاهد بر ادعا» ──
 * scope `evidenced` روی مدل فقط مهارت‌هایی را برای نمایش عمومی
 * می‌پذیرد که به پروژه‌ی منتشرشده و عمومی وصل باشند. این قاعده در
 * فرم به‌صورت راهنما و در جدول به‌صورت شمارش شواهد نشان داده می‌شود
 * تا مدیر بفهمد چرا یک مهارت با اینکه `is_active` است دیده نمی‌شود.
 *
 * ── چرا «سطح» عدد نیست ──
 * `proficiency_label` متن آزاد است، نه درصد — چون امتیاز خودارزیابی
 * شاهد نیست (UI Kit:222-227). فرم هم عدد نمی‌پذیرد.
 */
class SkillResource extends Resource
{
    protected static ?string $model = Skill::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'هویت';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'مهارت';

    protected static ?string $pluralModelLabel = 'مهارت‌ها';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('مهارت')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('نام')
                        ->required()
                        ->maxLength(120),
                    Ui::slugField('name'),
                    Forms\Components\TextInput::make('category')
                        ->label('دسته')
                        ->maxLength(80),
                    Forms\Components\TextInput::make('proficiency_label')
                        ->label('برچسب سطح')
                        ->helperText('متن توصیفی مثل «آشنا» یا «حرفه‌ای» — نه عدد یا درصد. سطح عددی، شاهد نیست.')
                        ->maxLength(40),
                    Forms\Components\Textarea::make('description')
                        ->label('توضیحات')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('ترتیب نمایش')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_active')
                        ->label('فعال')
                        ->default(true),
                    Forms\Components\Placeholder::make('evidence_hint')
                        ->hiddenLabel()
                        ->content('مهارتی که به هیچ پروژه‌ای وصل نیست، در سایت عمومی نمایش داده نمی‌شود — حتی اگر فعال باشد. برای نمایش، آن را در فرم پروژه در تب «روابط» انتخاب کنید.')
                        ->columnSpanFull(),
                ])->columns(2),
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
                    ->limit(40),
                Tables\Columns\TextColumn::make('category')
                    ->label('دسته')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('proficiency_label')
                    ->label('سطح')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('projects_count')
                    ->label('شواهد (پروژه)')
                    ->counts('projects')
                    ->badge()
                    ->color(fn (Skill $record): string => $record->projects_count > 0 ? 'success' : 'warning')
                    ->tooltip('تعداد پروژه‌های متصل. بدون پروژه، مهارت در سایت عمومی پنهان است.'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('فعال'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
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
            'index' => Pages\ListSkills::route('/'),
            'create' => Pages\CreateSkill::route('/create'),
            'edit' => Pages\EditSkill::route('/{record}/edit'),
        ];
    }
}
