@props([
  'id',
  'titleCreate' => 'Novo registro',
  'titleEdit'   => 'Editar registro',
  'titleShow'   => 'Detalhes',
  'submitLabel' => 'Salvar',
  'formId'      => null,
])

@php
    $formId = $formId ?: $id.'Form';
@endphp

<div id="{{ $id }}" class="fixed inset-0 z-[95] hidden" role="dialog" aria-modal="true">
    <div data-crud-overlay class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="absolute inset-x-0 bottom-0 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-[560px]">
        <div data-crud-body class="rounded-t-3xl md:rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 shadow-soft dark:shadow-softDark p-4 md:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h3 data-crud-title class="text-lg font-semibold">{{ $titleCreate }}</h3>
                    @isset($subtitle)
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $subtitle }}</p>
                    @endisset
                </div>
                <button data-crud-close
                        class="size-10 grid place-items-center rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        aria-label="Fechar">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="{{ $formId }}" class="mt-4 grid gap-3" novalidate>
                <div data-form-error class="hidden mb-2 rounded-lg bg-red-50 text-red-700 text-sm px-3 py-2"></div>

                {{ $slot }}

                <div class="mt-2 flex items-center justify-end gap-2">
                    <button type="button" data-crud-cancel
                            class="px-3 py-2 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-800">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                        {{ $submitLabel }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
