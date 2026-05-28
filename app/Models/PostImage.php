<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['post_id', 'image_path'])]
class PostImage extends Model
{
    use HasFactory;

    /**
     * Le post auquel appartient cette image.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
