<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Enregistre un commentaire de l'utilisateur connecté sur un post.
     */
    public function store(Request $request, Post $post): RedirectResponse
    {
        $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $post->comments()->create([
            'user_id' => auth()->id(),
            'content' => $request->input('content'),
        ]);

        return redirect()
            ->to(url()->previous() . '#comments')
            ->with('success', 'Commentaire publié.');
    }
}
