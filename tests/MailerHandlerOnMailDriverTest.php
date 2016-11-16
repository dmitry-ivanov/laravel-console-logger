<?php

use Illuminated\Console\Log\HtmlFormatter;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Logger;

class MailerHandlerOnMailDriverTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        config(['mail.driver' => 'mail']);
    }

    /** @test */
    public function it_uses_configured_monolog_swift_mailer_handler()
    {
        $mailer = app('swift.mailer');
        $message = $mailer->createMessage();
        $message->setSubject('[TESTING] %level_name% in `command-with-notification-recipients` command');
        $message->setFrom(to_swiftmailer_emails(['address' => 'no-reply@example.com', 'name' => 'ICLogger Notification']));
        $message->setTo(to_swiftmailer_emails([
            ['address' => 'john.doe@example.com', 'name' => 'John Doe'],
            ['address' => 'jane.smith@example.com', 'name' => 'Jane Smith'],
        ]));
        $message->setContentType('text/html');
        $message->setCharset('utf-8');
        $expectedHandler = new SwiftMailerHandler($mailer, $message, Logger::NOTICE);
        $expectedHandler->setFormatter(new HtmlFormatter());

        $handler = $this->runViaObject(CommandWithNotificationRecipients::class)->mailerHandler();

        $this->assertMailerHandlersAreEqual($expectedHandler, $handler);
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

        return $dump;
    }
}
