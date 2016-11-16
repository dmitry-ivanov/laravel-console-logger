<?php

class MailerHandlerTest extends TestCase
{
    /** @test */
    public function it_sends_email_notifications_for_specified_recipients()
    {
        config(['mail.driver' => 'mail']);
        $this->runViaObject(CommandWithNotificationRecipients::class);
    }
}
