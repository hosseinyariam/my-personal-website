<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\ImageOptimizer;
use App\Services\Settings as SettingsService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * تنظیمات سایت — تحقق «محتوا از پنل، نه از کد» (D04).
 *
 * هر مقدار اینجا مستقیماً روی صفحه‌ی عمومی می‌نشیند؛ خالی گذاشتن هر فیلد،
 * بخشِ مربوطه را مخفی می‌کند — نه اینکه جای خالی نشان دهد (پیوست ب).
 */
class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'تنظیمات';

    protected static ?string $navigationLabel = 'تنظیمات سایت';

    protected static ?string $title = 'تنظیمات سایت';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.settings';

    protected static ?string $slug = 'settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $settings = app(SettingsService::class);

        $flat = [];
        foreach (SettingsService::GROUPS as $group) {
            foreach ($settings->group($group) as $key => $value) {
                $flat["{$group}__{$key}"] = $value;
            }
        }

        $this->form->fill($flat);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Tabs::make()->tabs([
                    Tabs\Tab::make('پروفایل')
                        ->icon('heroicon-m-user')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('profile__display_name')
                                    ->label('نام نمایشی')
                                    ->required()
                                    ->maxLength(60)
                                    ->helperText('در هدر، فوتر و عنوان صفحات استفاده می‌شود.'),

                                TextInput::make('profile__headline')
                                    ->label('عنوان حرفه‌ای')
                                    ->maxLength(120)
                                    ->placeholder('مثلاً: توسعه‌دهنده‌ی وب و سازنده‌ی محصول'),

                                TextInput::make('profile__location')
                                    ->label('موقعیت')
                                    ->maxLength(60)
                                    ->placeholder('تاکستان، ایران'),

                                Toggle::make('profile__is_available')
                                    ->label('در دسترس برای پروژه‌ی جدید')
                                    ->helperText('نشان «زنده» را در پنل وضعیت صفحه‌ی اصلی روشن می‌کند.'),
                            ]),

                            Textarea::make('profile__bio_short')
                                ->label('بیوی کوتاه')
                                ->rows(3)
                                ->maxLength(300)
                                ->helperText('همان چیزی که کارفرما در سه ثانیه‌ی اول می‌خواند. برای لانچ الزامی است.'),

                            Textarea::make('profile__bio_long')
                                ->label('بیوی بلند')
                                ->rows(6)
                                ->helperText('اختیاری — با خط خالی، پاراگراف جدید می‌سازد. خالی بماند، سکشن «درباره‌ی من» مخفی می‌شود.'),

                            TagsInput::make('profile__expertise')
                                ->label('تخصص‌ها')
                                ->helperText('به‌صورت چیپ زیر بیوی کوتاه نمایش داده می‌شود.'),

                            Grid::make(2)->schema([
                                FileUpload::make('profile__avatar')
                                    ->label('آواتار')
                                    ->image()
                                    ->avatar()
                                    ->disk('public')
                                    ->directory('profile')
                                    ->maxSize(4096)
                                    ->helperText('بدون تصویر، مونوگرام حرف اول نام نمایش داده می‌شود.')
                                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => app(ImageOptimizer::class)
                                        ->store($file, 'profile')),

                                FileUpload::make('profile__resume_pdf')
                                    ->label('فایل PDF رزومه')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->disk('public')
                                    ->directory('resume')
                                    ->maxSize(8192)
                                    ->helperText('دکمه‌ی «دانلود رزومه» فقط با وجود این فایل رندر می‌شود.'),
                            ]),
                        ]),

                    Tabs\Tab::make('تماس')
                        ->icon('heroicon-m-envelope')
                        ->schema([
                            TextInput::make('contact__notification_email')
                                ->label('ایمیل اعلان')
                                ->email()
                                ->maxLength(150)
                                ->helperText('پیام‌های فرم تماس به این نشانی ارسال می‌شوند. خالی بماند، پیام‌ها فقط در پنل ثبت می‌شوند.'),

                            Textarea::make('contact__response_note')
                                ->label('یادداشت پاسخ‌گویی')
                                ->rows(3)
                                ->helperText('کنار فرم تماس نمایش داده می‌شود — مثلاً «معمولاً ظرف ۲۴ ساعت پاسخ می‌دهم».'),
                        ]),

                    Tabs\Tab::make('سئو و آنالیتیکس')
                        ->icon('heroicon-m-magnifying-glass')
                        ->schema([
                            TextInput::make('seo__meta_title')
                                ->label('عنوان متای پیش‌فرض')
                                ->maxLength(60)
                                ->helperText('حداکثر ۶۰ کاراکتر. خالی = نام نمایشی.'),

                            Textarea::make('seo__meta_description')
                                ->label('توضیح متای پیش‌فرض')
                                ->rows(2)
                                ->maxLength(160)
                                ->helperText('حداکثر ۱۶۰ کاراکتر. خالی = بیوی کوتاه.'),

                            FileUpload::make('seo__og_image')
                                ->label('تصویر اشتراک‌گذاری (OG)')
                                ->image()
                                ->disk('public')
                                ->directory('seo')
                                ->maxSize(4096)
                                ->helperText('نسبت پیشنهادی ۱۲۰۰×۶۳۰ — هنگام اشتراک لینک در شبکه‌های اجتماعی دیده می‌شود.')
                                ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => app(ImageOptimizer::class)
                                    ->store($file, 'seo')),

                            Grid::make(2)->schema([
                                TextInput::make('seo__matomo_url')
                                    ->label('نشانی Matomo')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://analytics.hosseinyariam.ir/')
                                    ->helperText('خالی بماند، هیچ اسکریپت ردیابی‌ای بارگذاری نمی‌شود.'),

                                TextInput::make('seo__matomo_site_id')
                                    ->label('شناسه‌ی سایت در Matomo')
                                    ->numeric()
                                    ->placeholder('1'),
                            ]),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(SettingsService::class);

        foreach ($data as $flatKey => $value) {
            [$group, $key] = explode('__', $flatKey, 2);
            $settings->set($group, $key, $value);
        }

        Notification::make()
            ->title('تنظیمات ذخیره شد')
            ->body('تغییرات بلافاصله روی سایت عمومی اعمال می‌شوند.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('ذخیره‌ی تنظیمات')
                ->submit('save'),
        ];
    }
}
