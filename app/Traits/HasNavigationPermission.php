<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait HasNavigationPermission
{
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        $className = class_basename(static::class);

        // dd($className);
        $allowedRoles = config("navigation_permissions.{$className}");
        // Log::info('Navigation permission check', [
        //     'resource'      => static::class,
        //     'resource_name' => $className,
        //     'user_id'       => $user->id,
        //     'user_role'     => $user->role,
        //     'allowed_roles' => $allowedRoles,
        // ]);

        // If no roles defined, allow all authenticated users
        if ($allowedRoles === null || empty($allowedRoles)) return true;

        return in_array($user->role, $allowedRoles);
    }
}
