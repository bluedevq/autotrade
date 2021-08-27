<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Class ForceSSL
 * @package App\Http\Middleware
 */
class ForceSSL
{
    public function handle($request, Closure $next)
    {
        if (getSystemConfig('use_ssl')) {
            // for Proxies
            Request::setTrustedProxies([$request->getClientIp()], Request::HEADER_X_FORWARDED_ALL);

            if (!$request->isSecure()) {
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $next($request);
    }

}
