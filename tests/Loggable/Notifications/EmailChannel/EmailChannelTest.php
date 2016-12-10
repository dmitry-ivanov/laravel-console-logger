<?php

use Illuminated\Console\Loggable\Notifications\EmailChannel\MonologHtmlFormatter;
use Illuminated\Testing\InteractsWithConsole;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\MandrillHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;

class EmailChannelTest extends TestCase
{
    use InteractsWithConsole;

    /** @test */
    public function it_validates_and_filters_notification_recipients()
    {
        $handler = $this->runConsoleCommand(EmailNotificationsInvalidRecipientsCommand::class)->mailerHandler();
        $this->assertNotInstanceOf(SwiftMailerHandler::class, $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_swift_mailer_handler_on_mail_driver()
    {
        config(['mail.driver' => 'mail']);
        $handler = $this->runConsoleCommand(EmailNotificationsCommand::class)->mailerHandler();

        $this->assertMailerHandlersEqual($this->composeSwiftMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_swift_mailer_handler_on_smtp_driver()
    {
        config(['mail.driver' => 'smtp']);
        $handler = $this->runConsoleCommand(EmailNotificationsCommand::class)->mailerHandler();

        $this->assertMailerHandlersEqual($this->composeSwiftMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_swift_mailer_handler_on_sendmail_driver()
    {
        config(['mail.driver' => 'sendmail']);
        $handler = $this->runConsoleCommand(EmailNotificationsCommand::class)->mailerHandler();

        $this->assertMailerHandlersEqual($this->composeSwiftMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_mandrill_mailer_handler_on_mandrill_driver()
    {
        config(['mail.driver' => 'mandrill', 'services.mandrill.secret' => 'secret']);
        $handler = $this->runConsoleCommand(EmailNotificationsCommand::class)->mailerHandler();

        $this->assertMailerHandlersEqual($this->composeMandrillMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_native_mailer_handler_on_other_drivers()
    {
        config(['mail.driver' => 'any-other']);
        $handler = $this->runConsoleCommand(EmailNotificationsCommand::class)->mailerHandler();

        $this->assertMailerHandlersEqual($this->composeNativeMailerHandler(), $handler);
    }

    /** @test */
    public function it_uses_configured_monolog_deduplication_handler_if_deduplication_enabled()
    {
        config(['mail.driver' => 'any-other']);
        $handler = $this->runConsoleCommand(EmailNotificationsDeduplicationCommand::class)->mailerHandler();
        $handler->flush();

        $this->assertMailerHandlersEqual($this->composeDeduplicationHandler(), $handler);
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

    private function composeNativeMailerHandler($name = 'email-notifications-command')
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
            $this->composeNativeMailerHandler('email-notifications-deduplication-command'), null, Logger::NOTICE, 60
        );
    }

    private function composeMailerHandlerMessage()
    {
        $message = app('swift.mailer')->createMessage();
        $message->setSubject('[TESTING] %level_name% in `email-notifications-command` command');
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
        $dump = preg_replace('/-_cacheKey: ".*?"/', '-_cacheKey: "normalized"', $dump);
        $dump = preg_replace('/-_timestamp: .*?\n/', '', $dump);
        $dump = preg_replace('/#initialized: .*?\n/', '', $dump);

        return $dump;
    }
}
