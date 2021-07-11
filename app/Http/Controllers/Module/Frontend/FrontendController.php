<?php

namespace App\Http\Controllers\Module\Frontend;

use App\Http\Controllers\Base\BaseController;

/**
 * Class FrontendController
 * @package App\Http\Controllers\Module\Frontend
 */
class FrontendController extends BaseController
{
    protected $_area = 'frontend';

    public function __construct()
    {
        parent::__construct();
    }
}
