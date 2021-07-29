<?php

namespace App\Http\Controllers\Base;

use App\Helper\Common;
use App\Http\Mail\Mailer;
use App\Http\Supports\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Class BaseController
 * @package App\Http\Controllers\Base
 */
class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use ApiResponse;

    protected $_area = '';

    protected $_mailer = '';

    protected $_title = '';

    protected $_viewData = array();

    public function __construct()
    {
        $this->setMailer(new Mailer());
    }

    public function setArea($area)
    {
        $this->_area = $area;
    }

    public function getArea()
    {
        return $this->_area;
    }

    public function setMailer($mailer)
    {
        $this->_mailer = $mailer;
    }

    public function getMailer()
    {
        return $this->_mailer;
    }

    public function setTitle($title)
    {
        $this->_title = $title;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setViewData($viewData)
    {
        $this->_viewData = array_merge($this->getViewData(), (array)$viewData);
        return $this;
    }

    public function getViewData($key = null)
    {
        if ($key) {
            return isset($this->_viewData[$key]) ? $this->_viewData[$key] : null;
        }
        return $this->_viewData;
    }

    public function getCurrentRouteName()
    {
        return Route::currentRouteName();
    }

    public function render($view = null, $params = array(), $mergeData = array())
    {
        $area = Common::getArea();
        $controllerName = $this->getCurrentControllerName();
        $actionName = $this->getCurrentActionName();
        $routeName = $this->getCurrentRouteName();
        $routePrefix = str_replace('.' . $actionName, '', $routeName);
        if (empty($view)) {
            $view = $area . '.' . $controllerName . '.' . $actionName;
        }
        $params += array(
            'title' => $this->getTitle(),
            '_form' => $this->getArea() . '.' . $controllerName . '._form',
            'controllerName' => $controllerName,
            'actionName' => $actionName,
            'routeName' => $routeName,
            'routePrefix' => $routePrefix,
            'area' => $this->getArea(),
        );
        $params += $this->getViewData();
        $this->setViewData($params);

        return view($view, $params, $mergeData);
    }

    public function getCurrentControllerName()
    {
        $currentRoute = Route::currentRouteAction();
        $currentRoute = explode('@', $currentRoute);
        $controller = isset($currentRoute[0]) ? $currentRoute[0] : null;
        $controller = explode('\\', $controller);
        $controller = last($controller);
        $controller = str_replace('Controller', '', $controller);
        return Str::snake($controller);
    }

    public function getCurrentActionName()
    {
        $currentRoute = Route::currentRouteAction();
        $currentRoute = explode('@', $currentRoute);
        return Str::snake(last($currentRoute));
    }

    public function getParams()
    {
        return request()->all();
    }

    public function getParam($key, $default = null)
    {
        return request()->get($key, $default);
    }

    protected function _back()
    {
        return Redirect::back();
    }

    protected function _to($url, $params = array())
    {
        $data = ['url' => $url, 'params' => $params];
        $url = $data['url'];
        $params = $data['params'];
        if (strpos($url, 'http') !== false) {
            return new RedirectResponse(url($url, $params));
        }
        if (strpos($url, '.') !== false) {
            $url = route($url, $params);
        }

        return Redirect::to($url)->with($params);
    }

    protected function _backWithError($errors)
    {
        return $this->_back()->withErrors($errors)->withInput(request()->all());
    }

    protected function _redirectToHome()
    {
        $url = request()->get('return_url', Common::buildDashBoardUrl());
        $url = empty($url) ? Common::buildDashBoardUrl() : $url;
        return $this->_to($url);
    }
}
