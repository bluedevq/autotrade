<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Model\Entities\BotMethodDefault;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

/**
 * Class MethodController
 * @package App\Http\Controllers\Module\Backend
 */
class DefaultMethodController extends BackendController
{
    /**
     * @param BotMethodDefault $botMethodDefault
     */
    public function __construct(BotMethodDefault $botMethodDefault)
    {
        parent::__construct();
        $this->setModel($botMethodDefault);
    }

    public function valid()
    {
        // validate data
        $params = $this->getParams();
        $params['signal'] = explode(Common::getConfig('aresbo.order_signal_delimiter'), $params['signal']);
        $params['order_pattern'] = explode(Common::getConfig('aresbo.order_pattern_delimiter'), $params['order_pattern']);
        $this->getModel()->setParams($params);
        $validator = Validator::make($params, $this->getModel()->rules(), $this->getModel()->messages());
        if ($validator->fails()) {
            return $this->_backWithError($validator->errors()->first());
        }

        // save data
        DB::beginTransaction();
        try {
            $entity = $this->getModel()->fill($this->getParams());
            $create = true;
            if ($entity->id) {
                $entity->exists = true;
                $create = false;
            } else {
                $entity->color = $this->_randomColor();
                $entity->status = Common::getConfig('aresbo.method.active');
            }
            $entity->save();
            Session::flash('success', [($create ? 'Thêm mới' : 'Chỉnh sửa') . ' thành công.']);
            DB::commit();
            return $this->_to('default.method.edit', $entity->id);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
        }

        return $this->_backWithError(new MessageBag(['Lỗi hệ thống. Vui lòng thử lại.']));
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            // delete method
            $entity = $this->getModel()->where('id', $id)->first();
            $entity->delete();
            Session::flash('success', ['Xóa thành công.']);
            DB::commit();

            return $this->_back();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
        }
        return $this->_back()->withErrors(new MessageBag(['Xóa thất bại, vui lòng thử lại.']));
    }

    protected function _prepareForm($id = null)
    {
        $entity = $this->getModel();
        if ($id) {
            $entity = $this->getModel()->where('id', $id)->first();
        }
        if (session()->hasOldInput()) {
            $entity->setRawAttributes(session()->getOldInput());
        }
        $this->setViewData(['entity' => $entity]);
        parent::_prepareForm($id);
    }
}
