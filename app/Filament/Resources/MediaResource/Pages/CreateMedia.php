<?php

declare(strict_types=1);

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Services\MediaService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * آپلود رسانه.
 *
 * ── چرا ذخیره از `MediaService` عبور می‌کند ──
 * فیلد آپلود با `storeFiles(false)` فایل را موقت نگه می‌دارد. ذخیره‌ی
 * واقعی اینجا از سرویس انجام می‌شود که اعتبارسنجی واقعی MIME، سقف
 * حجم، نام تصادفی و جلوگیری از اجرای فایل را تضمین می‌کند. اگر
 * FileUpload خودش ذخیره کند، این محافظت‌ها دور زده می‌شوند.
 */
class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        /** @var TemporaryUploadedFile|null $upload */
        $upload = $data['upload'] ?? null;

        if (! $upload instanceof TemporaryUploadedFile) {
            // نباید اتفاق بیفتد چون فیلد required است؛ ولی به‌جای
            // خطای مبهم، پیام روشن می‌دهیم.
            Notification::make()
                ->title('فایلی انتخاب نشده است')
                ->danger()
                ->send();

            $this->halt();
        }

        /** @var MediaService $service */
        $service = app(MediaService::class);
        $media = $service->store(
            file: $upload,
            userId: auth()->id(),
            altText: $data['alt_text'] ?? null,
        );

        // فراداده‌ی قابل ویرایش که سرویس آپلود نمی‌شناسد.
        $media->forceFill([
            'alt_text' => $data['alt_text'] ?? null,
            'caption' => $data['caption'] ?? null,
        ])->save();

        return $media;
    }

    /**
     * فیلد آپلود یک فیلد مجازی است و نباید به مدل نوشته شود.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }
}
