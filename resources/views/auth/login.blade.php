<x-layout :mainClass="'flex items-center justify-center'" :bodyClass="'bg-sage-500'">
    <div class="w-full max-w-md">

        {{-- ZUGANG header --}}
        <div class="text-center mb-5">
            <p class="font-stencil text-xs tracking-[0.4em] text-khaki/60 uppercase mb-1">Zugangsberechtigungsausweis</p>
            <h1 class="font-stencil text-3xl font-black tracking-[0.2em] text-white uppercase">ZUGANG</h1>
            <p class="font-mono text-[10px] tracking-[0.25em] text-white/35 mt-1 uppercase">Nur autorisiertes Personal · Befehlsstelle</p>
        </div>

        <div class="rounded-2xl bg-[#2c3335]/75 ring-1 ring-black/40 p-6 text-white noise-texture">
            <div class="h-px bg-khaki/30 mb-5"></div>

            @if ($errors->any())
                <div class="mb-4 rounded-xl bg-red-800/50 ring-1 ring-red-500/30 p-3 text-sm text-red-200">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="font-mono block text-[10px] tracking-[0.25em] text-white/95 uppercase mb-1">KENNZEICHEN / EMAIL</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus @error('email') aria-invalid="true" aria-describedby="login-error-email" @enderror
                        class="w-full rounded-xl bg-black/25 ring-1 ring-black/40 px-3 py-2 text-white placeholder-white/40 font-mono text-sm
                               @error('email') ring-red-400/60 @enderror" />
                    @error('email')
                    <p id="login-error-email" class="mt-1 text-sm text-red-200">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="font-mono block text-[10px] tracking-[0.25em] text-white/95 uppercase mb-1">LOSUNGSWORT / PASSWORT</label>
                    <input id="password" type="password" name="password" required @error('password') aria-invalid="true" aria-describedby="login-error-password" @enderror
                        class="w-full rounded-xl bg-black/25 ring-1 ring-black/40 px-3 py-2 text-white placeholder-white/40 font-mono text-sm
                               @error('password') ring-red-400/60 @enderror" />
                    @error('password')
                    <p id="login-error-password" class="mt-1 text-sm text-red-200">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                            class="rounded border-black/40 bg-black/25 text-white" />
                        <span class="font-mono text-[10px] tracking-[0.15em] text-white/95 uppercase">Eingeloggt bleiben</span>
                    </label>

                    @if (Route::has('password.request'))
                    <a class="font-mono text-[10px] tracking-[0.15em] text-white/95 hover:text-white uppercase"
                        href="{{ route('password.request') }}">
                        Vergessen?
                    </a>
                    @endif
                </div>

                <div class="h-px bg-khaki/20"></div>

                <button type="submit"
                    class="w-full rounded-xl bg-black/30 hover:bg-black/40 ring-1 ring-khaki/30 px-4 py-2.5 font-stencil tracking-[0.2em] text-sm text-white uppercase transition">
                    EINTRETEN &#8212; Enter
                </button>
            </form>
        </div>
    </div>
</x-layout>
