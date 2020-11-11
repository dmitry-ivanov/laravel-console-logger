<?php

namespace Illuminated\Console\Loggable\Notifications\EmailChannel;

use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;
use Swift_Mailer;
use Swift_Message;

trait EmailChannel
{
    protected function useEmailNotifications()
    {
        return true;
    }

    protected function getEmailChannelHandler()
    {
        $recipients = $this->filterEmailNotificationsRecipients();
        if (!$this->useEmailNotifications() || empty($recipients)) {
            return false;
        }

        $subject = $this->getEmailNotificationsSubject();
        $from = $this->getEmailNotificationsFrom();
        $level = $this->getEmailNotificationsLevel();

        /** @var Swift_Mailer $mailer */
        $mailer = app('mailer')->getSwiftMailer();

        /** @var Swift_Message $message */
        $message = $mailer->createMessage();
        $message->setSubject($subject);
        $message->setFrom(to_swiftmailer_emails($from));
        $message->setTo(to_swiftmailer_emails($recipients));
        $message->setContentType('text/html');
        $message->setCharset('utf-8');

        $mailerHandler = new SwiftMailerHandler($mailer, $message, $level);
        $mailerHandler->setFormatter(new MonologHtmlFormatter);

        if ($this->useEmailNotificationsDeduplication()) {
            $time = $this->getEmailNotificationsDeduplicationTime();
            $mailerHandler = new DeduplicationHandler($mailerHandler, null, $level, $time);
        }

        return $mailerHandler;
    }

    protected function getEmailNotificationsLevel()
    {
        return Logger::NOTICE;
    }

    protected function getEmailNotificationsRecipients()
    {
        return [
            ['address' => null, 'name' => null],
        ];
    }

    protected function getEmailNotificationsSubject()
    {
        $env = str_upper(app()->environment());
        $name = $this->getName();
        return "[{$env}] %level_name% in `{$name}` command";
    }

    protected function getEmailNotificationsFrom()
    {
        return ['address' => 'no-reply@example.com', 'name' => 'ICLogger Notification'];
    }

    protected function useEmailNotificationsDeduplication()
    {
        return false;
    }

    protected function getEmailNotificationsDeduplicationTime()
    {
        return 60;
    }

    private function filterEmailNotificationsRecipients()
    {
        $result = [];

        $recipients = $this->getEmailNotificationsRecipients();
        foreach ($recipients as $recipient) {
            if (!empty($recipient['address']) && is_email($recipient['address'])) {
                $result[] = $recipient;
            }
        }

        return $result;
    }
}
