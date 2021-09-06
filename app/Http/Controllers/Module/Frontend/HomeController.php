<?php

namespace App\Http\Controllers\Module\Frontend;

/**
 * Class HomeController
 * @package App\Http\Controllers\Module\Frontend
 */
class HomeController extends FrontendController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->_to('backend.login');
        return $this->render();
    }
}
