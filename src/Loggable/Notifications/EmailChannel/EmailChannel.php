<?php

namespace Illuminated\Console\Loggable\Notifications\EmailChannel;

use Illuminate\Support\Str;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\SymfonyMailerHandler;
use Monolog\Logger;
use Symfony\Component\Mime\Email;

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
     * @return \Monolog\Handler\SymfonyMailerHandler|\Monolog\Handler\DeduplicationHandler|false
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

        $message = (new Email)
            ->subject($subject)
            ->from(...to_symfony_emails($from))
            ->to(...to_symfony_emails($recipients));

        $mailer = app('mailer')->getSymfonyTransport();
        $mailerHandler = new SymfonyMailerHandler($mailer, $message, $level);
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
        $env = Str::upper(app()->environment());
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
