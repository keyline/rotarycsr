<?php

namespace Tests\Feature\Services;

use App\Mail\TransactionalMessage;
use App\Services\BrevoMailer;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BrevoMailerTest extends TestCase
{
    public function test_sends_transactional_messages_through_the_default_laravel_mailer(): void
    {
        Mail::fake();

        $sent = app(BrevoMailer::class)->send(
            'recipient@example.com',
            'Recipient Name',
            'Message subject',
            '<p>Message body</p>',
        );

        $this->assertTrue($sent);
        Mail::assertSent(TransactionalMessage::class, function (TransactionalMessage $message): bool {
            return $message->hasTo('recipient@example.com', 'Recipient Name')
                && $message->messageSubject === 'Message subject'
                && $message->htmlContent === '<p>Message body</p>';
        });
    }

    public function test_transactional_email_renders_the_shared_branding_and_message(): void
    {
        $message = new TransactionalMessage('Application update', '<p>Message body</p>');

        $message->assertSeeInHtml('https://rotarycsr3291.com/images/logo.png');
        $message->assertSeeInHtml('Application update');
        $message->assertSeeInHtml('Message body');
    }
}
