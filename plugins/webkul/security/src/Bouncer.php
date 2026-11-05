<?php

namespace Webkul\Security;

use Webkul\Security\Enums\PermissionType;
use Webkul\Security\Models\User;

class Bouncer
{
    /**
     * Authorized user IDs, cached per signed-in user so a long-running worker never reuses another person's list.
     *
     * @var array<string, array<int, int>|null>
     */
    protected static array $authorizedUserIdsCache = [];

    /**
     * Return user IDs authorized for the current user (null means no restriction).
     */
    public function getAuthorizedUserIds(): ?array
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $permission = $user->resource_permission instanceof PermissionType
            ? $user->resource_permission->value
            : (string) $user->resource_permission;

        $cacheKey = $user->id.'|'.$permission;

        if (array_key_exists($cacheKey, static::$authorizedUserIdsCache)) {
            return static::$authorizedUserIdsCache[$cacheKey];
        }

        if ($user->resource_permission == PermissionType::GLOBAL) {
            $ids = null;
        } elseif ($user->resource_permission == PermissionType::GROUP) {
            $ids = array_values(array_unique(array_merge([$user->id], $this->getCurrentAccessibleUserIds($user))));
        } else {
            $ids = [$user->id];
        }

        return static::$authorizedUserIdsCache[$cacheKey] = $ids;
    }

    public static function flushCache(): void
    {
        static::$authorizedUserIdsCache = [];
    }

    /**
     * Get user IDs of the current user's groups.
     */
    protected function getCurrentAccessibleUserIds(User $user): array
    {
        return User::query()
            ->select('users.id')
            ->leftJoin('user_team', 'users.id', '=', 'user_team.user_id')
            ->leftJoin('teams', 'user_team.team_id', '=', 'teams.id')
            ->whereIn('teams.id', $user->teams()->pluck('id'))
            ->pluck('users.id')
            ->toArray();
    }
}
