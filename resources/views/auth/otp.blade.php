@extends('layouts.app')

@section('content')

<div class="min-h-[80vh] flex items-center justify-center relative">

    {{-- Ambient glows --}}
    <div class="absolute -top-20 -left-20 w-80 h-80 bg-[#bdc2ff]/5 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-[#3626ce]/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="glass-card w-full max-w-md p-8 md:p-12 flex flex-col items-center gap-8 relative z-10 rounded-2xl">

        {{-- Icône --}}
        <div class="w-16 h-16 rounded-full bg-[#191f31] border border-[#454653] flex items-center justify-center">
            <span class="material-symbols-outlined text-[#bdc2ff] text-3xl">mark_email_read</span>
        </div>

        {{-- Titre --}}
        <div class="flex flex-col items-center gap-1 text-center">
            <h1 class="font-[Geist] text-2xl font-bold tracking-tight text-[#bdc2ff]">
                Vérifiez votre email
            </h1>
            <p class="text-sm text-[#908f9e] leading-relaxed">
                Un code à 4 chiffres a été envoyé à<br>
                <span class="text-[#dce1fb] font-medium">{{ $email }}</span>
            </p>
            <p class="font-[JetBrains_Mono] text-[10px] tracking-widest text-[#454653] uppercase mt-1">
                Valable 15 minutes
            </p>
        </div>

        {{-- Erreurs --}}
        @if ($errors->any())
            <div class="w-full px-4 py-3 bg-[#93000a]/30 border border-[#ffb4ab]/30
                        text-[#ffb4ab] rounded-xl text-sm font-[JetBrains_Mono] tracking-wide text-center">
                {{ $errors->first('code') }}
            </div>
        @endif

        <form method="POST" action="{{ route('otp.verify') }}" class="w-full flex flex-col gap-6">
            @csrf

            {{-- Saisie du code — 4 chiffres séparés --}}
            <div class="flex justify-center gap-3" id="otp-inputs">
                @for ($i = 0; $i < 4; $i++)
                    <input type="text"
                           inputmode="numeric"
                           maxlength="1"
                           pattern="[0-9]"
                           autocomplete="one-time-code"
                           class="otp-digit w-14 h-16 text-center text-2xl font-bold font-[JetBrains_Mono]
                                  bg-[#070d1f] border border-[#454653] rounded-xl text-[#dce1fb]
                                  focus:outline-none focus:border-[#bdc2ff] focus:ring-1 focus:ring-[#bdc2ff]/50
                                  transition-all caret-transparent"
                           {{ $i === 0 ? 'autofocus' : '' }}>
                @endfor
                {{-- Champ caché qui reçoit le code complet --}}
                <input type="hidden" name="code" id="otp-hidden">
            </div>

            <button type="submit"
                    id="submit-btn"
                    disabled
                    class="w-full py-4 gradient-btn rounded-xl text-white font-semibold text-sm
                           hover:opacity-90 active:scale-[0.98] transition-all
                           flex items-center justify-center gap-2
                           disabled:opacity-40 disabled:cursor-not-allowed disabled:scale-100">
                Vérifier le code
                <span class="material-symbols-outlined text-sm">verified</span>
            </button>
        </form>

        {{-- Renvoyer le code --}}
        <p class="text-xs text-[#908f9e] font-[JetBrains_Mono] tracking-wide text-center">
            Pas reçu ?
            <a href="{{ route('login') }}"
               class="text-[#bdc2ff] hover:underline underline-offset-4 transition-colors">
                Réessayer
            </a>
        </p>

    </div>
</div>

@endsection

@push('scripts')
<script>
    const digits    = document.querySelectorAll('.otp-digit');
    const hidden    = document.getElementById('otp-hidden');
    const submitBtn = document.getElementById('submit-btn');

    function syncHidden() {
        const code = Array.from(digits).map(d => d.value).join('');
        hidden.value = code;
        submitBtn.disabled = code.length < 4;
    }

    digits.forEach((digit, idx) => {
        digit.addEventListener('input', (e) => {
            // N'accepter que les chiffres
            digit.value = digit.value.replace(/\D/g, '').slice(-1);
            syncHidden();
            // Avancer au suivant
            if (digit.value && idx < digits.length - 1) {
                digits[idx + 1].focus();
            }
        });

        digit.addEventListener('keydown', (e) => {
            // Reculer sur Backspace si vide
            if (e.key === 'Backspace' && !digit.value && idx > 0) {
                digits[idx - 1].focus();
            }
        });

        // Coller le code d'un coup
        digit.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData)
                .getData('text').replace(/\D/g, '').slice(0, 4);
            pasted.split('').forEach((char, i) => {
                if (digits[i]) digits[i].value = char;
            });
            syncHidden();
            const next = digits[Math.min(pasted.length, 3)];
            if (next) next.focus();
        });
    });
</script>
@endpush
