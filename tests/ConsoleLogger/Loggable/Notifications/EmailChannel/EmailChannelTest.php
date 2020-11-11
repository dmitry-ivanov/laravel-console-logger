<?php

namespace Illuminated\Console\ConsoleLogger\Tests\Loggable\Notifications\EmailChannel;

use EmailNotificationsCommand;
use EmailNotificationsDeduplicationCommand;
use EmailNotificationsInvalidRecipientsCommand;
use Illuminated\Console\ConsoleLogger\Tests\TestCase;
use Illuminated\Console\Loggable\Notifications\EmailChannel\MonologHtmlFormatter;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;

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
        $handler = $this->runArtisan(new EmailNotificationsDeduplicationCommand)->emailChannelHandler();
        $handler->flush();

        $this->assertMailerHandlersEqual($this->composeDeduplicationHandler(), $handler);
    }

    private function composeSwiftMailerHandler($name = 'email-notifications-command')
    {
        $handler = new SwiftMailerHandler(app('swift.mailer'), $this->composeMailerHandlerMessage($name), Logger::NOTICE);
        $handler->setFormatter(new MonologHtmlFormatter);
        return $handler;
    }

    private function composeDeduplicationHandler()
    {
        return new DeduplicationHandler(
            $this->composeSwiftMailerHandler('email-notifications-deduplication-command'), null, Logger::NOTICE, 60
        );
    }

    private function composeMailerHandlerMessage($name = 'email-notifications-command')
    {
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

    protected function assertMailerHandlersEqual($handler1, $handler2)
    {
        $handler1 = $this->normalizeMailerHandlerDump(get_dump($handler1));
        $handler2 = $this->normalizeMailerHandlerDump(get_dump($handler2));
        $this->assertEquals($handler1, $handler2);
    }

    private function normalizeMailerHandlerDump($dump)
    {
        $dump = preg_replace('/\{#\d*/', '{', $dump);
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
