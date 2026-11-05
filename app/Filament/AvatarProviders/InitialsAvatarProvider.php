<?php

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Draws the person's initials locally, so no name is ever sent to an outside avatar service.
 */
class InitialsAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        $name = (string) Filament::getNameForDefaultAvatar($record);

        $initials = Str::of($name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" fill="#1f3b57"/>'
            .'<text x="50%" y="50%" dy=".35em" text-anchor="middle" fill="#f5f4f0" font-family="IBM Plex Sans, Arial, sans-serif" font-size="26" font-weight="600">'
            .e($initials ?: '?')
            .'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
