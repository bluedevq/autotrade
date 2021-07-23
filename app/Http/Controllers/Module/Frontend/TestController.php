<?php

namespace App\Http\Controllers\Module\Frontend;

/**
 * Class TestController
 * @package App\Http\Controllers\Module\Frontend
 */
class TestController extends FrontendController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->_to('backend.login');
    }
}
