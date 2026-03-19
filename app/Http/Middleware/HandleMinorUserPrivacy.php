<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleMinorUserPrivacy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $viewer = $request->user();
        $targetUser = $this->resolveTargetUser($request, $viewer);

        $privacy = [
            'viewer_is_logged_in' => $viewer !== null,
            'viewer_is_privileged' => $viewer?->hasAnyRole(['admin', 'rector', 'manager']) ?? false,
            'target_is_minor' => $targetUser?->isMinorPrivacyRestricted() ?? false,
            'can_view_avatar' => $targetUser ? $targetUser->canViewerSeeMinorSensitiveFields($viewer) : true,
            'can_view_bio' => $targetUser ? $targetUser->canViewerSeeMinorSensitiveFields($viewer) : true,
            'can_view_social' => $targetUser ? $targetUser->canViewerSeeMinorSensitiveFields($viewer) : true,
            'can_view_battle_name' => $targetUser ? $targetUser->canViewerSeeMinorBattleName($viewer) : true,
            'can_view_institutions' => $targetUser ? $targetUser->canViewerSeeMinorInstitutions($viewer) : true,
            'can_edit_restricted_profile_fields' => !($targetUser && $viewer && $targetUser->is($viewer) && $targetUser->isMinorPrivacyRestricted()),
        ];

        $request->attributes->set('minor_privacy', $privacy);

        if (
            $request->isMethod('patch')
            && $targetUser
            && $viewer
            && $targetUser->is($viewer)
            && $targetUser->isMinorPrivacyRestricted()
        ) {
            $request->merge([
                'instagram' => null,
                'telegram' => null,
                'bio' => null,
            ]);
        }

        return $next($request);
    }

    private function resolveTargetUser(Request $request, ?User $viewer): ?User
    {
        $routeUser = $request->route('user');
        if ($routeUser instanceof User) {
            return $routeUser;
        }

        if ($request->routeIs('profile.edit', 'profile.update')) {
            return $viewer;
        }

        return null;
    }
}
