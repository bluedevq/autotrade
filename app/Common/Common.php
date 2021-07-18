<?php
if (!function_exists('getSystemConfig')) {
    function getSystemConfig($key, $default = null, $flip = false)
    {
        return config('system.' . $key, $default);
    }
}
if (!function_exists('isBackend')) {
    function isBackend()
    {
        $uri = explode('/', request()->getRequestUri());
        return $uri[1] === \App\Helper\Common::getBackendAlias() || request()->getBaseUrl() === \App\Helper\Common::getBackendDomain();
    }
}
if (!function_exists('isApi')) {
    function isApi()
    {
        $uri = explode('/', request()->getRequestUri());
        return $uri[1] === \App\Helper\Common::getApiAlias() || request()->getBaseUrl() === \App\Helper\Common::getApiDomain();
    }
}
if (!function_exists('getCurrentArea')) {
    function getCurrentArea()
    {
        if (\Illuminate\Support\Facades\App::runningInConsole()) {
            return 'batch';
        }
        if (isBackend()) {
            return 'backend';
        }
        if (isApi()) {
            return 'api';
        }
        return 'frontend';
    }
}
if (!function_exists('getCurrentControllerName')) {
    function getCurrentControllerName()
    {
        return getViewData('controllerName');
    }
}
if (!function_exists('getCurrentAction')) {
    function getCurrentAction()
    {
        return getViewData('actionName');
    }
}
if (!function_exists('getViewData')) {
    function getViewData($key = null)
    {
        if (request()->route()) {
            return request()->route()->getController()->getViewData($key);
        }
        return null;
    }
}
if (!function_exists('getCurrentLangCode')) {
    function getCurrentLangCode($default = 'ja')
    {
        try {
            $lang = \Illuminate\Support\Facades\Session::get(getLocaleKey(), config('app.locale.' . getCurrentArea(), $default));
            return $lang;
        } catch (\Exception $e) {

        } catch (\Error $error) {

        }
        return config('app.locale.' . getCurrentArea(), $default);
    }
}
if (!function_exists('getCurrentLangCode')) {
    function getLocaleKey()
    {
        return isBackend() ? 'locale_backend' : 'locale_frontend';
    }
}
if (!function_exists('getBodyClass')) {
    function getBodyClass()
    {
        return ' area-' . getCurrentArea() . ' c-' . getCurrentControllerName() . ' a-' . getCurrentAction() . ' l-' . getCurrentLangCode();
    }
}
if (!function_exists('loadFile')) {

    function loadFiles($files, $area = '', $type = 'css')
    {
        if (empty($files)) {
            return '';
        }
        $result = '';
        foreach ($files as $item) {
            $type = $area ? ($type . DIRECTORY_SEPARATOR . $area) : $type;
            $filePath = $type . DIRECTORY_SEPARATOR . $item . '.' . $type;
            if (!file_exists(public_path($filePath))) {
                continue;
            }
            $result .= $type == 'css' ? Html::style(buildVersion(public_url($filePath))) : Html::script(buildVersion(public_url($filePath)));
        }
        return $result;
    }
}
if (!function_exists('buildVersion')) {

    function buildVersion($file)
    {
        return $file . '?v=' . getSystemConfig('static_version');

    }
}
if (!function_exists('public_url')) {
    function public_url($url)
    {
        if (strpos($url, 'http') !== false) {
            return $url;
        }

        $appURL = config('app.url');
        $str = substr($appURL, strlen($appURL) - 1, 1);
        if ($str != '/') {
            $appURL .= '/';
        }
        if (\Illuminate\Support\Facades\Request::secure()) {
            $appURL = str_replace('http://', 'https://', $appURL);
        }
        return $appURL . 'public/' . $url;
    }
}
if (!function_exists('backendGuard')) {

    /**
     * @param string $default
     * @return mixed
     */
    function backendGuard($default = 'backend')
    {
        return Auth::guard(getSystemConfig('backend_guard', $default));
    }
}
if (!function_exists('frontendGuard')) {

    /**
     * @param string $default
     * @return mixed
     */
    function frontendGuard($default = 'frontend')
    {
        return Auth::guard(getSystemConfig('frontend_guard', $default));
    }
}
// migrate
if (!function_exists('getUpdatedAtColumn')) {

    function getUpdatedAtColumn($key = 'field')
    {
        return getSystemConfig('updated_at_column.' . $key);
    }
}
if (!function_exists('getCreatedAtColumn')) {

    function getCreatedAtColumn($key = 'field')
    {
        return getSystemConfig('created_at_column.' . $key);
    }
}
if (!function_exists('getDeletedAtColumn')) {

    function getDeletedAtColumn($key = 'field')
    {
        return getSystemConfig('deleted_at_column.' . $key, '');
    }
}
if (!function_exists('getDelFlagColumn')) {

    function getDelFlagColumn($key = 'field')
    {
        return getSystemConfig('del_flag_column.' . $key);
    }
}
if (!function_exists('getCreatedByColumn')) {

    function getCreatedByColumn($key = 'field')
    {
        return getSystemConfig('created_by_column.' . $key);
    }
}
if (!function_exists('getUpdatedByColumn')) {

    function getUpdatedByColumn($key = 'field')
    {
        return getSystemConfig('updated_by_column.' . $key);
    }
}
if (!function_exists('getDeletedByColumn')) {

    function getDeletedByColumn($key = 'field')
    {
        return getSystemConfig('deleted_by_column.' . $key, getUpdatedByColumn());
    }
}
if (!function_exists('getStatusColumn')) {

    function getStatusColumn($key = 'field')
    {
        return getSystemConfig('status_column.' . $key);
    }
}
