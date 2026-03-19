<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReachedAdultAgeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Adult account verification required - ' . $this->user->name . ' ' . $this->user->surname,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reached-adult-age',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
