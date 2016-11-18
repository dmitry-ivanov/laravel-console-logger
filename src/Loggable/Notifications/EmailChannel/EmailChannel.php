<?php

namespace Illuminated\Console\Loggable\Notifications\EmailChannel;

use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\MandrillHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\SwiftMailerHandler;

trait EmailChannel
{
    protected function useEmailNotifications()
    {
        return true;
    }

    protected function getEmailChannelHandler()
    {
        if (!$this->useEmailNotifications()) {
            return false;
        }

        $recipients = $this->normalizeEmailNotificationRecipients();
        if (empty($recipients)) {
            return false;
        }

        $subject = $this->getNotificationSubject();
        $from = $this->getNotificationFrom();
        $level = $this->getNotificationLevel();

        $driver = config('mail.driver');
        switch ($driver) {
            case 'mail':
            case 'smtp':
            case 'sendmail':
            case 'mandrill':
                $mailer = app('swift.mailer');
                $message = $mailer->createMessage();
                $message->setSubject($subject);
                $message->setFrom(to_swiftmailer_emails($from));
                $message->setTo(to_swiftmailer_emails($recipients));
                $message->setContentType('text/html');
                $message->setCharset('utf-8');

                if ($driver == 'mandrill') {
                    $mailerHandler = new MandrillHandler(config('services.mandrill.secret'), $message, $level);
                } else {
                    $mailerHandler = new SwiftMailerHandler($mailer, $message, $level);
                }
                break;

            default:
                $to = to_rfc2822_email($recipients);
                $from = to_rfc2822_email($from);
                $mailerHandler = new NativeMailerHandler($to, $subject, $from, $level);
                $mailerHandler->setContentType('text/html');
                break;
        }
        $mailerHandler->setFormatter(new MonologHtmlFormatter);

        if ($this->enableNotificationDeduplication()) {
            $time = $this->getNotificationDeduplicationTime();
            $mailerHandler = new DeduplicationHandler($mailerHandler, null, $level, $time);
        }

        return $mailerHandler;
    }

    protected function getNotificationRecipients()
    {
        return [
            ['address' => null, 'name' => null],
        ];
    }

    protected function getNotificationSubject()
    {
        $env = str_upper(app()->environment());
        $name = $this->getName();
        return "[{$env}] %level_name% in `{$name}` command";
    }

    protected function getNotificationFrom()
    {
        return ['address' => 'no-reply@example.com', 'name' => 'ICLogger Notification'];
    }

    protected function enableNotificationDeduplication()
    {
        return false;
    }

    protected function getNotificationDeduplicationTime()
    {
        return 60;
    }

    private function normalizeEmailNotificationRecipients()
    {
        $result = [];

        $recipients = $this->getNotificationRecipients();
        foreach ($recipients as $recipient) {
            if (!empty($recipient['address']) && is_email($recipient['address'])) {
                $result[] = $recipient;
            }
        }

        return $result;
    }
}
