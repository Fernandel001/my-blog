<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Mon Blog') }}</title>

    {{-- Fonts chargées localement via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0c1324] text-[#dce1fb] font-sans min-h-screen">

    {{-- ══════════════════════════════════════════
         HEADER — hamburger menu
    ══════════════════════════════════════════ --}}
    <header class="fixed top-0 w-full z-50 bg-[#0c1324]/80 backdrop-blur-md border-b border-[#454653]">
        <div class="flex justify-between items-center h-16 px-6 max-w-5xl mx-auto">

            {{-- Logo --}}
            <a href="{{ route('home') }}"
               class="font-[Geist] text-2xl font-bold tracking-tighter text-[#bdc2ff]">
                {{ config('app.name', 'Mon Blog') }}
            </a>

            {{-- Hamburger button --}}
            <button id="menu-btn"
                    aria-label="Menu"
                    aria-expanded="false"
                    aria-controls="drawer"
                    class="flex flex-col justify-center items-center w-10 h-10 gap-1.5 rounded-lg
                           hover:bg-[#191f31] transition-colors">
                <span class="block w-5 h-0.5 bg-[#c6c5d5] transition-all duration-300" id="bar1"></span>
                <span class="block w-5 h-0.5 bg-[#c6c5d5] transition-all duration-300" id="bar2"></span>
                <span class="block w-5 h-0.5 bg-[#c6c5d5] transition-all duration-300" id="bar3"></span>
            </button>
        </div>
    </header>

    {{-- ══════════════════════════════════════════
         DRAWER — slide-in depuis la droite
    ══════════════════════════════════════════ --}}
    {{-- Overlay --}}
    <div id="overlay"
         class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
    </div>

    {{-- Panneau --}}
    <nav id="drawer"
         role="dialog"
         aria-label="Menu de navigation"
         class="fixed top-0 right-0 z-50 h-full w-64 bg-[#191f31] border-l border-[#454653]
                translate-x-full transition-transform duration-300 flex flex-col py-8 px-6">

        {{-- Bouton fermer --}}
        <button id="close-btn"
                aria-label="Fermer le menu"
                class="self-end mb-8 text-[#908f9e] hover:text-[#bdc2ff] transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>

        {{-- Liens selon l'état de connexion --}}
        <div class="flex flex-col gap-2 flex-1">
            <a href="{{ route('home') }}"
               class="flex items-center gap-3 py-3 px-4 rounded-lg text-[#c6c5d5]
                      hover:bg-[#33394c] hover:text-[#bdc2ff] transition-colors font-medium">
                <span class="material-symbols-outlined">dynamic_feed</span>
                <span>Feed</span>
            </a>

            @auth
                <a href="{{ route('admin.posts.create') }}"
                   class="flex items-center gap-3 py-3 px-4 rounded-lg text-[#c6c5d5]
                          hover:bg-[#33394c] hover:text-[#bdc2ff] transition-colors font-medium">
                    <span class="material-symbols-outlined">add_circle</span>
                    <span>Nouveau post</span>
                </a>
            @endauth
        </div>

        {{-- Bas du drawer --}}
        <div class="border-t border-[#454653] pt-6">
            @auth
                {{-- Username connecté --}}
                <div class="flex items-center gap-2 px-4 mb-4">
                    <div class="w-7 h-7 rounded-full bg-[#818cf8] flex items-center justify-center
                                text-[#000767] text-xs font-bold font-[JetBrains_Mono] uppercase">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <span class="text-sm text-[#dce1fb] font-medium truncate">
                        {{ auth()->user()->name }}
                    </span>
                </div>

                @if(auth()->user()->name === 'Fernandel001')
                    <a href="{{ route('admin.posts.create') }}"
                       class="flex items-center gap-3 py-3 px-4 rounded-lg text-[#c6c5d5]
                              hover:bg-[#33394c] hover:text-[#bdc2ff] transition-colors font-medium mb-1">
                        <span class="material-symbols-outlined">add_circle</span>
                        <span>Nouveau post</span>
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 py-3 px-4 w-full rounded-lg
                                   text-[#908f9e] hover:text-[#ffb4ab] transition-colors">
                        <span class="material-symbols-outlined">logout</span>
                        <span class="font-[JetBrains_Mono] text-xs tracking-widest uppercase">Déconnexion</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="flex items-center gap-3 py-3 px-4 rounded-lg text-[#908f9e]
                          hover:text-[#bdc2ff] transition-colors">
                    <span class="material-symbols-outlined">login</span>
                    <span class="font-[JetBrains_Mono] text-xs tracking-widest uppercase">Connexion</span>
                </a>
            @endauth
        </div>
    </nav>

    {{-- ══════════════════════════════════════════
         CONTENU PRINCIPAL
    ══════════════════════════════════════════ --}}
    <main class="max-w-2xl mx-auto px-4 pt-24 pb-16">
        @yield('content')
    </main>

    {{-- ══════════════════════════════════════════
         SCRIPT — hamburger + drawer
    ══════════════════════════════════════════ --}}
    <script>
        const btn     = document.getElementById('menu-btn');
        const drawer  = document.getElementById('drawer');
        const overlay = document.getElementById('overlay');
        const closeBtn = document.getElementById('close-btn');
        const bar1 = document.getElementById('bar1');
        const bar2 = document.getElementById('bar2');
        const bar3 = document.getElementById('bar3');

        function openMenu() {
            drawer.classList.remove('translate-x-full');
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            btn.setAttribute('aria-expanded', 'true');
            // Animate to X
            bar1.style.transform = 'translateY(8px) rotate(45deg)';
            bar2.style.opacity   = '0';
            bar3.style.transform = 'translateY(-8px) rotate(-45deg)';
        }

        function closeMenu() {
            drawer.classList.add('translate-x-full');
            overlay.classList.add('opacity-0', 'pointer-events-none');
            btn.setAttribute('aria-expanded', 'false');
            bar1.style.transform = '';
            bar2.style.opacity   = '';
            bar3.style.transform = '';
        }

        btn.addEventListener('click', () => {
            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            isOpen ? closeMenu() : openMenu();
        });

        closeBtn.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);

        // Fermer avec Escape
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeMenu();
        });
    </script>

    @stack('scripts')
</body>
</html>
