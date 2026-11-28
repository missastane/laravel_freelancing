<?php

namespace App\Http\Services\Message\Email;

use App\Http\InterFaces\MessageInterface;
use Illuminate\Support\Facades\Mail;

class EmailService implements MessageInterface
{
    private $details;
    private $subject;
    private $files;
    private $locale;
    private $subjectTranslation;
    private $subjectReplacements = [];
    private $view = 'emails.send-otp';
    private $from = [];

    private $to;

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function setLocale(?string $locale)
    {
        $this->locale = $locale ?: config('app.locale');
    }

    public function setTranslatedSubject(string $key, array $replace = [])
    {
        $this->subjectTranslation = $key;
        $this->subjectReplacements = $replace;
    }

    public function setView(string $view)
    {
        $this->view = $view;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom($address, $name)
    {
        $this->from = [
            [
                'address' => $address,
                'name' => $name
            ]
        ];
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setTo($to)
    {
        $this->to = $to;
    }

    public function fire()
    {
        $locale = $this->locale ?: config('app.locale');

        $subject = $this->subjectTranslation
            ? __($this->subjectTranslation, $this->subjectReplacements, $locale)
            : $this->subject;

        Mail::to($this->to)
            ->locale($locale)
            ->send(
                new MailViewProvider(
                    $this->details,
                    $subject,
                    $this->from,
                    $this->files,
                    $this->view
                )
            );

        return true;
    }
}
