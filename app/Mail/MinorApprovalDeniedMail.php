<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MinorApprovalDeniedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $reason)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Minor Registration Documents Update - ' . $this->user->name . ' ' . $this->user->surname,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.minor-approval-denied',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
