<?php

namespace App\Http\Controllers\Base\Traits;

/**
 * Trait Model
 * @package App\Http\Controllers\Base\Traits
 */
trait Model
{
    protected $_model = null;

    protected $_models = [];

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

    public function registModel(...$models)
    {
        foreach ($models as $model) {
            if (is_string($model)) {
                $model = app()->make($model);
            }
            $this->_models[get_class($model)] = $model;
        }
        return $this;
    }

    public function fetchModel($classname)
    {
        if ($classname) {
            return isset($this->_models[$classname]) ? $this->_models[$classname] : null;
        }
        return null;
    }
}
