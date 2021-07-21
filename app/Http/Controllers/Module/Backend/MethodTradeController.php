<?php

namespace App\Http\Controllers\Module\Backend;

use App\Model\Entities\BotUserMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

/**
 * Class MethodTradeController
 * @package App\Http\Controllers\Module\Backend
 */
class MethodTradeController extends BackendController
{
    public function __construct(BotUserMethod $botUserMethod)
    {
        parent::__construct();
        $this->setModel($botUserMethod);
    }

    public function index()
    {
        $methodDefaults = $this->getModel()->where(function ($q) {
            $q->orWhere('deleted_at', '');
            $q->orWhereNull('deleted_at');
        })
            ->orderBy($this->getParam('sort_field', 'id'), $this->getParam('sort_type', 'asc'))
            ->get();
        $this->setViewData(['entities' => $methodDefaults]);

        return $this->render();
    }

    public function valid()
    {
        // validate data
        $validator = Validator::make($this->getParams(), $this->getModel()->rules());
        if ($validator->fails()) {
            return $this->_to('method-trade.index')->withErrors($validator)->withInput();
        }

        // save data
        DB::beginTransaction();
        try {
            $entity = $this->getModel()->fill($this->getParams());
            if ($entity->id) {
                $entity->exists = true;
            }
            $entity->save();

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
        }

        return $this->_to('method-trade.index')->withSuccess(new MessageBag(['success' => 'Lưu thành công']));
    }

    protected function _prepareForm($id = null)
    {
        $entity = $this->getModel();
        if ($id) {
            $entity = $this->getModel()->where('id', $id)->first();
        }

        $this->setViewData(['entity' => $entity]);
    }
}
