<?php

namespace App\Model\Base;

use App\Helper\Common;
use App\Model\Base\Traits\CustomBuilder;

class BaseModel extends \Illuminate\Database\Eloquent\Model
{
    use CustomBuilder;

    protected $_params = [];

    public function setParams($params = [])
    {
        $this->_params = $params;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function getList(array $params = [], array $columns = [])
    {
        return $this->search($params, $columns)->paginate(Common::getConfig('pagination.' . $this->getTable(), Common::getConfig('pagination.default')));
    }
}
