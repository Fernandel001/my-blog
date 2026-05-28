<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;

class LikeController extends Controller
{
    /**
     * Toggle le like de l'utilisateur connecté sur un post.
     * Attach si pas encore liké, detach sinon.
     */
    public function toggle(Post $post): RedirectResponse
    {
        $user = auth()->user();

        if ($post->likes()->where('user_id', $user->id)->exists()) {
            $post->likes()->detach($user->id);
        } else {
            $post->likes()->attach($user->id);
        }

        return redirect()->route('home');
    }
}
