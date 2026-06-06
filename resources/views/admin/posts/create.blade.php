@extends('layouts.app')

@section('content')

<div class="w-full max-w-2xl mx-auto">

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-8">
        <h2 class="font-[Geist] text-2xl font-bold tracking-tight"
            style="color: var(--color-primary)">
            Nouveau post
        </h2>
        <span class="font-[JetBrains_Mono] text-[10px] tracking-widest uppercase"
              style="color: var(--color-outline)">
            Admin Console
        </span>
    </div>

    {{-- Erreur générale --}}
    @if ($errors->has('general'))
        <div class="mb-6 px-4 py-3 bg-[#93000a]/30 border border-[#ffb4ab]/30
                    text-[#ffb4ab] rounded-xl text-sm font-[JetBrains_Mono] tracking-wide">
            {{ $errors->first('general') }}
        </div>
    @endif

    {{-- Autres erreurs --}}
    @php $otherErrors = collect($errors->keys())->filter(fn($k) => $k !== 'general'); @endphp
    @if ($otherErrors->isNotEmpty())
        <ul class="mb-6 px-4 py-3 bg-[#93000a]/30 border border-[#ffb4ab]/30
                   text-[#ffb4ab] rounded-xl text-sm font-[JetBrains_Mono] space-y-1">
            @foreach ($otherErrors as $key)
                <li>{{ $errors->first($key) }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST"
          action="{{ route('admin.posts.store') }}"
          enctype="multipart/form-data"
          class="space-y-8">
        @csrf

        {{-- ── Titre (optionnel) ── --}}
        <div class="relative border-b pb-4" style="border-color: var(--color-outline-variant)">
            <input type="text"
                   id="title"
                   name="title"
                   value="{{ old('title') }}"
                   placeholder="Titre du post…"
                   class="w-full border-none focus:ring-0 focus:outline-none
                          text-2xl font-bold leading-tight p-0"
                   style="background: transparent;
                          color: var(--color-on-surface);
                          caret-color: var(--color-primary)">
        </div>

        {{-- ── Zone de texte ── --}}
        <div class="relative">
            <textarea id="content"
                      name="content"
                      rows="6"
                      placeholder="Quoi de neuf ?"
                      autofocus
                      class="w-full border-none focus:ring-0 focus:outline-none
                             text-[17px] leading-relaxed resize-none p-0"
                      style="background: transparent;
                             color: var(--color-on-surface);
                             caret-color: var(--color-primary)">{{ old('content') }}</textarea>
        </div>

        {{-- ── Zone de dépôt d'images ── --}}
        <div id="drop-zone"
             class="drag-area-dashed rounded-xl p-12 transition-all duration-300 cursor-pointer
                    flex flex-col items-center justify-center gap-4"
             style="background-color: transparent"
             onclick="document.getElementById('images').click()">

            <div class="w-16 h-16 rounded-full flex items-center justify-center
                        transition-transform duration-300"
                 style="background-color: var(--color-surface-container-high)">
                <span class="material-symbols-outlined text-3xl"
                      style="color: var(--color-primary)">cloud_upload</span>
            </div>
            <div class="text-center">
                <p class="text-sm font-medium" style="color: var(--color-on-surface)">
                    Glisser-déposer des images
                </p>
                <p class="font-[JetBrains_Mono] text-[11px] tracking-wider mt-1 uppercase"
                   style="color: var(--color-outline)">
                    PNG, JPG ou WEBP — 4 Mo max
                </p>
            </div>

            <input type="file" id="images" name="images[]" accept="image/*" multiple class="hidden">
        </div>

        {{-- Prévisualisation des images sélectionnées --}}
        <div id="preview-grid" class="hidden grid grid-cols-3 gap-3"></div>

        {{-- ── Actions ── --}}
        <div class="flex items-center justify-between pt-6 border-t border-[#454653]">
            <div class="flex gap-2">
                {{-- Bouton raccourci image --}}
                <button type="button"
                        onclick="document.getElementById('images').click()"
                        class="p-2 rounded-lg text-[#908f9e] hover:bg-[#33394c] hover:text-[#bdc2ff] transition-colors">
                    <span class="material-symbols-outlined">image</span>
                </button>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}"
                   class="font-[JetBrains_Mono] text-xs tracking-widest text-[#908f9e]
                          hover:text-[#dce1fb] transition-colors uppercase">
                    Annuler
                </a>
                <button type="submit"
                        class="gradient-btn px-8 py-3 rounded-full font-semibold text-white text-sm
                               hover:scale-105 active:scale-95 transition-all duration-300
                               flex items-center gap-2">
                    Publier
                    <span class="material-symbols-outlined text-sm">send</span>
                </button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    // Auto-resize textarea
    const textarea = document.getElementById('content');
    textarea.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });

    // Drag & drop
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('images');

    ['dragenter', 'dragover'].forEach(ev => {
        dropZone.addEventListener(ev, e => {
            e.preventDefault();
            dropZone.classList.add('drag-over', 'bg-[#818cf8]/5', 'scale-[1.02]');
        });
    });

    ['dragleave', 'drop'].forEach(ev => {
        dropZone.addEventListener(ev, e => {
            e.preventDefault();
            dropZone.classList.remove('drag-over', 'bg-[#818cf8]/5', 'scale-[1.02]');
        });
    });

    dropZone.addEventListener('drop', e => {
        const dt = e.dataTransfer;
        // Transférer les fichiers dans l'input
        const dataTransfer = new DataTransfer();
        Array.from(dt.files).forEach(f => dataTransfer.items.add(f));
        fileInput.files = dataTransfer.files;
        showPreviews(dt.files);
    });

    fileInput.addEventListener('change', () => showPreviews(fileInput.files));

    function showPreviews(files) {
        const grid = document.getElementById('preview-grid');
        grid.innerHTML = '';
        if (!files.length) { grid.classList.add('hidden'); return; }
        grid.classList.remove('hidden');

        Array.from(files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const div = document.createElement('div');
                div.className = 'aspect-square glass-card rounded-xl overflow-hidden border border-[#454653] hover:border-[#818cf8] transition-all';
                div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                grid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
</script>
@endpush
