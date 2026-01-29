@extends('layouts.templates.app')

@section('new-content')
    <section class="mt-6">

        <div class="mb-4">
            <h2 class="text-xl font-semibold">Outros problemas ou dúvidas</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Não encontrou sua dúvida nas outras categorias? Conta pra gente o que está acontecendo.
            </p>
        </div>

        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-4 space-y-4 text-sm text-neutral-800 dark:text-neutral-200">

            {{-- Mensagem de sucesso --}}
            @if(session('success'))
                <div class="px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 text-xs">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Pequena explicação --}}
            <p>
                Descreva com o máximo de detalhes o problema ou a dúvida que você está tendo.
                Nossa equipe vai analisar e usar essas informações para melhorar o app e, quando necessário,
                entrar em contato pelos canais oficiais.
            </p>

            <form method="POST"
                  action="{{ route('support.outros.store') }}"
                  enctype="multipart/form-data"
                  class="space-y-3">
                @csrf

                <div>
                    <label for="subject" class="block text-xs font-medium mb-1">
                        Assunto (opcional)
                    </label>
                    <input type="text" id="subject" name="subject"
                           class="w-full border border-neutral-300 dark:border-neutral-700 rounded-xl px-3 py-2 text-sm bg-white dark:bg-neutral-950"
                           placeholder="Ex: Dificuldade para cadastrar cofrinho"
                           value="{{ old('subject') }}">
                </div>

                <div>
                    <label for="message" class="block text-xs font-medium mb-1">
                        Descreva seu problema ou dúvida *
                    </label>
                    <textarea id="message" name="message" rows="5" required
                              class="w-full border border-neutral-300 dark:border-neutral-700 rounded-xl px-3 py-2 text-sm bg-white dark:bg-neutral-950"
                              placeholder="Explique o que você tentou fazer, o que esperava que acontecesse e o que aconteceu de fato.">{{ old('message') }}</textarea>

                    @error('message')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Anexar imagens --}}
                <div>
                    <label class="block text-xs font-medium mb-1">
                        Anexar imagens (opcional)
                    </label>
                    <input type="file"
                           id="supportImagesInput"
                           name="images[]"
                           accept="image/*"
                           multiple
                           class="w-full text-xs text-neutral-600 dark:text-neutral-300">

                    <p class="text-[11px] text-neutral-500 mt-1">
                        Você pode enviar até 5 imagens. Tamanho máximo de 4MB por imagem.
                    </p>

                    @error('images')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror

                    {{-- Preview das imagens selecionadas --}}
                    <div id="supportImagesPreview" class="mt-2 flex flex-wrap gap-2">
                        {{-- thumbs via JS --}}
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm">
                        Enviar para suporte
                    </button>
                </div>
            </form>

            {{-- Link de vídeo (se quiser deixar algo genérico aqui também) --}}
            <div class="pt-3 border-t border-dashed border-neutral-200 dark:border-neutral-700 mt-2">
                <p class="text-xs text-neutral-500 mb-1">
                    Veja também nosso vídeo geral de apresentação do app:
                </p>
                <a href="https://www.youtube.com/seu-video-geral-aqui"
                   target="_blank"
                   class="inline-flex items-center gap-1 text-xs text-brand-600 hover:underline">
                    Ver vídeo no YouTube
                </a>
            </div>
        </div>

    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const input = document.getElementById('supportImagesInput');
            const preview = document.getElementById('supportImagesPreview');

            if (!input || !preview) return;

            input.addEventListener('change', () => {
                preview.innerHTML = '';

                const files = Array.from(input.files || []);
                if (!files.length) return;

                files.forEach(file => {
                    if (!file.type.startsWith('image/')) return;

                    const reader = new FileReader();

                    reader.onload = (e) => {
                        const wrapper = document.createElement('div');
                        wrapper.className =
                            'w-16 h-16 rounded-lg overflow-hidden border border-neutral-200 ' +
                            'dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center';

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = file.name;
                        img.className = 'w-full h-full object-cover';
                        wrapper.title = file.name;

                        wrapper.appendChild(img);
                        preview.appendChild(wrapper);
                    };

                    reader.readAsDataURL(file);
                });
            });
        })();
    </script>
@endpush
