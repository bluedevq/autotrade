<?php

namespace App\Model\Base;

class BaseModel extends \Illuminate\Database\Eloquent\Model
{
    protected $_params = [];

    public function setParams($params = [])
    {
        $this->_params = $params;
    }

    public function getParams()
    {
        return $this->_params;
    }
}
