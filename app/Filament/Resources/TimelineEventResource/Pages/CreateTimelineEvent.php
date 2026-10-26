<?php

declare(strict_types=1);

namespace App\Filament\Resources\TimelineEventResource\Pages;

use App\Filament\Resources\TimelineEventResource;
use App\Models\Company;
use App\Models\Note;
use App\Models\Product;
use App\Models\Project;
use Filament\Resources\Pages\CreateRecord;

/**
 * ایجاد رویداد مسیر.
 *
 * فیلد `timelineable_type_key` یک فیلد **مجازی** رابط کاربری است و
 * ستون دیتابیس نیست. اینجا به `timelineable_type` واقعی (کلاس کامل
 * morph) تبدیل و فیلد مجازی حذف می‌شود.
 */
class CreateTimelineEvent extends CreateRecord
{
    protected static string $resource = TimelineEventResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return self::resolvePolymorphicTarget($data);
    }

    /**
     * تبدیل کلید نوع به کلاس morph و حذف فیلد مجازی.
     *
     * اگر نوع انتخاب نشده باشد، هر دو ستون morph پاک می‌شوند تا
     * رویداد «بدون هدف» یک حالت معتبر بماند (ستون‌ها nullableاند).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function resolvePolymorphicTarget(array $data): array
    {
        $typeKey = $data['timelineable_type_key'] ?? null;
        unset($data['timelineable_type_key']);

        $map = [
            'project' => Project::class,
            'product' => Product::class,
            'company' => Company::class,
            'note' => Note::class,
        ];

        if (filled($typeKey) && isset($map[$typeKey]) && filled($data['timelineable_id'] ?? null)) {
            $data['timelineable_type'] = (new $map[$typeKey])->getMorphClass();
        } else {
            $data['timelineable_type'] = null;
            $data['timelineable_id'] = null;
        }

        return $data;
    }
}
