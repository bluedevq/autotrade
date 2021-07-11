<?php

namespace App\Http\Controllers\Base\Traits;

/**
 * Trait Repository
 * @package App\Http\Controllers\Base\Traits
 */
trait Repository
{
    protected $_repository = null;

    protected $_repositories = [];

    public function setRepository($repository)
    {
        if (is_string($repository)) {
            $repository = app()->make($repository);
        }
        $this->_repository = $repository;
    }

    public function getRepository()
    {
        return $this->_repository;
    }

    public function registRepository(...$repositories)
    {
        foreach ($repositories as $repository) {
            if (is_string($repository)) {
                $repository = app()->make($repository);
            }
            $this->_repositories[get_class($repository)] = $repository;
        }
        return $this;
    }

    public function fetchRepository($classname)
    {
        if ($classname) {
            return isset($this->_repositories[$classname]) ? $this->_repositories[$classname] : null;
        }
        return null;
    }

    public function getModel($className)
    {
        return $this->fetchRepository($className)->getModel();
    }

}
