<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AiProviderResource\Pages;
use App\Models\AiProvider;
use App\Services\Ai\AiService;
use App\Services\Ai\SsrfGuard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

/**
 * فراهم‌کننده‌ی هوش مصنوعی.
 *
 * ── وضعیت MVP ──
 * `config('services.ai.enabled')` در MVP `false` است. جدول وجود دارد
 * ولی هیچ درخواست خارجی زده نمی‌شود. یک هشدار برجسته در فرم این را
 * به مدیر یادآوری می‌کند تا انتظار نادرست نسازد.
 *
 * ── امنیت کلید (الزام سخت) ──
 * `api_key` با cast `encrypted` ذخیره می‌شود و در `$hidden` است.
 * فیلد فرم از نوع password است و هرگز مقدار ذخیره‌شده را نشان نمی‌دهد؛
 * فقط وضعیت «کلید تنظیم شده / تنظیم نشده» از `hasKey()` نمایش داده
 * می‌شود. `dehydrated(false)` وقتی فیلد خالی بماند تضمین می‌کند که
 * ذخیره‌ی خالی، کلید موجود را پاک نکند.
 *
 * ── محافظت SSRF (الزام سخت) ──
 * `base_url` از پنل قابل تنظیم است و سرور به آن درخواست می‌زند. اگر
 * بدون اعتبارسنجی بماند، مهاجم می‌تواند آن را روی سرویس فراداده‌ی
 * ابری یا پنل cPanel داخلی تنظیم کند. پس با `SsrfGuard::isSafeUrl()`
 * اعتبارسنجی می‌شود: فقط https، بدون آدرس خصوصی/داخلی، بدون پورت
 * غیراستاندارد.
 */
class AiProviderResource extends Resource
{
    protected static ?string $model = AiProvider::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'تنظیمات';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'فراهم‌کننده‌ی AI';

    protected static ?string $pluralModelLabel = 'فراهم‌کننده‌های AI';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // ── هشدار برجسته‌ی MVP ──
            Forms\Components\Placeholder::make('mvp_notice')
                ->hiddenLabel()
                ->content(new HtmlString(
                    '<div style="padding:0.75rem 1rem;border-radius:0.5rem;background:#FEF3C7;color:#92400E;font-weight:600;">'
                    .'قابلیت هوش مصنوعی در MVP غیرفعال است. فعال‌سازی آن نیازمند یک کاربرد واقعی و تأییدشده است و نباید به‌صورت پیش‌فرض روشن شود.'
                    .'</div>'
                ))
                ->columnSpanFull(),

            Forms\Components\Section::make('فراهم‌کننده')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('نام')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\Select::make('provider_key')
                        ->label('نوع فراهم‌کننده')
                        ->options(self::providerOptions())
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                            // پرکردن پیش‌فرض base_url و model از پیکربندی،
                            // برای راحتی مدیر. مقادیر قابل ویرایش می‌مانند.
                            $provider = config("services.ai.providers.{$state}");

                            if (is_array($provider)) {
                                if (filled($provider['base_url'] ?? null)) {
                                    $set('base_url', $provider['base_url']);
                                }
                                if (filled($provider['model'] ?? null)) {
                                    $set('model_name', $provider['model']);
                                }
                            }
                        }),
                    Forms\Components\TextInput::make('base_url')
                        ->label('نشانی پایه (Base URL)')
                        ->maxLength(2048)
                        ->helperText('باید https باشد. آدرس‌های داخلی، خصوصی و پورت‌های غیراستاندارد رد می‌شوند (محافظت SSRF).')
                        // ── محافظت SSRF در لایه‌ی اعتبارسنجی ──
                        ->rules([
                            fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                if (blank($value)) {
                                    return;
                                }

                                if (! SsrfGuard::isSafeUrl((string) $value)) {
                                    $fail('این نشانی برای درخواست خروجی امن نیست. باید https باشد و به یک آدرس عمومی اشاره کند (نه localhost، نه شبکه‌ی خصوصی، نه پورت غیراستاندارد).');
                                }
                            },
                        ]),
                    Forms\Components\TextInput::make('model_name')
                        ->label('نام مدل')
                        ->maxLength(160),
                ])->columns(2),

            Forms\Components\Section::make('کلید API')
                ->schema([
                    Forms\Components\Placeholder::make('key_status')
                        ->label('وضعیت کلید')
                        ->content(fn (?AiProvider $record): string => $record === null
                            ? 'پس از ذخیره مشخص می‌شود.'
                            : ($record->hasKey() ? 'کلید تنظیم شده است' : 'کلید تنظیم نشده است'))
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('api_key')
                        ->label('کلید جدید')
                        ->password()
                        ->revealable()
                        // هرگز مقدار ذخیره‌شده را نشان نده.
                        ->default(null)
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->maxLength(2000)
                        ->helperText('برای تغییر کلید، مقدار جدید را وارد کنید. خالی گذاشتن این فیلد، کلید موجود را دست‌نخورده نگه می‌دارد. مقدار ذخیره‌شده هرگز دوباره نمایش داده نمی‌شود.')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('وضعیت')
                ->schema([
                    Forms\Components\Toggle::make('is_enabled')
                        ->label('فعال'),
                    Forms\Components\Toggle::make('is_default')
                        ->label('پیش‌فرض')
                        ->helperText('فقط یک فراهم‌کننده می‌تواند پیش‌فرض باشد.'),
                    Forms\Components\KeyValue::make('options')
                        ->label('تنظیمات غیرحساس')
                        ->keyLabel('کلید')
                        ->valueLabel('مقدار')
                        ->helperText('مثلاً temperature یا max_tokens. هرگز کلید یا توکن اینجا نگذارید.')
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('provider_key')
                    ->label('نوع')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('base_url')
                    ->label('نشانی پایه')
                    ->limit(40)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('model_name')
                    ->label('مدل')
                    ->placeholder('—'),
                // ── وضعیت کلید — بدون افشای مقدار ──
                Tables\Columns\TextColumn::make('key_status')
                    ->label('کلید')
                    ->state(fn (AiProvider $record): string => $record->hasKey() ? 'تنظیم شده' : 'تنظیم نشده')
                    ->badge()
                    ->color(fn (AiProvider $record): string => $record->hasKey() ? 'success' : 'gray'),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('فعال')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('پیش‌فرض')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')->label('فعال'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\Action::make('testConnection')
                    ->label('تست اتصال')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('تست اتصال')
                    ->modalDescription('یک درخواست کوتاه از سمت سرور به فراهم‌کننده زده می‌شود. خطاها کلید را افشا نمی‌کنند.')
                    ->action(function (AiProvider $record): void {
                        // تست از سمت سرور انجام می‌شود و فقط پیام عمومی
                        // برمی‌گرداند — هیچ‌گاه کلید یا جزئیات درخواست.
                        $result = app(AiService::class)->testConnection($record);

                        Notification::make()
                            ->title($result['ok'] ? 'اتصال برقرار شد' : 'اتصال برقرار نشد')
                            ->body($result['message'])
                            ->{$result['ok'] ? 'success' : 'danger'}()
                            ->send();
                    }),
                Tables\Actions\Action::make('setDefault')
                    ->label('تعیین پیش‌فرض')
                    ->icon('heroicon-o-star')
                    ->color('success')
                    ->visible(fn (AiProvider $record): bool => ! $record->is_default)
                    ->action(function (AiProvider $record): void {
                        // تضمین «فقط یکی» در تراکنش داخل سرویس انجام می‌شود.
                        app(AiService::class)->setDefault($record);

                        Notification::make()->title('پیش‌فرض تعیین شد')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAiProviders::route('/'),
            'create' => Pages\CreateAiProvider::route('/create'),
            'edit' => Pages\EditAiProvider::route('/{record}/edit'),
        ];
    }

    /**
     * گزینه‌های فراهم‌کننده از پیکربندی — برچسب فارسی هر کدام.
     *
     * @return array<string, string>
     */
    private static function providerOptions(): array
    {
        $providers = config('services.ai.providers', []);

        return collect($providers)
            ->mapWithKeys(fn (array $provider, string $key): array => [
                $key => (string) ($provider['label'] ?? $key),
            ])
            ->all();
    }
}
