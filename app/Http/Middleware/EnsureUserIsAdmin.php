<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for admin-only routes.
 *
 * This is a coarse door check. It is never the only protection: controllers
 * re-verify ownership and role for the specific record being acted on, because
 * an admin route is a public HTTP endpoint and passing this check does not
 * mean the user may touch *this* row.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next, string $level = 'admin'): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->guest(route('admin.login'));
        }

        if (!$user->is_active) {
            abort(403, 'This account has been deactivated.');
        }

        $allowed = match ($level) {
            'author' => $user->role->canAuthor(),
            default => $user->role->isAdmin(),
        };

        if (!$allowed) {
            abort(403);
        }

        return $next($request);
    }
}
