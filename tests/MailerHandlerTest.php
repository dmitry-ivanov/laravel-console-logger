<?php

use Monolog\Handler\SwiftMailerHandler;

class MailerHandlerTest extends TestCase
{
    /** @test */
    public function it_validates_and_filters_notification_recipients()
    {
        $handler = $this->runViaObject(CommandWithInvalidNotificationRecipients::class)->mailerHandler();
        $this->assertNotInstanceOf(SwiftMailerHandler::class, $handler);
    }
}
