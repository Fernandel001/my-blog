<?php

namespace App\Http\Controllers;

use App\Models\Comment;
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

    /**
     * Supprime un commentaire — admin ou auteur uniquement.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $user = auth()->user();

        if ($user->email !== 'admin@thehackerexperiment.com' && $comment->user_id !== $user->id) {
            abort(403);
        }

        $comment->delete();

        return back();
    }

    /**
     * Modifie un commentaire — auteur uniquement, dans les 3 jours.
     */
    public function update(Request $request, Comment $comment): RedirectResponse
    {
        if (auth()->id() !== $comment->user_id) abort(403);

        if ($comment->created_at->diffInDays(now()) > 3) {
            return back()->withErrors(['edit' => 'Délai de modification dépassé.']);
        }

        $request->validate(['content' => ['required', 'string', 'max:1000']]);

        $comment->update(['content' => $request->input('content')]);

        return back();
    }
}
