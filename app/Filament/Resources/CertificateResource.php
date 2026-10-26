<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PublicationStatus;
use App\Filament\Resources\CertificateResource\Pages;
use App\Filament\Support\PublishesContent;
use App\Filament\Support\Ui;
use App\Models\Certificate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * گواهی‌نامه.
 *
 * ── چرا نشان «منقضی» لازم است ──
 * نمایش گواهی منقضی‌شده به‌عنوان «معتبر» یک ادعای نادرست است. مدل
 * متد `isExpired()` دارد؛ اینجا همان در جدول به‌صورت نشان دیده می‌شود
 * تا مدیر بداند کدام گواهی دیگر معتبر نیست.
 */
class CertificateResource extends Resource
{
    use PublishesContent;

    protected static ?string $model = Certificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'هویت';

    protected static ?int $navigationSort = 8;

    protected static ?string $modelLabel = 'گواهی‌نامه';

    protected static ?string $pluralModelLabel = 'گواهی‌نامه‌ها';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('گواهی‌نامه')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('عنوان')
                        ->required()
                        ->maxLength(180),
                    Forms\Components\TextInput::make('issuer')
                        ->label('صادرکننده')
                        ->required()
                        ->maxLength(180),
                    Forms\Components\DatePicker::make('issued_at')
                        ->label('تاریخ صدور'),
                    Forms\Components\DatePicker::make('expires_at')
                        ->label('تاریخ انقضا')
                        ->helperText('اگر پر شده باشد و گذشته باشد، در فهرست با نشان «منقضی» نمایش داده می‌شود.'),
                    Forms\Components\TextInput::make('credential_url')
                        ->label('نشانی اعتبارسنجی')
                        ->url()
                        ->maxLength(2048),
                    Forms\Components\Select::make('status')
                        ->label('وضعیت انتشار')
                        ->options(PublicationStatus::options())
                        ->required()
                        ->native(false),
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
                Tables\Columns\TextColumn::make('issuer')
                    ->label('صادرکننده')
                    ->searchable()
                    ->limit(30),
                Ui::dateColumn('issued_at', 'صدور'),
                Ui::dateColumn('expires_at', 'انقضا'),
                Tables\Columns\TextColumn::make('expired')
                    ->label('اعتبار')
                    ->state(fn (Certificate $record): string => $record->isExpired() ? 'منقضی' : 'معتبر')
                    ->badge()
                    ->color(fn (Certificate $record): string => $record->isExpired() ? 'danger' : 'success'),
                Ui::statusColumn('status', 'وضعیت', 'publication'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(PublicationStatus::options()),
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
            'index' => Pages\ListCertificates::route('/'),
            'create' => Pages\CreateCertificate::route('/create'),
            'edit' => Pages\EditCertificate::route('/{record}/edit'),
        ];
    }
}
