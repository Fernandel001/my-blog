@extends('layouts.app')

@section('content')

<div class="min-h-[80vh] flex items-center justify-center relative">

    {{-- Ambient glows --}}
    <div class="absolute -top-20 -left-20 w-80 h-80 bg-[#bdc2ff]/5 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-[#3626ce]/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="glass-card w-full max-w-md p-8 md:p-12 flex flex-col items-center gap-8 relative z-10 rounded-2xl">

        {{-- Logo --}}
        <div class="flex flex-col items-center gap-1">
            <h1 class="font-[Geist] text-4xl font-bold tracking-tighter text-[#bdc2ff]">
                {{ config('app.name', 'Mon Blog') }}
            </h1>
            <p class="font-[JetBrains_Mono] text-[11px] tracking-widest text-[#908f9e] uppercase">
                Connexion
            </p>
        </div>

        {{-- Erreurs --}}
        @if ($errors->any())
            <div class="w-full px-4 py-3 bg-[#93000a]/30 border border-[#ffb4ab]/30
                        text-[#ffb4ab] rounded-xl text-sm font-[JetBrains_Mono] tracking-wide">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="w-full flex flex-col gap-5" id="login-form">
            @csrf

            {{-- Email --}}
            <div class="relative group">
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder=" "
                       required
                       autofocus
                       class="peer block w-full px-4 pt-6 pb-2 bg-[#070d1f] border border-[#454653]
                              rounded-xl text-[#dce1fb] text-sm
                              focus:outline-none focus:border-[#bdc2ff] focus:ring-1 focus:ring-[#bdc2ff]/50
                              transition-all">
                <label for="email"
                       class="absolute left-4 top-4 text-[#908f9e] text-sm transition-all duration-200
                              pointer-events-none origin-left
                              peer-focus:-translate-y-3 peer-focus:scale-75 peer-focus:text-[#bdc2ff]
                              peer-[:not(:placeholder-shown)]:-translate-y-3 peer-[:not(:placeholder-shown)]:scale-75">
                    Adresse email
                </label>
                <span class="material-symbols-outlined absolute right-4 top-4 text-[#908f9e]
                             group-focus-within:text-[#bdc2ff] transition-colors text-xl">
                    alternate_email
                </span>
            </div>

            {{-- Mot de passe — visible uniquement pour l'admin --}}
            <div id="password-field" class="relative group hidden">
                <input type="password"
                       id="password"
                       name="password"
                       placeholder=" "
                       class="peer block w-full px-4 pt-6 pb-2 bg-[#070d1f] border border-[#454653]
                              rounded-xl text-[#dce1fb] text-sm
                              focus:outline-none focus:border-[#bdc2ff] focus:ring-1 focus:ring-[#bdc2ff]/50
                              transition-all">
                <label for="password"
                       class="absolute left-4 top-4 text-[#908f9e] text-sm transition-all duration-200
                              pointer-events-none origin-left
                              peer-focus:-translate-y-3 peer-focus:scale-75 peer-focus:text-[#bdc2ff]
                              peer-[:not(:placeholder-shown)]:-translate-y-3 peer-[:not(:placeholder-shown)]:scale-75">
                    Mot de passe
                </label>
                <button type="button" id="toggle-pass"
                        class="absolute right-4 top-4 text-[#908f9e] hover:text-[#bdc2ff] transition-colors">
                    <span class="material-symbols-outlined text-xl" id="pass-icon">visibility</span>
                </button>
            </div>

            {{-- Remember me (admin seulement) --}}
            <label id="remember-field" class="hidden items-center gap-2 cursor-pointer group">
                <input type="checkbox" name="remember"
                       class="w-4 h-4 rounded border-[#454653] bg-[#070d1f] text-[#818cf8]
                              focus:ring-[#818cf8]/50">
                <span class="font-[JetBrains_Mono] text-[11px] tracking-widest text-[#908f9e]
                             uppercase group-hover:text-[#dce1fb] transition-colors">
                    Se souvenir de moi
                </span>
            </label>

            {{-- Hint visiteur --}}
            <p id="otp-hint" class="text-xs text-[#908f9e] font-[JetBrains_Mono] tracking-wide hidden">
                Un code à 4 chiffres sera envoyé à cet email.
            </p>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full py-4 gradient-btn rounded-xl text-white font-semibold text-sm
                           hover:opacity-90 active:scale-[0.98] transition-all
                           flex items-center justify-center gap-2">
                <span id="btn-label">Continuer</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const emailInput   = document.getElementById('email');
    const passField    = document.getElementById('password-field');
    const rememberField = document.getElementById('remember-field');
    const otpHint      = document.getElementById('otp-hint');
    const btnLabel     = document.getElementById('btn-label');
    const ADMIN_EMAIL  = 'admin@thehackerexperiment.com';

    function updateForm() {
        const isAdmin = emailInput.value.trim().toLowerCase() === ADMIN_EMAIL;
        passField.classList.toggle('hidden', !isAdmin);
        rememberField.classList.toggle('hidden', !isAdmin);
        rememberField.classList.toggle('flex', isAdmin);
        otpHint.classList.toggle('hidden', isAdmin);
        btnLabel.textContent = isAdmin ? 'Se connecter' : 'Recevoir le code';
        document.getElementById('password').required = isAdmin;
    }

    emailInput.addEventListener('input', updateForm);

    // Restore state si old('email') est présent
    if (emailInput.value) updateForm();

    // Toggle password visibility
    document.getElementById('toggle-pass').addEventListener('click', () => {
        const p = document.getElementById('password');
        const i = document.getElementById('pass-icon');
        const hidden = p.type === 'password';
        p.type = hidden ? 'text' : 'password';
        i.textContent = hidden ? 'visibility_off' : 'visibility';
    });

    // Parallax glows
    document.addEventListener('mousemove', (e) => {
        const xP = (e.clientX / window.innerWidth  - 0.5) * 20;
        const yP = (e.clientY / window.innerHeight - 0.5) * 20;
        document.querySelectorAll('.blur-\\[120px\\]').forEach((el, i) => {
            const m = i === 0 ? 1 : -1;
            el.style.transform = `translate(${xP * m}px, ${yP * m}px)`;
        });
    });
</script>
@endpush
