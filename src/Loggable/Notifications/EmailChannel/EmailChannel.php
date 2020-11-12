<?php

namespace Illuminated\Console\Loggable\Notifications\EmailChannel;

use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;
use Swift_Mailer;
use Swift_Message;

trait EmailChannel
{
    /**
     * Defines whether to use email notifications or not.
     *
     * @return bool
     */
    protected function useEmailNotifications()
    {
        return true;
    }

    /**
     * Get the email channel handler.
     *
     * @return \Monolog\Handler\NativeMailerHandler|\Monolog\Handler\SwiftMailerHandler|\Monolog\Handler\DeduplicationHandler|false
     */
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

    /**
     * Get the email notifications level.
     *
     * @return int
     */
    protected function getEmailNotificationsLevel()
    {
        return Logger::NOTICE;
    }

    /**
     * Get the email notifications recipients.
     *
     * @return array
     */
    protected function getEmailNotificationsRecipients()
    {
        return [
            ['address' => null, 'name' => null],
        ];
    }

    /**
     * Get the email notifications subject.
     *
     * @return string
     */
    protected function getEmailNotificationsSubject()
    {
        $env = str_upper(app()->environment());
        $name = $this->getName();

        return "[{$env}] %level_name% in `{$name}` command";
    }

    /**
     * Get the email notifications "from".
     *
     * @return array
     */
    protected function getEmailNotificationsFrom()
    {
        return ['address' => 'no-reply@example.com', 'name' => 'ICLogger Notification'];
    }

    /**
     * Defines whether to use email notifications deduplication or not.
     *
     * @return bool
     */
    protected function useEmailNotificationsDeduplication()
    {
        return false;
    }

    /**
     * Get email notifications deduplication time in seconds.
     *
     * @return int
     */
    protected function getEmailNotificationsDeduplicationTime()
    {
        return 60;
    }

    /**
     * Filter email notifications recipients.
     *
     * @return array
     */
    private function filterEmailNotificationsRecipients()
    {
        return collect($this->getEmailNotificationsRecipients())
            ->filter(function (array $recipient) {
                return isset($recipient['address'])
                    && is_email($recipient['address']);
            })
            ->toArray();
    }
}
