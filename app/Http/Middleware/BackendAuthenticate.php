<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;

/**
 * Class BackendAuthenticate
 * @package App\Http\Middleware
 */
class BackendAuthenticate extends BaseAuthenticate
{
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->_isResetRoute($request) || $request->routeIs('*login')) {
            return $next($request);
        }

        if (!$this->getGuard()->check()) {
            return $this->_toLogin($request);
        }

        if (Carbon::parse(backendGuard()->user()->expired_date)->lessThan(Carbon::now())) {
            backendGuard()->logout();
            Session::flush();
            return $this->_toLogin($request)->withErrors(new MessageBag(['Tài khoản của bạn đã hết hạn. Vui lòng liên hệ admin để được hỗ trợ.']));
        }

        return $next($request);
    }

    public function init()
    {
        $this->setGuard(backendGuard());
    }

    protected function _toLogin($request)
    {
        if (blank(backendGuard()->user()) || !backendGuard()->user()->isSuperAdmin()) {
            return parent::_toLogin($request)->with('error', 'Not permission');
        }
        return parent::_toLogin($request);
    }
}
