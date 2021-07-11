<?php

namespace App\Repository\Base;

class BaseRepository
{
    protected $_model;

    protected $_validator;

    public function __construct()
    {
        $this->setModel($this->model());
        $this->setValidator($this->validator());
    }

    public function setModel($model)
    {
        if (is_string($model)) {
            $model = app()->make($model);
        }
        $this->_model = $model;
    }

    public function getModel()
    {
        return $this->_model;
    }

    public function setValidator($validator)
    {
        $this->_validator = $validator;
    }

    public function getValidator()
    {
        return $this->_validator;
    }

    public function model()
    {
        return null;
    }

    public function validator()
    {
        return null;
    }

    public function getList()
    {
        return $this->getModel()->get();
    }
}
