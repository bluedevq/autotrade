<?php

namespace App\Http\Controllers\Module\Backend;

use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\Traits\CRUD;
use App\Http\Controllers\Base\Traits\Repository;

/**
 * Class BackendController
 * @package App\Http\Controllers\Module\Backend
 */
class BackendController extends BaseController
{
    use CRUD, Repository;

    protected $_area = 'backend';

    public function __construct()
    {
        parent::__construct();
    }

    public function create()
    {
        $this->_prepareForm();
        return $this->render();
    }

    public function edit($id)
    {
        $this->_prepareForm($id);
        return $this->render();
    }

    protected function _prepareForm($id = null)
    {
    }
}
