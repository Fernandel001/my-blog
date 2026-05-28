<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['content'])]
class Post extends Model
{
    use HasFactory;

    /**
     * Les images associées à ce post (affichées en carrousel).
     */
    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class);
    }

    /**
     * Les commentaires associés à ce post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Les utilisateurs qui ont liké ce post.
     */
    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_user_like')
                    ->withPivot('created_at');
    }
}
