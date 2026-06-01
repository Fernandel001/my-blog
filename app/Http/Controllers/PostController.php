<?php

namespace App\Http\Controllers;

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
     * Valide et enregistre un nouveau post avec upload synchrone vers Supabase.
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

        $post = Post::create([
            'content' => $request->input('content'),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $extension    = $image->getClientOriginalExtension();
                $supabasePath = 'posts/' . uniqid() . '.' . $extension;

                try {
                    $result = Storage::disk('supabase')->put(
                        $supabasePath,
                        file_get_contents($image->getRealPath())
                    );
                    error_log('Supabase upload result: ' . ($result ? 'true' : 'false') . ' path: ' . $supabasePath);
                } catch (\Exception $e) {
                    error_log('Supabase upload error: ' . $e->getMessage());
                }

                $post->images()->create([
                    'image_path' => $supabasePath,
                ]);
            }
        }

        return redirect()
            ->route('home')
            ->with('success', 'Post publié.');
    }
}
