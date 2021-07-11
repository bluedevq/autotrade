<?php

namespace App\Helper;

use App\Helper\Slack\SlackNotification;
use App\Helper\Telegram\TelegramNotification;

/**
 * Class Common
 * @package App\Helper
 */
Class Common
{
    public static function getConfig($key, $default = null, $flip = false)
    {
        return config('config.' . $key, $default);
    }

    public static function getBackendDomain()
    {
        return getSystemConfig('backend_domain', '/');
    }

    public static function getBackendAlias()
    {
        return getSystemConfig('backend_alias', '/');
    }

    public static function getBackendNamespace()
    {
        return '';
    }

    public static function getFrontendDomain()
    {
        return getSystemConfig('frontend_domain', '/');
    }

    public static function getFrontendAlias()
    {
        return getSystemConfig('frontend_alias', '/');
    }

    public static function getFrontendNamespace()
    {
        return '';
    }

    public static function getApiDomain()
    {
        return getSystemConfig('api_domain', '/');
    }

    public static function getApiAlias()
    {
        return getSystemConfig('api_alias', '/');
    }

    public static function getApiNamespace()
    {
        return '';
    }

    public static function getArea()
    {
        try {
            $url = url()->current();
        } catch (\Exception $e) {
            $url = '';
        }
        switch (true) {
            case strpos($url, self::getBackendAlias()) !== false:
                return 'backend';
                break;
            case strpos($url, self::getFrontendAlias()) !== false:
                return 'frontend';
                break;
            default:
                return '';
                break;
        }
    }

    public static function sendMessageToSlack($message)
    {
        $slack = new SlackNotification();
        $slack->setWebHook(self::getConfig('slack_config.incoming_webhook'));
        $slack->send($message);
    }

    public static function sendMessageToTelegram($message)
    {
        $telegram = new TelegramNotification();
        $telegram->send($message);
    }
}
