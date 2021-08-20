<?php

namespace App\Http\Controllers\Module\Backend;

use App\Model\Entities\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

/**
 * Class UserController
 * @package App\Http\Controllers\Module\Backend
 */
class UserController extends BackendController
{
    public function __construct(User $user)
    {
        parent::__construct();
        $this->setModel($user);
    }

    public function index()
    {
        $this->setEntities($this->getModel()->getList($this->getParams()));
        return $this->render();
    }

    public function valid()
    {
        // validate data
        $params = $this->getParams();
        $rules = $this->getModel()->rules();
        $messages = $this->getModel()->messages();
        $validator = Validator::make($params, $rules, $messages);
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
            }
            $entity->save();
            DB::commit();
            Session::flash('success', [($create ? 'Thêm mới' : 'Chỉnh sửa') . ' thành công.']);
            return $this->_to('user.edit', $entity->id);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
        }

        return $this->_backWithError(new MessageBag(['Lỗi hệ thống. Vui lòng thử lại.']));
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
