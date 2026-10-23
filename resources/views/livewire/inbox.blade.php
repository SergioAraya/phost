<main
    {{-- When the initial server status is offline, we ping a heartbeat, which checks the status & restarts the server process if needed --}}
        wire:init="heartbeat"
        wire:poll.3s="heartbeat"
    {{-- Make sure we stay on the same page when refreshing (workaround for snappier UI) --}}
    x-on:keydown.meta.r.prevent="
        window.location.href = '/{{ $this->message?->id }}';
    "
    class="flex h-screen w-full"
>

    <section @class([
        'relative flex h-full w-4/12 min-w-80 flex-col overflow-y-scroll bg-gray-50 transition-colors dark:bg-neutral-900',
        'pt-6' => PHP_OS_FAMILY !== 'Darwin',
        'pt-8' => PHP_OS_FAMILY === 'Darwin',
    ])>

        <div class="mb-3 flex items-center space-x-2 px-3">
            <label class="sr-only" for="mailbox">{{ __('mailbox.label') }}</label>
            <select
                id="mailbox"
                wire:model.live="mailboxId"
                class="w-full rounded-lg border-none bg-gray-100 p-2 text-sm text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200"
            >
                @foreach ($this->mailboxes as $mailbox)
                    <option value="{{ $mailbox->id }}">
                        {{ $mailbox->slug === 'general' ? __('mailbox.general') : $mailbox->name }}
                    </option>
                @endforeach
            </select>

            <button
                type="button"
                x-on:click="$dispatch('open-mailbox-dialog')"
                class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200"
                aria-label="{{ __('mailbox.create') }}"
            >
                +
            </button>
        </div>

        <label class="px-3">
            <input
                placeholder="{{ __('inbox.search') }}"
                wire:model.live="search"
                class="w-full rounded-lg border-none bg-gray-100 p-4 transition-all duration-200 focus:outline-none focus:ring-2 dark:bg-neutral-800 dark:text-neutral-300 dark:placeholder:text-neutral-400"
            >
        </label>

        <ul class="mt-6 flex-grow">

            @forelse ($this->inbox as $message)
                <li wire:key="{{ $message->id }}">

                    <button
                        wire:click="selectMessage({{ $message->id }})"
                        class="w-full"
                    >
                        <x-message.list-item
                            :$message
                            :selected="$message->id === $selectedMessageId"
                        />
                    </button>

                </li>

            @empty

                <li class="block w-full select-none px-4 py-5 text-center text-neutral-500">
                    @if ($search)
                        {{ __('inbox.nothing_found') }} 🙈
                    @else
                        {{ __('inbox.zero') }} 🎉
                    @endif
                </li>
            @endforelse

        </ul>

        <x-support.statusbar :$online />

    </section>

    <div class="w-full overflow-y-auto">

        @if ($this->message)
            <x-message
                wire:key="message"
                :message="$this->message"
            />
        @else
            <section
                wire:key="no-message"
                class="flex h-full w-full flex-col items-center justify-center bg-white px-4 transition-colors dark:bg-neutral-950"
            >
                <x-heroicon-o-envelope
                    class="size-48 text-neutral-200 dark:text-neutral-500"
                    stroke-width="1"
                />

                <x-input.button
                    level="secondary"
                    x-on:click="$dispatch('open-settings-dialog')"
                    class="flex items-center"
                >
                    <x-heroicon-c-cog-6-tooth class="mr-1 size-3 text-neutral-400 dark:text-neutral-600" />
                    {{ __('inbox.settings') }}
                </x-input.button>
            </section>
        @endif

    </div>

    {{-- Dialogs --}}
    <x-dialog
        x-on:open-settings-dialog.window="open()"
        x-on:close-settings-dialog.window="close()"
    >
        <x-dialog.panel>
            <h2 class="mb-3 font-semibold text-neutral-700 dark:text-neutral-200">{{ __('inbox.configuration') }}</h2>

            <livewire:settings :mailbox-id="$mailboxId" wire:key="settings-{{ $mailboxId }}" />
        </x-dialog.panel>
    </x-dialog>

    <x-dialog
        x-on:open-mailbox-dialog.window="open()"
        x-on:close-mailbox-dialog.window="close()"
    >
        <x-dialog.panel>
            <h2 class="mb-3 font-semibold text-neutral-700 dark:text-neutral-200">{{ __('mailbox.new') }}</h2>

            <form
                wire:submit="createMailbox"
                class="space-y-4"
            >
                <x-input.text
                    :label="__('mailbox.name')"
                    wire:model="newMailboxName"
                />

                <x-dialog.footer>
                    <x-input.button
                        type="button"
                        level="secondary"
                        x-on:click="close()"
                    >
                        {{ __('settings.cancel') }}
                    </x-input.button>

                    <x-input.button type="submit">
                        {{ __('mailbox.create') }}
                    </x-input.button>
                </x-dialog.footer>
            </form>
        </x-dialog.panel>
    </x-dialog>

</main>
