<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * @param  array<int, string>|string  $roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $roles = $roles ?: [];

        if ($roles === []) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if (method_exists($user, 'hasRoleOrAbove') && $user->hasRoleOrAbove($role)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
