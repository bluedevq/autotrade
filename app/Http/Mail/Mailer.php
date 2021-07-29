<?php

namespace App\Http\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;

class Mailer extends Mailable
{
    use Queueable, SerializesModels;

    public function sendmail($from, $sender, $to, $subject, $content, $cc = [], $contentHtml = '', $param = [])
    {
        Mail::send([], [], function ($message) use ($from, $sender, $to, $subject, $content, $cc, $contentHtml, $param) {
            $message->to($to)->subject($subject);
            $message->cc($cc);
            $message->from($from, $sender);
            $message->setContentType("text/plain");
            $message->setBody($content)->setCharset('utf8')->setEncoder(new \Swift_Mime_ContentEncoder_PlainContentEncoder('7bit'));
            if (!empty($contentHtml)) {
                $message->addPart($contentHtml, 'text/html');
            }
            if (!empty($param) && file_exists(Arr::get($param, 'attach')) && is_readable(Arr::get($param, 'attach'))) {
                $message->attach(Arr::get($param, 'attach'), Arr::get($param, 'attach_option'));
            }
        });

        return $this;
    }
}
