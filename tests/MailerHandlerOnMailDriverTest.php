<?php

use Monolog\Handler\SwiftMailerHandler;

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
        $handler = $this->runViaObject(CommandWithNotificationRecipients::class)->mailerHandler();

        $this->assertInstanceOf(SwiftMailerHandler::class, $handler);
    }
}
