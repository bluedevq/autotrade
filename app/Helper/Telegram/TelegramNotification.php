<?php

namespace App\Helper\Telegram;


use App\Helper\Common;
use GuzzleHttp\Client;

/**
 * Class TelegramNotification
 * @package App\Helper\Telegram
 */
class TelegramNotification
{
    public function __construct()
    {
    }

    /**
     * @param $message
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send($message)
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($message instanceof \Exception) {
            $message = $message->getTraceAsString();
        }

        $client = new Client();
        $client->request('post', Common::getConfig('telegram_config.api_url'), [
            'body' => json_encode([
                'api_key' => Common::getConfig('telegram_config.api_key'),
                '@type' => Common::getConfig('telegram_config.send_message'),
                'chat_id' => Common::getConfig('telegram_config.send_message_chat_id'),
                'disable_notification' => true,
                'input_message_content' => [
                    '@type' => 'inputMessageText',
                    'disable_web_page_preview' => false,
                    'text' => [
                        '@type' => 'formattedText',
                        'text' => $message
                    ],
                ],
            ]),
            'headers' => $headers
        ]);

    }
}
