<?php

namespace App\Http\Controllers\Module\Backend;

/**
 * Class HomeController
 * @package App\Http\Controllers\Module\Backend
 */
class HomeController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->render();
    }
}
