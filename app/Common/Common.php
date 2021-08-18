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
if (!function_exists('getSortLink')) {
    function getSortLink($text, $sortField, $sortType = 'asc')
    {
        $currentRoute = \Illuminate\Support\Facades\Route::currentRouteName();
        $currentSortField = request()->get('sort_field');
        $currentSortType = request()->get('sort_type');
        $sortType = $currentSortField == $sortField && $currentSortType == 'asc' ? 'desc' : 'asc';
        $arrow = $sortField == $currentSortField ? ('&nbsp;<span class="fas fa-arrow-' . ($sortType == 'asc' ? 'down' : 'up') . '"></span>') : '';

        $html = '<a href="' . route($currentRoute, ['sort_field' => $sortField, 'sort_type' => $sortType]) . '" class="text-decoration-none">' . $text . $arrow . '</a>';;

        return $html;
    }
}
if (!function_exists('isMobile')) {
    function isMobile()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
            return true;
        }
        return false;
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
