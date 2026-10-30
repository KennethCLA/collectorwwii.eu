<x-layout title="Contact — CollectorWWII"
    metaDescription="Contact CollectorWWII with questions, information or enquiries about the collection."
    :mainClass="'flex items-center justify-center'" :bodyClass="'bg-sage-500'">
    <x-form-layout>
        <div class="w-full max-w-md mx-auto">

            {{-- FUNKMELDUNG header --}}
            <div class="text-center mb-5">
                <p class="font-stencil text-xs tracking-[0.4em] text-khaki/60 uppercase mb-1">Fernschreiben Nr. ——</p>
                <h1 class="font-stencil text-3xl font-black tracking-[0.2em] text-white uppercase">FUNKMELDUNG</h1>
                <p class="font-mono text-[10px] tracking-[0.25em] text-white/35 mt-1 uppercase">Feldpost · WK II Sammlung</p>
            </div>

            <div class="h-px bg-khaki/30 mb-5"></div>

            @if (session('success'))
                <div role="status" class="mb-4 rounded-xl bg-green-800/50 ring-1 ring-green-500/30 p-3 text-sm text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-4 rounded-xl bg-red-800/50 ring-1 ring-red-500/30 p-3 text-sm text-red-200">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->getMessages() as $field => $messages)
                            @foreach ($messages as $index => $error)
                            <li @if ($index === 0) id="contact-error-{{ $field }}" @endif>{{ $error }}</li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('contact.store') }}" method="POST" class="space-y-4">
                @csrf
                <div aria-hidden="true" class="absolute -left-[10000px] h-px w-px overflow-hidden">
                    <label for="website">Website</label>
                    <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>
                <div>
                    <label for="name" class="font-mono block text-[10px] tracking-[0.25em] text-white/95 uppercase mb-1">ABSENDER / NAME</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" @error('name') aria-invalid="true" aria-describedby="contact-error-name" @enderror
                        class="w-full rounded-xl bg-black/25 ring-1 ring-black/40 px-3 py-2 text-white placeholder-white/40 font-mono text-sm
                               @error('name') ring-red-400/60 @enderror"
                        placeholder="Your name" required>
                </div>

                <div>
                    <label for="email" class="font-mono block text-[10px] tracking-[0.25em] text-white/95 uppercase mb-1">FUNKADRESSE / EMAIL</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" @error('email') aria-invalid="true" aria-describedby="contact-error-email" @enderror
                        class="w-full rounded-xl bg-black/25 ring-1 ring-black/40 px-3 py-2 text-white placeholder-white/40 font-mono text-sm
                               @error('email') ring-red-400/60 @enderror"
                        placeholder="your@email.com" required>
                </div>

                <div>
                    <label for="message" class="font-mono block text-[10px] tracking-[0.25em] text-white/95 uppercase mb-1">MELDUNGSTEXT / MESSAGE</label>
                    <textarea id="message" name="message" rows="5" @error('message') aria-invalid="true" aria-describedby="contact-error-message" @enderror
                        class="w-full rounded-xl bg-black/25 ring-1 ring-black/40 px-3 py-2 text-white placeholder-white/40 font-mono text-sm
                               @error('message') ring-red-400/60 @enderror"
                        placeholder="Your message..." required>{{ old('message') }}</textarea>
                </div>

                <div class="h-px bg-khaki/20"></div>

                @if (config('services.turnstile.site_key') && config('services.turnstile.secret_key'))
                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                @endif

                <button type="submit"
                    class="w-full rounded-xl bg-black/30 hover:bg-black/40 ring-1 ring-khaki/30 px-4 py-2.5 font-stencil tracking-[0.2em] text-sm text-white uppercase transition">
                    SENDEN &#8212; Transmit
                </button>
            </form>
        </div>
    </x-form-layout>
</x-layout>
