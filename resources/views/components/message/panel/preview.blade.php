@use('ZBateson\MailMimeParser\Header\HeaderConsts', 'Header')

@props(['message'])

<x-message.panel>

    <iframe
        wire:key="hmtl-preview-{{ str()->random(6) }}"
        x-title="html-preview"
        x-data="{
            resize: () => $nextTick(
                () => setTimeout(() => {
                    const scrollHeight = $el.contentDocument?.body?.scrollHeight ?? null

                    if (scrollHeight != null) {
                        const available = Math.max(window.innerHeight - $el.getBoundingClientRect().top - 24, 160)
                        $el.style.height = Math.max(scrollHeight + 1, available) + 'px'
                    }
                })
            ),
        
            handleAnchorClicks: () => $el.contentDocument.addEventListener('click', function(event) {
                if (event.target.tagName === 'A') {
                    event.preventDefault();
        
                    if (url = event.target.href) {
                        Helpers.openExternal(url);
                    }
                }
            }, true) // Using capture phase to ensure we catch the event first
        }"
        srcdoc="{{ $message->parsed->getHtmlContent() ?? $message->parsed->getTextContent() }}"
        x-on:resize.window.debounce="resize()"
        x-intersect:enter="resize()"
        x-on:load="resize(), handleAnchorClicks()"
        x-init="resize(), handleAnchorClicks()"
        x-cloak
        frameborder="0"
        class="!min-h-[calc(100vh + 45px)] w-full"
    ></iframe>

</x-message.panel>
