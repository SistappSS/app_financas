<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <label class="block text-sm">
        <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">Nome</span>
        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $user->name ?? '') }}"
            placeholder="John Doe"
            required
            class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
        >
    </label>

    <label class="block text-sm">
        <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">E-mail</span>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email', $user->email ?? '') }}"
            placeholder="john.doe@email.com"
            required
            class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
        >
    </label>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <label class="block text-sm">
        <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">Senha</span>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="********"
            class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
        >
    </label>

    <label class="block text-sm">
        <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">Confirmar senha</span>
        <input
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            placeholder="********"
            class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
        >
    </label>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <label class="block text-sm" id="imageInputWrap">
        <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">Foto</span>

        {{-- arquivo só para o front ler --}}
        <input
            type="file"
            id="image_file"
            name="image_file"
            accept="image/*"
            class="mt-1 block w-full text-sm text-neutral-700 dark:text-neutral-200
                   file:mr-3 file:rounded-lg file:border-0
                   file:bg-neutral-100 file:px-3 file:py-2
                   dark:file:bg-neutral-800 dark:file:text-neutral-100
                   file:text-xs cursor-pointer"
        >

        {{-- hidden que realmente vai pro backend --}}
        <input type="hidden" id="image" name="image">
    </label>

    <div id="imagePreview" class="hidden">
        {{-- preenchido via JS --}}
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('formUser');
            if (!form) return;

            const fileEl   = form.querySelector('#image_file');
            const hiddenEl = form.querySelector('#image');
            const wrapEl   = form.querySelector('#imageInputWrap');
            const prevEl   = form.querySelector('#imagePreview');

            if (!fileEl || !hiddenEl || !wrapEl || !prevEl) return;

            function showPreview(dataUrl) {
                // manda o dataURL inteiro; o backend já trata "data:image..."
                hiddenEl.value = dataUrl;

                prevEl.innerHTML = `
            <div class="relative inline-block">
                <img src="${dataUrl}" alt="preview"
                     class="w-28 h-28 rounded-xl object-cover border border-neutral-200/70 dark:border-neutral-800/70">
                <button type="button" id="changeImg"
                        class="absolute bottom-1 right-1 inline-flex items-center rounded-lg bg-white/95 dark:bg-neutral-900/95 border border-neutral-200/70 dark:border-neutral-800/70 px-2 py-1 text-[11px] shadow-sm">
                    Trocar
                </button>
            </div>`;
                prevEl.classList.remove('hidden');
                wrapEl.classList.add('hidden');
            }

            function clearPreview() {
                prevEl.innerHTML = '';
                prevEl.classList.add('hidden');
                wrapEl.classList.remove('hidden');
                fileEl.value = '';
                hiddenEl.value = '';
            }

            fileEl.addEventListener('change', () => {
                const f = fileEl.files && fileEl.files[0];
                if (!f) { clearPreview(); return; }

                const r = new FileReader();
                r.onload = () => showPreview(r.result);
                r.readAsDataURL(f);
            });

            prevEl.addEventListener('click', (e) => {
                if (e.target && e.target.id === 'changeImg') {
                    clearPreview();
                    fileEl.click();
                }
            });

            form.addEventListener('reset', () => {
                setTimeout(clearPreview, 0);
            });
        });
    </script>
@endpush
