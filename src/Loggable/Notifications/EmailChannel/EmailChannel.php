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
     */
    protected function useEmailNotifications(): bool
    {
        return true;
    }

    /**
     * Get the email channel handler.
     *
     * @noinspection PhpUnused
     */
    protected function getEmailChannelHandler(): SymfonyMailerHandler|DeduplicationHandler|false
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
     */
    protected function getEmailNotificationsLevel(): int
    {
        return Logger::NOTICE;
    }

    /**
     * Get the email notifications recipients.
     */
    protected function getEmailNotificationsRecipients(): array
    {
        return [
            ['address' => null, 'name' => null],
        ];
    }

    /**
     * Get the email notifications subject.
     */
    protected function getEmailNotificationsSubject(): string
    {
        $env = Str::upper(app()->environment());
        $name = $this->getName();

        return "[{$env}] %level_name% in `{$name}` command";
    }

    /**
     * Get the email notifications "from".
     */
    protected function getEmailNotificationsFrom(): array
    {
        return ['address' => 'no-reply@example.com', 'name' => 'ICLogger Notification'];
    }

    /**
     * Defines whether to use email notifications deduplication or not.
     */
    protected function useEmailNotificationsDeduplication(): bool
    {
        return false;
    }

    /**
     * Get email notifications deduplication time in seconds.
     */
    protected function getEmailNotificationsDeduplicationTime(): int
    {
        return 60;
    }

    /**
     * Filter email notifications recipients.
     */
    private function filterEmailNotificationsRecipients(): array
    {
        return collect($this->getEmailNotificationsRecipients())
            ->filter(function (array $recipient) {
                return isset($recipient['address'])
                    && is_email($recipient['address']);
            })
            ->toArray();
    }
}
