<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Throwable;

class ExceptionOccurred extends Mailable
{
    public function __construct(
        public Throwable $exceptionInstance,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CollectorWWII error: '.class_basename($this->exceptionInstance),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.exception-occurred',
            with: [
                'exceptionClass' => get_class($this->exceptionInstance),
                'exceptionMessage' => $this->exceptionInstance->getMessage(),
                'file' => $this->exceptionInstance->getFile(),
                'line' => $this->exceptionInstance->getLine(),
                'trace' => $this->exceptionInstance->getTraceAsString(),
                'url' => $this->url,
            ],
        );
    }
}
