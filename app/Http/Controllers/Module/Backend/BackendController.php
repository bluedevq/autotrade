<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\Traits\CRUD;
use App\Http\Controllers\Base\Traits\Model;

/**
 * Class BackendController
 * @package App\Http\Controllers\Module\Backend
 */
class BackendController extends BaseController
{
    use CRUD, Model;

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

    public function render($view = null, $params = array(), $mergeData = array())
    {
        $this->setTitle(Common::getConfig('aresbo.bot_title'));
        return parent::render($view, $params, $mergeData);
    }

    protected function _prepareForm($id = null)
    {
    }

    protected function _sendMail($subject, $to, $view, $data = [])
    {
        $from = Common::getConfig('mail.from');
        $sender = Common::getConfig('mail.sender');
        $content = '';
        $contentHtml = view($view, compact('data'));
        return $this->getMailer()->sendmail($from, $sender, $to, $subject, $content, [], $contentHtml);
    }

    protected function _generateToken($data, $type)
    {
        if ($type == Common::getConfig('user_status.verify')) {
            return hash('sha256', Common::getConfig('hash.verify.password') . Common::getConfig('hash.delimiter') . json_encode($data));
        }
        if ($type == Common::getConfig('user_status.forgot_password')) {
            return hash('sha256', Common::getConfig('hash.forgot_password.password') . Common::getConfig('hash.delimiter') . json_encode($data));
        }
        return hash('sha256', Common::getConfig('hash.default.password') . Common::getConfig('hash.delimiter') . json_encode($data));
    }
}
