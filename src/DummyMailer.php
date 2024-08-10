<?php

namespace Sunnysideup\EmailTest;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

class DummyMailer implements MailerInterface
{
    /**
     * @throws TransportExceptionInterface
     */
    public function send(RawMessage $message, ?Envelope $envelope = null): void
    {
        // do nothing
    }

}
