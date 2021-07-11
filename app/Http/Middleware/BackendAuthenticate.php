<?php

namespace App\Http\Middleware;

/**
 * Class BackendAuthenticate
 * @package App\Http\Middleware
 */
class BackendAuthenticate extends BaseAuthenticate
{
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
