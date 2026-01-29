@props(['target' => null, 'id' => null])
<button id="{{ $id }}" type="button" @if($target) data-open-modal="{{ $target }}" @endif
class="md:hidden fixed bottom-20 right-4 z-[40] size-14 rounded-2xl grid place-items-center
               text-white shadow-lg bg-brand-600 hover:bg-brand-700 active:scale-95 transition">
    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
</button>
