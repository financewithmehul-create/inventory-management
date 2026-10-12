<?php

namespace Webkul\Support\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Str;

/**
 * A grid of ready-made avatars (public/images/roles/*.png). The state is the file's slug, for example "accountant".
 */
class AvatarPicker extends Field
{
    protected string $view = 'support::forms.avatar-picker';

    /**
     * @return array<string, string> slug => label
     */
    public function getAvatarOptions(): array
    {
        $options = [];

        foreach (glob(public_path('images/roles/*.png')) ?: [] as $file) {
            $slug = basename($file, '.png');

            $options[$slug] = Str::headline($slug);
        }

        return $options;
    }

    public static function isValidSlug(?string $slug): bool
    {
        return filled($slug) && preg_match('/^[a-z0-9-]+$/', $slug) === 1 && is_file(public_path("images/roles/{$slug}.png"));
    }
}
