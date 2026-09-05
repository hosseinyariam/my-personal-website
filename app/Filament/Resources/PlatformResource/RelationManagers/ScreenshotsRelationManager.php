<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlatformResource\RelationManagers;

use App\Services\ImageOptimizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * گالری اسکرین‌شات — بدون رکورد، صفحه‌ی عمومی ماک مرورگر نشان می‌دهد (US-M3-2).
 */
class ScreenshotsRelationManager extends RelationManager
{
    protected static string $relationship = 'screenshots';

    protected static ?string $title = 'اسکرین‌شات‌ها';

    protected static ?string $modelLabel = 'اسکرین‌شات';

    public function form(Form $form): Form
    {
        return $form->schema([
            FileUpload::make('path')
                ->label('تصویر')
                ->image()
                ->disk('public')
                ->directory('platforms/screenshots')
                ->maxSize(8192)
                ->required()
                ->helperText('حداکثر ۸ مگابایت. تصویر هنگام آپلود به WebP با عرض حداکثر ۱۶۰۰ پیکسل بهینه می‌شود.')
                // بهینه‌سازی در لحظه‌ی آپلود — نه در پس‌زمینه (بدون worker روی هاست اشتراکی)
                ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => app(ImageOptimizer::class)
                    ->store($file, 'platforms/screenshots')),

            TextInput::make('caption')
                ->label('توضیح تصویر')
                ->maxLength(160)
                ->helperText('هم زیرنویس نمایش داده می‌شود، هم متن جایگزین برای دسترس‌پذیری.'),

            TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),

            Toggle::make('is_active')->label('فعال')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('path')->label('تصویر')->disk('public')->height(56),
                TextColumn::make('caption')->label('توضیح')->limit(50)->placeholder('—'),
                ToggleColumn::make('is_active')->label('فعال'),
            ])
            ->headerActions([CreateAction::make()->label('افزودن اسکرین‌شات')])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->emptyStateHeading('بدون اسکرین‌شات')
            ->emptyStateDescription('تا افزودن اولین تصویر، صفحه‌ی عمومی «ماک مرورگر» برند-دار نشان می‌دهد — نه فضای خالی.');
    }
}
