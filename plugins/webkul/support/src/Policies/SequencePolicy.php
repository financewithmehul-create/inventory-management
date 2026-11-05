<?php

namespace Webkul\Support\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Sequence;

/**
 * Document numbering affects every invoice and order, so only people granted it may change it.
 */
class SequencePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_support_sequence');
    }

    public function view(User $user, Sequence $sequence): bool
    {
        return $user->can('view_support_sequence');
    }

    public function create(User $user): bool
    {
        return $user->can('create_support_sequence');
    }

    public function update(User $user, Sequence $sequence): bool
    {
        return $user->can('update_support_sequence');
    }

    public function delete(User $user, Sequence $sequence): bool
    {
        return $user->can('delete_support_sequence');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_support_sequence');
    }
}
