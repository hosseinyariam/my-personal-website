<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Media;
use App\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * پیوست رسانه به هر موجودیت.
 *
 * چرا `MorphToMany` روی خودِ Media و با pivot صریح: `media_attachments`
 * یک نقش (`role`) و ترتیب (`sort_order`) هم دارد، پس نمی‌تواند یک
 * `morphToMany` ساده باشد. دسترسی از طریق مدل pivot این دو را حفظ می‌کند.
 */
trait HasMedia
{
    public function mediaAttachments(): MorphMany
    {
        return $this->morphMany(MediaAttachment::class, 'mediable')->orderBy('sort_order');
    }

    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable', 'media_attachments')
            ->withPivot(['role', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /** رسانه‌های یک نقش خاص — مثلاً `gallery` یا `cover`. */
    public function mediaInRole(string $role): Collection
    {
        return $this->media()->wherePivot('role', $role)->get();
    }

    /** تصویر شاخص — اولین رسانه در نقش `cover`، با بازگشت به اولین رسانه. */
    public function coverImage(): ?Media
    {
        return $this->mediaInRole('cover')->first() ?? $this->media->first();
    }

    /** گالری — همه‌ی رسانه‌ها به‌جز تصویر شاخص. */
    public function gallery(): Collection
    {
        $cover = $this->coverImage();

        return $this->media->reject(fn (Media $m): bool => $cover !== null && $m->id === $cover->id)->values();
    }
}
