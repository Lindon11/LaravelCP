<?php

namespace App\Mail;

use App\Core\Models\EmailSetting;
use App\Core\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DynamicEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected string $renderedSubject;
    protected string $renderedHtml;
    protected ?string $renderedText;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected string $templateSlug,
        protected array $variables = []
    ) {
        // Apply email settings from database
        $settings = EmailSetting::getActive();
        if ($settings) {
            $settings->applyToConfig();
        }

        // Get and render template
        $template = EmailTemplate::findBySlug($this->templateSlug);

        if ($template) {
            $rendered = $template->render($this->variables);
            $this->renderedSubject = $rendered['subject'];
            $this->renderedHtml = $rendered['body_html'];
            $this->renderedText = $rendered['body_text'];
        } else {
            // Fallback if template not found
            $this->renderedSubject = 'Notification from ' . config('app.name');
            $this->renderedHtml = '<p>Email template not configured.</p>';
            $this->renderedText = 'Email template not configured.';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->renderedSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->renderedHtml,
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the message (for plain text fallback)
     */
    public function build()
    {
        $mail = $this->html($this->renderedHtml);

        if ($this->renderedText) {
            $mail->text(new \Illuminate\Support\HtmlString($this->renderedText));
        }

        return $mail;
    }
}
