<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Support\Ui;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

/**
 * کاربران پنل.
 *
 * ── هشدار حیاتی درباره‌ی دسترسی ──
 * تنها ایمیل‌هایی که در `ADMIN_EMAIL` (env) فهرست شده‌اند می‌توانند
 * وارد پنل شوند (`User::canAccessPanel()`). ساختن کاربری که ایمیلش
 * در آن فهرست نیست، حساب بی‌فایده می‌سازد — کاربر هرگز نمی‌تواند
 * وارد شود. فرم این را صریح به مدیر می‌گوید.
 *
 * ── چرا رمز فقط در ایجاد ──
 * رمز در فرم ویرایش نمایش داده نمی‌شود؛ تغییر رمز یک اکشن جداگانه
 * است تا یک ذخیره‌ی اتفاقی، رمز را بازنویسی نکند.
 *
 * ── چرا حذف محدود است ──
 * `UserPolicy` دو محافظ دارد: مدیر نمی‌تواند خودش را حذف کند و
 * نمی‌تواند آخرین مدیر فعال را حذف کند (وگرنه پنل برای همیشه قفل
 * می‌شود). این محافظ‌ها سمت سرور اعمال می‌شوند؛ متن راهنما فقط
 * توضیح می‌دهد.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'تنظیمات';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'کاربر';

    protected static ?string $pluralModelLabel = 'کاربران';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('کاربر')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('نام')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\TextInput::make('email')
                        ->label('ایمیل')
                        ->email()
                        ->required()
                        ->maxLength(190)
                        ->unique(ignoreRecord: true)
                        ->helperText('فقط ایمیل‌هایی که در متغیر محیطی ADMIN_EMAIL فهرست شده‌اند می‌توانند وارد پنل شوند. اگر این ایمیل در آن فهرست نباشد، کاربر ساخته می‌شود ولی هرگز نمی‌تواند وارد شود.'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('فعال')
                        ->default(true)
                        ->helperText('کاربر غیرفعال حتی با ایمیل مجاز هم نمی‌تواند وارد پنل شود.'),
                    Forms\Components\TextInput::make('password')
                        ->label('رمز عبور')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                        ->minLength(12)
                        ->maxLength(255)
                        // رمز در ویرایش نشان داده نمی‌شود؛ برای تغییر
                        // رمز از اکشن «تغییر رمز» استفاده کنید.
                        ->visibleOn('create')
                        ->helperText('حداقل ۱۲ نویسه. رمز قوی انتخاب کنید؛ این حساب به کل محتوای سایت دسترسی دارد.'),
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
                Tables\Columns\TextColumn::make('email')
                    ->label('ایمیل')
                    ->searchable(),
                Tables\Columns\TextColumn::make('panel_access')
                    ->label('دسترسی پنل')
                    ->state(fn (User $record): string => $record->canAccessPanel() ? 'مجاز' : 'غیرمجاز')
                    ->badge()
                    ->color(fn (User $record): string => $record->canAccessPanel() ? 'success' : 'danger')
                    ->tooltip('دسترسی تنها برای ایمیل‌های موجود در فهرست ADMIN_EMAIL و حساب‌های فعال برقرار است.'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
                Ui::dateColumn('created_at', 'ایجاد'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('فعال'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\Action::make('changePassword')
                    ->label('تغییر رمز')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('password')
                            ->label('رمز جدید')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(12)
                            ->maxLength(255)
                            ->helperText('حداقل ۱۲ نویسه.'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->forceFill(['password' => Hash::make($data['password'])])->save();

                        Notification::make()
                            ->title('رمز تغییر کرد')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->modalDescription('حذف کاربر مجاز نیست اگر خودتان باشید یا آخرین مدیر فعال باشد. متن محتوای تألیف‌شده حفظ می‌شود (نویسنده خالی می‌ماند).'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
