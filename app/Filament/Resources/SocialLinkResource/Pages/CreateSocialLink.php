<?php

declare(strict_types=1);

namespace App\Filament\Resources\SocialLinkResource\Pages;

use App\Filament\Resources\SocialLinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSocialLink extends CreateRecord
{
    protected static string $resource = SocialLinkResource::class;
}
