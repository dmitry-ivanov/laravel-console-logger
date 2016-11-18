<?php

use Illuminated\Console\Loggable\Notifications\EmailChannel\MonologHtmlFormatter;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\MandrillHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;

class MailerHandlerTest extends TestCase
{
    /** @test */
    public function it_validates_and_filters_notification_recipients()
    {
        $handler = $this->runViaObject(CommandWithInvalidNotificationRecipients::class)->mailerHandler();
        $this->assertNotInstanceOf(SwiftMailerHandler::class, $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_swift_mailer_handler_on_mail_driver()
    {
        config(['mail.driver' => 'mail']);
        $handler = $this->runViaObject(CommandWithNotificationRecipients::class)->mailerHandler();

        $this->assertMailerHandlersAreEqual($this->composeSwiftMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_swift_mailer_handler_on_smtp_driver()
    {
        config(['mail.driver' => 'smtp']);
        $handler = $this->runViaObject(CommandWithNotificationRecipients::class)->mailerHandler();

        $this->assertMailerHandlersAreEqual($this->composeSwiftMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_swift_mailer_handler_on_sendmail_driver()
    {
        config(['mail.driver' => 'sendmail']);
        $handler = $this->runViaObject(CommandWithNotificationRecipients::class)->mailerHandler();

        $this->assertMailerHandlersAreEqual($this->composeSwiftMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_mandrill_mailer_handler_on_mandrill_driver()
    {
        config(['mail.driver' => 'mandrill', 'services.mandrill.secret' => 'secret']);
        $handler = $this->runViaObject(CommandWithNotificationRecipients::class)->mailerHandler();

        $this->assertMailerHandlersAreEqual($this->composeMandrillMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_native_mailer_handler_on_other_drivers()
    {
        config(['mail.driver' => 'any-other']);
        $handler = $this->runViaObject(CommandWithNotificationRecipients::class)->mailerHandler();

        $this->assertMailerHandlersAreEqual($this->composeNativeMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_deduplication_handler_if_deduplication_enabled()
    {
        config(['mail.driver' => 'any-other']);
        $handler = $this->runViaObject(CommandWithNotificationDeduplication::class)->mailerHandler();
        $handler->flush();

        $this->assertMailerHandlersAreEqual($this->composeDeduplicationHandler(), $handler);
    }

    private function composeSwiftMailerHandler()
    {
        $handler = new SwiftMailerHandler(app('swift.mailer'), $this->composeMailerHandlerMessage(), Logger::NOTICE);
        $handler->setFormatter(new MonologHtmlFormatter);
        return $handler;
    }

    private function composeMandrillMailerHandler()
    {
        $handler = new MandrillHandler(
            config('services.mandrill.secret'), $this->composeMailerHandlerMessage(), Logger::NOTICE
        );
        $handler->setFormatter(new MonologHtmlFormatter);
        return $handler;
    }

    private function composeNativeMailerHandler($name = 'command-with-notification-recipients')
    {
        $handler = new NativeMailerHandler(
            to_rfc2822_email([
                ['address' => 'john.doe@example.com', 'name' => 'John Doe'],
                ['address' => 'jane.smith@example.com', 'name' => 'Jane Smith'],
            ]),
            "[TESTING] %level_name% in `{$name}` command",
            to_rfc2822_email([
                'address' => 'no-reply@example.com',
                'name' => 'ICLogger Notification',
            ]),
            Logger::NOTICE
        );
        $handler->setContentType('text/html');
        $handler->setFormatter(new MonologHtmlFormatter);

        return $handler;
    }

    private function composeDeduplicationHandler()
    {
        return new DeduplicationHandler(
            $this->composeNativeMailerHandler('command-with-notification-deduplication'), null, Logger::NOTICE, 60
        );
    }

    private function composeMailerHandlerMessage()
    {
        $message = app('swift.mailer')->createMessage();
        $message->setSubject('[TESTING] %level_name% in `command-with-notification-recipients` command');
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

    protected function assertMailerHandlersAreEqual($handler1, $handler2)
    {
        $handler1 = $this->normalizeMailerHandlerDump(get_dump($handler1));
        $handler2 = $this->normalizeMailerHandlerDump(get_dump($handler2));
        $this->assertEquals($handler1, $handler2);
    }

    private function normalizeMailerHandlerDump($dump)
    {
        $dump = preg_replace('/\{#\d*/', '{', $dump);
        $dump = preg_replace('/".*?@swift.generated"/', '"normalized"', $dump);
        $dump = preg_replace('/-_cacheKey: ".*?"/', '-_cacheKey: "normalized"', $dump);
        $dump = preg_replace('/-_timestamp: .*?\n/', '', $dump);
        $dump = preg_replace('/#initialized: .*?\n/', '', $dump);

        return $dump;
    }
}
