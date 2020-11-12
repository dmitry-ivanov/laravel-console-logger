<?php

namespace Illuminated\Console\Tests\Loggable\Notifications\EmailChannel;

use Illuminated\Console\Loggable\Notifications\EmailChannel\MonologHtmlFormatter;
use Illuminated\Console\Tests\App\Console\Commands\EmailNotificationsCommand;
use Illuminated\Console\Tests\App\Console\Commands\EmailNotificationsDeduplicationCommand;
use Illuminated\Console\Tests\App\Console\Commands\EmailNotificationsInvalidRecipientsCommand;
use Illuminated\Console\Tests\TestCase;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;
use Swift_Message;

class EmailChannelTest extends TestCase
{
    /** @test */
    public function it_validates_and_filters_notification_recipients()
    {
        $handler = $this->runArtisan(new EmailNotificationsInvalidRecipientsCommand)->emailChannelHandler();
        $this->assertNotInstanceOf(SwiftMailerHandler::class, $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_swift_mailer_handler_on_mail_driver()
    {
        config(['mail.driver' => 'mail']);

        $handler = $this->runArtisan(new EmailNotificationsCommand)->emailChannelHandler();

        $this->assertMailerHandlersEqual($this->composeSwiftMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_swift_mailer_handler_on_smtp_driver()
    {
        config(['mail.driver' => 'smtp']);

        $handler = $this->runArtisan(new EmailNotificationsCommand)->emailChannelHandler();

        $this->assertMailerHandlersEqual($this->composeSwiftMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_swift_mailer_handler_on_sendmail_driver()
    {
        config(['mail.driver' => 'sendmail']);

        $handler = $this->runArtisan(new EmailNotificationsCommand)->emailChannelHandler();

        $this->assertMailerHandlersEqual($this->composeSwiftMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_deduplication_handler_if_deduplication_enabled()
    {
        config(['mail.driver' => 'sendmail']);

        /** @var \Monolog\Handler\DeduplicationHandler $handler */
        $handler = $this->runArtisan(new EmailNotificationsDeduplicationCommand)->emailChannelHandler();
        $handler->flush();

        $this->assertMailerHandlersEqual($this->composeDeduplicationHandler(), $handler);
    }

    /**
     * Compose "swift mailer" handler.
     *
     * @param string $name
     * @return \Monolog\Handler\SwiftMailerHandler
     */
    private function composeSwiftMailerHandler($name = 'email-notifications-command')
    {
        $handler = new SwiftMailerHandler(app('swift.mailer'), $this->composeMailerHandlerMessage($name), Logger::NOTICE);

        $handler->setFormatter(new MonologHtmlFormatter);

        return $handler;
    }

    /**
     * Compose "deduplication" handler.
     *
     * @return \Monolog\Handler\DeduplicationHandler
     */
    private function composeDeduplicationHandler()
    {
        return new DeduplicationHandler(
            $this->composeSwiftMailerHandler('email-notifications-deduplication-command'), null, Logger::NOTICE, 60
        );
    }

    /**
     * Compose mailer handler message.
     *
     * @param string $name
     * @return \Swift_Message
     */
    private function composeMailerHandlerMessage($name = 'email-notifications-command')
    {
        /** @var Swift_Message $message */
        $message = app('swift.mailer')->createMessage();
        $message->setSubject("[TESTING] %level_name% in `{$name}` command");
        $message->setFrom(to_swiftmailer_emails([
            'address' => 'no-reply@example.com',
            'name' => 'ICLogger Notification',
        ]));
        $message->setTo(to_swiftmailer_emails([
            ['address' => 'john.doe@example.com', 'name' => 'John Doe'],
            ['address' => 'jane.smith@example.com', 'name' => 'Jane Smith'],
        ]));
        $message->setContentType('text/html');
        $message->setCharset('utf-8');

        return $message;
    }

    /**
     * Assert mailer handlers are equal.
     *
     * @param mixed $handler1
     * @param mixed $handler2
     * @return void
     */
    protected function assertMailerHandlersEqual($handler1, $handler2)
    {
        $handler1 = $this->normalizeMailerHandlerDump(get_dump($handler1));
        $handler2 = $this->normalizeMailerHandlerDump(get_dump($handler2));
        $this->assertEquals($handler1, $handler2);
    }

    /**
     * Normalize the mailer handler dump.
     *
     * @param string $dump
     * @return string
     */
    private function normalizeMailerHandlerDump(string $dump)
    {
        $dump = preg_replace('/{#\d*/', '{', $dump);
        $dump = preg_replace('/".*?@swift.generated"/', '"normalized"', $dump);
        $dump = preg_replace('/\+"date": ".*?\.\d*"/', '+date: "normalized"', $dump);
        $dump = preg_replace('/date: .*?\.\d*/', 'date: "normalized"', $dump);
        $dump = preg_replace('/-dateTime: DateTimeImmutable @\d*/', '-dateTime: DateTimeImmutable @normalized', $dump);
        $dump = preg_replace('/-cacheKey: ".*?"/', '-cacheKey: "normalized"', $dump);
        $dump = preg_replace('/-_timestamp: .*?\n/', '', $dump);
        $dump = preg_replace('/#initialized: .*?\n/', '', $dump);

        return $dump;
    }
}
