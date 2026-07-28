<?php

namespace CustomSidebarManager\Support;

use App\Facades\Plugin;
use Throwable;

final class CustomSidebarAuthorization
{
    public static function canManage(): bool
    {
        $user = auth()->user();
        $plugin = Plugin::getPlugin('CustomSidebarManager');

        if (! $user || ! $plugin) {
            return false;
        }

        try {
            if ($user->can('Plugin:update')) {
                return true;
            }
        } catch (Throwable) {
            // Continue with the policy-style check used by some LeConfe builds.
        }

        try {
            return $user->can('update', $plugin);
        } catch (Throwable) {
            return false;
        }
    }
}
