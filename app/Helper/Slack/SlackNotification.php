<?php

namespace App\Helper\Slack;


use GuzzleHttp\Client;

/**
 * Class SlackNotification
 * @package App\Helper\Slack
 */
class SlackNotification
{
    protected $_webHook;

    public function setWebHook($webHook)
    {
        $this->_webHook = $webHook;
        return $this;
    }

    public function getWebHook()
    {
        return $this->_webHook;
    }

    public function __construct()
    {
    }

    public function send($message)
    {
        if (empty($this->getWebHook())) {
            return;
        }
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($message instanceof \Exception) {
            $message = $message->getTraceAsString();
        }

        $client = new Client();
        $client->request('post', $this->getWebHook(), [
            'body' => json_encode(['text' => $message]),
            'headers' => $headers
        ]);

    }
}
