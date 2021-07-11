<?php

namespace App\Http\Middleware;

/**
 * Class FrontendAuthenticate
 * @package App\Http\Middleware
 */
class FrontendAuthenticate extends BaseAuthenticate
{
    public function init()
    {
        $this->setGuard(frontendGuard());
    }

    protected function _toLogin($request)
    {
        if (!frontendGuard()->user()->isSuperAdmin()) {
            return parent::_toLogin($request)->with('error', 'Not permission');
        }
        return parent::_toLogin($request);
    }
}
