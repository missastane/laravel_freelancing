<?php

namespace App\Http\Services\Message\Email;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailViewProvider extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    protected $files;
    protected $viewName;

    public function __construct(
        $details,
        $subject,
        $from,
        $files = null,
        $viewName = 'emails.send-otp'
    ) {
        $this->details = $details;
        $this->subject = $subject;
        $this->from = $from;
        $this->files = $files;
        $this->viewName = $viewName;
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->view($this->viewName);
    }

    public function attachments()
    {
        $files = [];

        if ($this->files) {
            foreach ($this->files as $file) {
                if (file_exists(public_path($file))) {
                    array_push($files, public_path($file));
                } else {
                    array_push($files, storage_path($file));
                }
            }
        }

        return $files;
    }
}
