@extends('layouts.app')

@section('content')

    {{-- Flash success --}}
    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-[#191f31] border border-[#818cf8]/40 text-[#bdc2ff]
                    rounded-xl text-sm font-[JetBrains_Mono] tracking-wide">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- ── Liste des posts ── --}}
    @forelse ($posts as $post)
        <article id="post-{{ $post->id }}"
                 class="post-card-hover mb-8 bg-[#191f31] border border-[#454653] rounded-xl
                        overflow-hidden transition-all duration-300">

            {{-- ── En-tête du post ── --}}
            <div class="flex items-center justify-between px-6 pt-5 pb-3">
                <div class="flex items-center gap-3">
                    {{-- Avatar initiale admin --}}
                    <div class="w-8 h-8 rounded-full bg-[#818cf8] flex items-center justify-center
                                text-[#000767] text-xs font-bold font-[JetBrains_Mono]">
                        A
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-[#dce1fb]">Admin</span>
                        <span class="text-[11px] font-[JetBrains_Mono] tracking-widest text-[#908f9e] uppercase">
                            {{ $post->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- ── Texte ── --}}
            @if ($post->content)
                <div class="px-6 pb-4 text-[#dce1fb] text-[17px] leading-relaxed whitespace-pre-line">
                    {{ $post->content }}
                </div>
            @endif

            {{-- ── Médias ── --}}
            @php
                // URL Supabase construite statiquement — pas d'appel réseau
                $supabaseBase = 'https://qrpjdarfpuabkvvbhynz.supabase.co/storage/v1/object/public/blog-images';
                $imageUrl = fn($path) => str_starts_with($path, 'temp/')
                    ? null  // upload en cours
                    : $supabaseBase . '/' . ltrim($path, '/');
            @endphp

            @if ($post->images->count() === 1)
                <div class="mx-6 mb-4 rounded-xl overflow-hidden border border-[#454653]">
                    @if ($imageUrl($post->images->first()->image_path) === null)
                        <div class="w-full h-32 bg-[#151b2d] flex items-center justify-center gap-2 text-[#908f9e]">
                            <span class="material-symbols-outlined animate-spin text-xl">progress_activity</span>
                            <span class="font-[JetBrains_Mono] text-xs tracking-widest uppercase">Upload en cours…</span>
                        </div>
                    @else
                        <img src="{{ $imageUrl($post->images->first()->image_path) }}"
                             alt="Image du post"
                             class="w-full max-h-[480px] object-cover">
                    @endif
                </div>

            @elseif ($post->images->count() > 1)
                <div class="relative mx-6 mb-4">
                    <div class="carousel flex overflow-x-auto snap-x snap-mandatory scrollbar-none
                                rounded-xl border border-[#454653] bg-[#151b2d]">
                        @foreach ($post->images as $image)
                            <div class="snap-center shrink-0 w-full">
                                @if ($imageUrl($image->image_path) === null)
                                    <div class="w-full h-32 flex items-center justify-center gap-2 text-[#908f9e]">
                                        <span class="material-symbols-outlined animate-spin text-xl">progress_activity</span>
                                        <span class="font-[JetBrains_Mono] text-xs tracking-widest uppercase">Upload en cours…</span>
                                    </div>
                                @else
                                    <img src="{{ $imageUrl($image->image_path) }}"
                                         alt="Image {{ $loop->iteration }}"
                                         class="w-full max-h-[480px] object-cover">
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="carousel-dots absolute bottom-3 left-1/2 -translate-x-1/2
                                flex gap-1.5 px-3 py-1.5 rounded-full
                                bg-[#070d1f]/60 backdrop-blur-sm border border-white/10">
                        @foreach ($post->images as $image)
                            <div class="dot w-1.5 h-1.5 rounded-full transition-colors duration-200
                                        {{ $loop->first ? 'bg-[#bdc2ff]' : 'bg-[#908f9e]/40' }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ── Séparateur + section commentaires ── --}}
            <div class="border-t border-[#454653]">

                {{-- Barre d'interactions : Like --}}
                <div class="flex items-center gap-6 px-6 py-3 border-b border-[#454653]/50">
                    @auth
                        @php $userLiked = $post->likes->contains(auth()->id()); @endphp
                        <form method="POST" action="{{ route('posts.like', $post) }}">
                            @csrf
                            <button type="submit"
                                    class="flex items-center gap-2 transition-colors
                                           {{ $userLiked ? 'text-[#818cf8]' : 'text-[#908f9e] hover:text-[#818cf8]' }}">
                                <span class="material-symbols-outlined text-xl"
                                      style="font-variation-settings: 'FILL' {{ $userLiked ? 1 : 0 }}, 'wght' 400, 'GRAD' 0, 'opsz' 24;">
                                    favorite
                                </span>
                                <span class="font-[JetBrains_Mono] text-[11px] tracking-widest uppercase">
                                    {{ $post->likes_count }}
                                </span>
                            </button>
                        </form>
                    @else
                        {{-- Visiteur non connecté : compteur affiché, bouton désactivé --}}
                        <div class="flex items-center gap-2 text-[#454653] cursor-default">
                            <span class="material-symbols-outlined text-xl">favorite</span>
                            <span class="font-[JetBrains_Mono] text-[11px] tracking-widest uppercase">
                                {{ $post->likes_count }}
                            </span>
                        </div>
                    @endauth
                </div>

                {{-- Compteur --}}
                <div class="px-6 pt-4 pb-2">
                    <span class="text-[11px] font-[JetBrains_Mono] tracking-widest text-[#908f9e] uppercase">
                        {{ $post->comments->count() }}
                        {{ $post->comments->count() === 1 ? 'commentaire' : 'commentaires' }}
                    </span>
                </div>

                {{-- Liste des commentaires --}}
                @if ($post->comments->count() > 0)
                    <div class="px-6 pb-4 space-y-4">
                        @foreach ($post->comments as $comment)
                            <div class="flex gap-3">
                                <div class="shrink-0 w-8 h-8 rounded-full bg-[#23293c] flex items-center
                                            justify-center text-xs font-bold font-[JetBrains_Mono]
                                            text-[#bdc2ff] uppercase">
                                    {{ mb_substr($comment->user->name, 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-sm font-semibold text-[#dce1fb]">
                                            {{ $comment->user->name }}
                                        </span>
                                        <span class="text-[10px] font-[JetBrains_Mono] tracking-wider text-[#908f9e] uppercase">
                                            {{ $comment->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-sm text-[#c6c5d5] leading-relaxed">
                                        {{ $comment->content }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Formulaire commentaire — visible uniquement si connecté --}}
                @auth
                    <div class="bg-[#151b2d]/50 border-t border-[#454653]/50 px-6 py-4">

                        @if ($errors->any() && old('post_id') == $post->id)
                            <ul class="mb-3 text-xs text-[#ffb4ab] font-[JetBrains_Mono] space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <form method="POST"
                              action="{{ route('posts.comments.store', $post) }}"
                              class="grid grid-cols-1 gap-3">
                            @csrf
                            <input type="hidden" name="post_id" value="{{ $post->id }}">

                            <textarea name="content"
                                      rows="2"
                                      placeholder="Ajouter un commentaire…"
                                      required
                                      class="w-full bg-[#0c1324] border border-[#454653] rounded-xl
                                             px-4 py-2.5 text-sm text-[#dce1fb] placeholder-[#908f9e]
                                             focus:outline-none focus:border-[#818cf8] focus:ring-1
                                             focus:ring-[#818cf8]/50 transition-all resize-none">{{ old('content') }}</textarea>

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="gradient-btn px-6 py-2 rounded-full text-white text-xs
                                               font-[JetBrains_Mono] tracking-widest uppercase
                                               hover:scale-105 active:scale-95 transition-all duration-200">
                                    Publier
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    {{-- Invitation à se connecter --}}
                    <div class="bg-[#151b2d]/50 border-t border-[#454653]/50 px-6 py-4">
                        <p class="text-xs font-[JetBrains_Mono] tracking-wider text-[#908f9e] uppercase">
                            <a href="{{ route('login') }}" class="text-[#bdc2ff] hover:underline underline-offset-4">
                                Connectez-vous
                            </a>
                            pour laisser un commentaire.
                        </p>
                    </div>
                @endauth

            </div>
        </article>

    @empty
        <div class="text-center py-24 text-[#908f9e]">
            <span class="material-symbols-outlined text-5xl text-[#454653] block mb-4">article</span>
            <p class="font-[JetBrains_Mono] text-sm tracking-widest uppercase">Aucun article pour l'instant.</p>
            @auth
                <a href="{{ route('admin.posts.create') }}"
                   class="mt-6 inline-flex items-center gap-2 gradient-btn px-6 py-2.5 rounded-full
                          text-white text-sm font-medium hover:scale-105 transition-all">
                    <span class="material-symbols-outlined text-sm">add_circle</span>
                    Créer le premier post
                </a>
            @endauth
        </div>
    @endforelse

@endsection

@push('scripts')
<script>
    // Mise à jour des dots au scroll du carrousel
    document.querySelectorAll('.carousel').forEach(carousel => {
        const dots = carousel.closest('.relative').querySelectorAll('.dot');
        carousel.addEventListener('scroll', () => {
            const index = Math.round(carousel.scrollLeft / carousel.offsetWidth);
            dots.forEach((dot, i) => {
                dot.classList.toggle('bg-[#bdc2ff]', i === index);
                dot.classList.toggle('bg-[#908f9e]/40', i !== index);
            });
        });
    });
</script>
@endpush
