<?php

namespace App\Mail;

use App\Models\VisitRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VisitRequestSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public VisitRequest $visitRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [$this->visitRequest->email],
            subject: 'New Museum Visit Request — '.$this->visitRequest->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.visit-request-submitted');
    }
}
