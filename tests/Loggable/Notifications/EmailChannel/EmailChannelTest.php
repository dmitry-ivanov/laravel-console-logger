<?php

namespace Illuminated\Console\Tests\Loggable\Notifications\EmailChannel;

use Illuminated\Console\Loggable\Notifications\EmailChannel\MonologHtmlFormatter;
use Illuminated\Console\Tests\App\Console\Commands\EmailNotificationsCommand;
use Illuminated\Console\Tests\App\Console\Commands\EmailNotificationsDeduplicationCommand;
use Illuminated\Console\Tests\App\Console\Commands\EmailNotificationsInvalidRecipientsCommand;
use Illuminated\Console\Tests\TestCase;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\Handler;
use Monolog\Handler\SymfonyMailerHandler;
use Monolog\Logger;
use Symfony\Component\Mime\Email;

class EmailChannelTest extends TestCase
{
    /** @test */
    public function it_validates_and_filters_notification_recipients()
    {
        $this->artisan(EmailNotificationsInvalidRecipientsCommand::class);
        $this->assertNotInstanceOf(SymfonyMailerHandler::class, $this->emailChannelHandler());
    }

    /** @test */
    public function it_uses_configured_monolog_symfony_mailer_handler_on_mail_driver()
    {
        config(['mail.driver' => 'mail']);

        $this->artisan(EmailNotificationsCommand::class);

        $this->assertMailerHandlersEqual($this->composeSymfonyMailerHandler(), $this->emailChannelHandler());
    }

    /** @test */
    public function it_uses_configured_monolog_symfony_mailer_handler_on_smtp_driver()
    {
        config([
            'mail.driver' => 'smtp',
            'mail.host' => 'example.com',
            'mail.port' => 123,
        ]);

        $this->artisan(EmailNotificationsCommand::class);

        $this->assertMailerHandlersEqual($this->composeSymfonyMailerHandler(), $this->emailChannelHandler());
    }

    /** @test */
    public function it_uses_configured_monolog_symfony_mailer_handler_on_sendmail_driver()
    {
        config(['mail.driver' => 'sendmail']);

        $this->artisan(EmailNotificationsCommand::class);

        $this->assertMailerHandlersEqual($this->composeSymfonyMailerHandler(), $this->emailChannelHandler());
    }

    /** @test */
    public function it_uses_configured_monolog_deduplication_handler_if_deduplication_enabled()
    {
        config(['mail.driver' => 'sendmail']);

        $this->artisan(EmailNotificationsDeduplicationCommand::class);

        /** @var DeduplicationHandler $handler */
        $handler = $this->emailChannelHandler();
        $handler->flush();

        $this->assertMailerHandlersEqual($this->composeDeduplicationHandler(), $handler);
    }

    /**
     * Get the email channel handler.
     */
    private function emailChannelHandler(): AbstractHandler|false
    {
        return last(app('log.iclogger')->getHandlers());
    }

    /**
     * Compose "symfony mailer" handler.
     */
    private function composeSymfonyMailerHandler(string $command = 'email-notifications-command'): SymfonyMailerHandler
    {
        $handler = new SymfonyMailerHandler(app('mailer')->getSymfonyTransport(), $this->composeMailerHandlerMessage($command), Logger::NOTICE);

        $handler->setFormatter(new MonologHtmlFormatter);

        return $handler;
    }

    /**
     * Compose "deduplication" handler.
     */
    private function composeDeduplicationHandler(): DeduplicationHandler
    {
        return new DeduplicationHandler(
            $this->composeSymfonyMailerHandler('email-notifications-deduplication-command'), null, Logger::NOTICE, 60
        );
    }

    /**
     * Compose mailer handler message.
     */
    private function composeMailerHandlerMessage(string $command): Email
    {
        return (new Email())
            ->subject("[TESTING] %level_name% in `{$command}` command")
            ->from('ICLogger Notification <no-reply@example.com>')
            ->to('John Doe <john.doe@example.com>', 'Jane Smith <jane.smith@example.com>');
    }

    /**
     * Assert mailer handlers are equal.
     */
    protected function assertMailerHandlersEqual(Handler $handler1, Handler $handler2): void
    {
        $handler1 = $this->normalizeMailerHandlerDump(get_dump($handler1));
        $handler2 = $this->normalizeMailerHandlerDump(get_dump($handler2));
        $this->assertEquals($handler1, $handler2);
    }

    /**
     * Normalize the mailer handler dump.
     *
     * @noinspection PhpUnnecessaryLocalVariableInspection
     */
    private function normalizeMailerHandlerDump(string $dump): string
    {
        $dump = preg_replace('/{#\d*/', '{', $dump);
        $dump = preg_replace('/\+"date": ".*?\.\d*"/', '+date: "normalized"', $dump);
        $dump = preg_replace('/date: .*?\.\d*/', 'date: "normalized"', $dump);
        $dump = preg_replace('/-dateTime: DateTimeImmutable @\d*/', '-dateTime: DateTimeImmutable @normalized', $dump);
        $dump = preg_replace('/-cacheKey: ".*?"/', '-cacheKey: "normalized"', $dump);
        $dump = preg_replace('/-_timestamp: .*?\n/', '', $dump);
        $dump = preg_replace('/#initialized: .*?\n/', '', $dump);

        return $dump;
    }
}
