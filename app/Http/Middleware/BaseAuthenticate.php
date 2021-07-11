<?php

namespace App\Http\Middleware;

use App\Helper\Common;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

/**
 * Class BaseAuthenticate
 * @package App\Http\Middleware
 */
class BaseAuthenticate extends Middleware
{
    protected $_guard = null;

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
    }

    public function getGuard()
    {
        return $this->_guard;
    }

    public function setGuard($guard)
    {
        $this->_guard = $guard;
    }

    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->_isResetRoute($request) || $request->routeIs('*login')) {
            return $next($request);
        }

        if (!$this->getGuard()->check()) {
            return $this->_toLogin($request);
        }
        return $next($request);
    }

    protected function _toLogin($request)
    {
        return redirect($this->_getBackUrl($request));
    }

    protected function _getBackUrl($request)
    {
        $params = ['return_url' => Common::buildDashBoardUrl()];
        $url = route('backend.login', $params);
        return $url;
    }

    protected function _isResetRoute($request)
    {
        if ($request->routeIs('*password.*')) {
            return true;
        }
        return false;
    }

}
