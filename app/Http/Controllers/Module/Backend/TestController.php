<?php

namespace App\Http\Controllers\Module\Backend;

use App\Repository\Module\Backend\UserRepository;

/**
 * Class TestController
 * @package App\Http\Controllers\Module\Backend
 */
class TestController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->setRepository(UserRepository::class);
    }
}
