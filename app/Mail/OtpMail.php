<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code) {}

    public function build(): self
    {
        return $this->subject('Votre code de connexion — The Hacker Experiment')
            ->html("
                <p>Bonjour,</p>
                <p>Voici votre code de connexion à 4 chiffres :</p>
                <h2>{$this->code}</h2>
                <p>Ce code est valable <strong>15 minutes</strong>.</p>
                <p>Si vous n'avez pas demandé ce code, ignorez cet email.</p>
                <p>— The Hacker Experiment</p>
            ");
    }
}
