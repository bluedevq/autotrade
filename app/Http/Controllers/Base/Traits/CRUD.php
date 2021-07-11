<?php

namespace App\Http\Controllers\Base\Traits;

/**
 * Trait CRUD
 * @package App\Http\Controllers\Base\Traits
 */
trait CRUD
{
    public function index()
    {
        return $this->render();
    }

    public function create()
    {
    }

    public function edit($id)
    {
    }

    public function show($id)
    {
    }

    public function confirm()
    {
    }

    public function update()
    {
    }

    public function store()
    {
    }

    public function destroy($id)
    {
    }

    public function restore($id)
    {
    }

    public function render($view = null, $params = array(), $mergeData = array())
    {
        if (method_exists(get_parent_class($this), 'render')) {
            return parent::render($view, $params, $mergeData);
        }
        return view($view, $params, $mergeData);
    }
}
