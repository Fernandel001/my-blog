<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['email', 'username'])]
class Commenter extends Model
{
    use HasFactory;

    /**
     * Extrait automatiquement le pseudo depuis la partie locale de l'email.
     * Exemple : "jean.dupont@gmail.com" → username "jean.dupont"
     */
    public static function fromEmail(string $email): self
    {
        return self::firstOrCreate(
            ['email' => $email],
            ['username' => strstr($email, '@', before_needle: true)]
        );
    }

    /**
     * Les commentaires postés par ce visiteur.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
