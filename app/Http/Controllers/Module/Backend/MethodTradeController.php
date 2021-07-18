<?php

namespace App\Http\Controllers\Module\Backend;

use App\Model\Entities\BotUserMethod;

/**
 * Class MethodTradeController
 * @package App\Http\Controllers\Module\Backend
 */
class MethodTradeController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $methodDefaults = BotUserMethod::where(function ($q) {
            $q->orWhere('deleted_at', '');
            $q->orWhereNull('deleted_at');
        })->get();
        $this->setViewData(['entities' => $methodDefaults]);

        return $this->render();
    }

    public function valid()
    {
        // validate data

        // save data

        return $this->_to('method-trade.index');
    }

    protected function _prepareForm($id = null)
    {
        $entity = new BotUserMethod();
        if ($id) {
            $entity = BotUserMethod::where('id', $id)->first();
        }

        $this->setViewData(['entity' => $entity]);
    }
}
