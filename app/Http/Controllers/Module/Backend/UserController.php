<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Model\Entities\BotQueue;
use App\Model\Entities\BotUser;
use App\Model\Entities\BotUserMethod;
use App\Model\Entities\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
    /**
     * @param User $user
     * @param BotQueue $botQueue
     * @param BotUser $botUser
     */
    public function __construct(User $user, BotQueue $botQueue, BotUser $botUser, BotUserMethod $botUserMethod)
    {
        parent::__construct();
        $this->setModel($user);
        $this->registModel($botQueue, $botUser, $botUserMethod);
    }

    public function index()
    {
        $this->setEntities($this->getModel()->getList($this->getParams()));
        return $this->render();
    }

    public function profile($id)
    {
        $this->_prepareForm($id);
        return $this->render();
    }

    public function valid()
    {
        // validate data
        $params = $this->getParams();
        $this->getModel()->setParams($params);
        $rules = $this->getModel()->rules();
        $messages = $this->getModel()->messages();
        $validator = Validator::make($params, $rules, $messages);
        if ($validator->fails()) {
            return $this->_backWithError($validator->errors()->first());
        }
        if (blank(Arr::get($params, 'password'))) {
            unset($params['password']);
        }

        // save data
        DB::beginTransaction();
        try {
            $entity = $this->getModel()->fill($params);
            $create = true;
            if ($entity->id) {
                $entity->exists = true;
                $create = false;
            }
            if ($entity->password) {
                $entity->password = Hash::make($entity->password);
            }
            $entity->save();
            DB::commit();
            Session::flash('success', [($create ? 'Thêm mới' : 'Chỉnh sửa') . ' thành công.']);
            return $this->_to($this->getParam('profile') ? 'user.profile' : 'user.edit', $entity->id);
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
            // delete user
            $entity = $this->getModel()->where('id', $id)->first();
            $entity->delete();
            // delete bot queue
            $botQueue = $this->fetchModel(BotQueue::class)->where('user_id', $id);
            $botUserIds = $botQueue->pluck('bot_user_id', 'id');
            $botQueue->delete();
            // delete bot user
            foreach ($botUserIds as $botUserId) {
                $this->fetchModel(BotUser::class)->where('id', $botUserId)->delete();
                $this->fetchModel(BotUserMethod::class)->where('bot_user_id', $botUserId)->delete();
            }
            DB::commit();

            session()->flash('success', ['Xóa thành công.']);

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
            $entity = $this->getModel()->where('id', $id)->with(['botUserQueues.botUser'])->first();
        }
        if (session()->hasOldInput()) {
            $entity->setRawAttributes(session()->getOldInput());
        }
        // role
        $roles = Common::getConfig('user_role_text');
        if (backendGuard()->user()->role == Common::getConfig('user_role.admin')) {
            unset($roles[Common::getConfig('user_role.supper_admin')]);
        }
        // status
        $status = [
            Common::getConfig('user_status.stop') => Common::getConfig('user_status_text.0'),
            Common::getConfig('user_status.active') => Common::getConfig('user_status_text.1'),
        ];
        $this->setViewData([
            'entity' => $entity,
            'status' => $status,
            'roles' => $roles,
        ]);
        parent::_prepareForm($id);
    }
}
