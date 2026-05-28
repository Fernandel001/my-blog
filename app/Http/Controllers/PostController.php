<?php

namespace App\Http\Controllers;

use App\Jobs\UploadImageToSupabase;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Affiche la page d'accueil avec tous les posts.
     */
    public function index(): View
    {
        $posts = Post::with(['images', 'comments.user', 'likes'])
            ->withCount('likes')
            ->latest()
            ->get();

        return view('posts.index', compact('posts'));
    }

    /**
     * Affiche le formulaire de création d'un post.
     */
    public function create(): View
    {
        return view('admin.posts.create');
    }

    /**
     * Valide, crée le post, stocke les images localement
     * et dispatch un Job asynchrone pour l'upload vers Supabase.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'content'  => ['nullable', 'string'],
            'images'   => ['nullable', 'array'],
            'images.*' => ['image', 'max:4096'],
        ]);

        if (empty($request->input('content')) && ! $request->hasFile('images')) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'Le post doit contenir au moins un texte ou une image.']);
        }

        // 1. Créer le post en DB
        $post = Post::create([
            'content' => $request->input('content'),
        ]);

        // 2. Pour chaque image : stockage local temporaire + entrée DB + dispatch du job
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Stockage local temporaire (immédiat, pas de réseau)
                $tempPath = $image->store('temp', 'local');

                // Entrée en DB avec chemin temporaire (sera mis à jour par le job)
                $postImage = $post->images()->create([
                    'image_path' => $tempPath,
                ]);

                // Dispatch du job asynchrone pour l'upload vers Supabase
                UploadImageToSupabase::dispatch($postImage->id, $tempPath);
            }
        }

        return redirect()
            ->route('home')
            ->with('success', 'Post publié. Les images sont en cours d\'upload.');
    }
}
