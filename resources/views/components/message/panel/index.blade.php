<div x-tabs:panel>
    <div
        class="mb-4 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-md dark:ring-neutral-700"
        x-data="{
            fill: () => {
                const available = Math.max(window.innerHeight - $el.getBoundingClientRect().top - 24, 160)
                $el.style.minHeight = available + 'px'
            }
        }"
        x-init="fill()"
        x-intersect:enter="fill()"
        x-on:resize.window.debounce="fill()"
        x-cloak
    >
        <div {{ $attributes->class('overflow-x-auto') }}>
            {{ $slot }}
        </div>
    </div>
</div>
