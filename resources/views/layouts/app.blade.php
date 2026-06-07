<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Mon Blog') }}</title>

    {{-- Applique le thème AVANT le rendu pour éviter le flash --}}
    <script>
        (function () {
            const saved = localStorage.getItem('theme');
            // dark par défaut si rien de sauvegardé
            if (saved === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[var(--color-background)] text-[var(--color-on-background)] font-sans min-h-screen transition-colors duration-300">

    {{-- ══════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════ --}}
    <header class="fixed top-0 w-full z-50 border-b transition-colors duration-300"
            style="background-color: color-mix(in srgb, var(--color-background) 80%, transparent);
                   border-color: var(--color-outline-variant);
                   backdrop-filter: blur(12px);">
        <div class="flex justify-between items-center h-16 px-6 max-w-5xl mx-auto">

            {{-- Logo --}}
            <a href="{{ route('home') }}"
                class="flex flex-col transition-colors duration-300">
                    <span class="font-[Geist] text-2xl font-bold tracking-tighter"
                        style="color: var(--color-primary)">
                        {{ config('app.name', 'Mon Blog') }}
                    </span>
                    <span class="text-[9px] font-[JetBrains_Mono] tracking-widest uppercase"
                        style="color: var(--color-outline)">
                        par  BONI Wéri N'doissi
                    </span>
                </a>

            <div class="flex items-center gap-2">
                {{-- Bouton toggle thème --}}
                <button id="theme-btn"
                        aria-label="Basculer le thème"
                        class="flex items-center justify-center w-10 h-10 rounded-lg transition-colors duration-200"
                        style="color: var(--color-on-surface-variant)">
                    {{-- L'icône est mise à jour par JS --}}
                    <span class="material-symbols-outlined" id="theme-icon">dark_mode</span>
                </button>

                {{-- Hamburger --}}
                <button id="menu-btn"
                        aria-label="Menu"
                        aria-expanded="false"
                        aria-controls="drawer"
                        class="flex flex-col justify-center items-center w-10 h-10 gap-1.5 rounded-lg transition-colors duration-200">
                    <span class="block w-5 h-0.5 transition-all duration-300"
                          style="background-color: var(--color-on-surface-variant)" id="bar1"></span>
                    <span class="block w-5 h-0.5 transition-all duration-300"
                          style="background-color: var(--color-on-surface-variant)" id="bar2"></span>
                    <span class="block w-5 h-0.5 transition-all duration-300"
                          style="background-color: var(--color-on-surface-variant)" id="bar3"></span>
                </button>
            </div>
        </div>
    </header>

    {{-- ══════════════════════════════════════════
         DRAWER
    ══════════════════════════════════════════ --}}
    <div id="overlay"
         class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
    </div>

    <nav id="drawer"
         role="dialog"
         aria-label="Menu de navigation"
         class="fixed top-0 right-0 z-50 h-full w-64 border-l
                translate-x-full transition-transform duration-300 flex flex-col py-8 px-6"
         style="background-color: var(--color-surface-container);
                border-color: var(--color-outline-variant);">

        {{-- Bouton fermer --}}
        <button id="close-btn"
                aria-label="Fermer le menu"
                class="self-end mb-8 transition-colors"
                style="color: var(--color-outline)">
            <span class="material-symbols-outlined">close</span>
        </button>

        {{-- Liens --}}
        <div class="flex flex-col gap-2 flex-1">
            <a href="{{ route('home') }}"
               class="flex items-center gap-3 py-3 px-4 rounded-lg font-medium transition-colors duration-200"
               style="color: var(--color-on-surface-variant)"
               onmouseover="this.style.backgroundColor='var(--color-surface-bright)';this.style.color='var(--color-primary)'"
               onmouseout="this.style.backgroundColor='';this.style.color='var(--color-on-surface-variant)'">
                <span class="material-symbols-outlined">dynamic_feed</span>
                <span>Actualités</span>
            </a>

            @if(auth()->user()?->email === 'admin@thehackerexperiment.com')
                <a href="{{ route('admin.posts.create') }}"
                   class="flex items-center gap-3 py-3 px-4 rounded-lg font-medium transition-colors duration-200"
                   style="color: var(--color-on-surface-variant)"
                   onmouseover="this.style.backgroundColor='var(--color-surface-bright)';this.style.color='var(--color-primary)'"
                   onmouseout="this.style.backgroundColor='';this.style.color='var(--color-on-surface-variant)'">
                    <span class="material-symbols-outlined">add_circle</span>
                    <span>Nouveau post</span>
                </a>
            @endif
        </div>

        {{-- Bas du drawer --}}
        <div class="pt-6" style="border-top: 1px solid var(--color-outline-variant)">
            @auth
                {{-- Username --}}
                <div class="flex items-center gap-2 px-4 mb-4">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center
                                text-xs font-bold font-[JetBrains_Mono] uppercase"
                         style="background-color: var(--color-primary-container);
                                color: var(--color-on-primary-container)">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <span class="text-sm font-medium truncate"
                          style="color: var(--color-on-surface)">
                        {{ auth()->user()->name }}
                    </span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 py-3 px-4 w-full rounded-lg transition-colors"
                            style="color: var(--color-outline)"
                            onmouseover="this.style.color='var(--color-error)'"
                            onmouseout="this.style.color='var(--color-outline)'">
                        <span class="material-symbols-outlined">logout</span>
                        <span class="font-[JetBrains_Mono] text-xs tracking-widest uppercase">Déconnexion</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="flex items-center gap-3 py-3 px-4 rounded-lg transition-colors"
                   style="color: var(--color-outline)"
                   onmouseover="this.style.color='var(--color-primary)'"
                   onmouseout="this.style.color='var(--color-outline)'">
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
         SCRIPTS
    ══════════════════════════════════════════ --}}
    <script>
        // ── Thème dark/light ──
        const themeBtn  = document.getElementById('theme-btn');
        const themeIcon = document.getElementById('theme-icon');
        const html      = document.documentElement;

        function applyTheme(isDark) {
            if (isDark) {
                html.classList.add('dark');
                themeIcon.textContent = 'light_mode';   // propose de passer en clair
            } else {
                html.classList.remove('dark');
                themeIcon.textContent = 'dark_mode';    // propose de passer en sombre
            }
        }

        // Init icône selon thème actuel
        applyTheme(html.classList.contains('dark'));

        themeBtn.addEventListener('click', () => {
            const nowDark = html.classList.contains('dark');
            const next    = !nowDark;
            localStorage.setItem('theme', next ? 'dark' : 'light');
            applyTheme(next);
        });

        // ── Hamburger / Drawer ──
        const btn      = document.getElementById('menu-btn');
        const drawer   = document.getElementById('drawer');
        const overlay  = document.getElementById('overlay');
        const closeBtn = document.getElementById('close-btn');
        const bar1     = document.getElementById('bar1');
        const bar2     = document.getElementById('bar2');
        const bar3     = document.getElementById('bar3');

        function openMenu() {
            drawer.classList.remove('translate-x-full');
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            btn.setAttribute('aria-expanded', 'true');
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
            btn.getAttribute('aria-expanded') === 'true' ? closeMenu() : openMenu();
        });
        closeBtn.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenu(); });
    </script>

    @stack('scripts')
</body>
</html>
