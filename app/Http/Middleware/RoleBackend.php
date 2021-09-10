<?php

namespace App\Http\Middleware;

use App\Helper\Common;
use Closure;

/**
 * Class RoleBackend
 * @package App\Http\Middleware
 */
class RoleBackend
{
    public function handle($request, Closure $next)
    {
        if (backendGuard()->user()->role == Common::getConfig('user_role.normal') && !$request->routeIs('user.profile','user.valid')) {
            return redirect(route('bot.index'));
        }

        return $next($request);
    }
}
