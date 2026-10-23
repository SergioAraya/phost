@use(\App\Enums\Framework)

<div
    x-tabs
    x-model="selectedTabIndex"
    x-data="{ selectedTabIndex: 0 }"
>
    <nav
        x-tabs:list
        class="mb-4 flex space-x-2"
    >
        <x-message.tab-button>
            {{ __('settings.tab_configuration') }}
        </x-message.tab-button>

        <x-message.tab-button>
            {{ __('settings.connection') }}
        </x-message.tab-button>
    </nav>

    <div
        x-cloak
        x-tabs:panels
    >
        <div x-tabs:panel>
            <form
                wire:submit="save"
                novalidate
            >
                <x-input.fieldset
                    :legend="__('settings.language')"
                    class="space-x-4"
                >
                    <x-input.radio
                        wire:model.live="locale"
                        value="es"
                        :label="__('settings.spanish')"
                    />

                    <x-input.radio
                        wire:model.live="locale"
                        value="en"
                        :label="__('settings.english')"
                    />
                </x-input.fieldset>

                <x-input.fieldset
                    :legend="__('settings.color_scheme')"
                    class="space-x-4"
                >
                    <x-input.radio
                        wire:model.live="theme"
                        value="light"
                    >
                        <x-slot:label
                            class="flex"
                        >
                            <x-heroicon-c-sun class="size-4 text-neutral-600 dark:text-neutral-300" />
                        </x-slot:label>
                    </x-input.radio>

                    <x-input.radio
                        wire:model.live="theme"
                        value="dark"
                    >
                        <x-slot:label
                            class="flex"
                        >
                            <x-heroicon-c-moon class="size-4 text-neutral-600 dark:text-neutral-300" />
                        </x-slot:label>
                    </x-input.radio>
                </x-input.fieldset>

                <x-input.fieldset
                    :legend="__('settings.danger_zone')"
                    class="items-center space-x-2"
                >
                    <x-input.button
                        type="button"
                        level="danger"
                        wire:click="clearInbox"
                        wire:confirm="{{ __('settings.confirm_clear') }}"
                        class="flex items-center"
                    >
                        <x-heroicon-c-trash class="mr-1 size-3" />
                        {{ __('settings.clear_inbox') }}
                    </x-input.button>

                    @if ($this->canDeleteMailbox)
                        <x-input.button
                            type="button"
                            level="danger"
                            class="flex items-center"
                            data-confirm1="{{ __('mailbox.confirm_delete_1', ['name' => $this->mailbox->name]) }}"
                            data-confirm2="{{ __('mailbox.confirm_delete_2', ['name' => $this->mailbox->name, 'port' => $this->mailbox->port]) }}"
                            data-confirm3="{{ __('mailbox.confirm_delete_3', ['name' => $this->mailbox->name]) }}"
                            x-data="{
                                ask() {
                                    if (! confirm(this.$el.dataset.confirm1)) return
                                    if (! confirm(this.$el.dataset.confirm2)) return
                                    if (! confirm(this.$el.dataset.confirm3)) return
                                    this.$wire.deleteMailbox()
                                }
                            }"
                            x-on:click="ask()"
                        >
                            <x-heroicon-c-trash class="mr-1 size-3" />
                            {{ __('mailbox.delete') }}
                        </x-input.button>
                    @endif
                </x-input.fieldset>

                <x-dialog.footer>
                    <x-input.button
                        type="button"
                        level="secondary"
                        x-on:click="close()"
                    >
                        {{ __('settings.cancel') }}
                    </x-input.button>

                    <x-input.button type="submit">
                        {{ __('settings.save') }}
                    </x-input.button>
                </x-dialog.footer>
            </form>
        </div>

        <div x-tabs:panel>
            <div class="mb-3 flex flex-row space-x-4">
                @foreach (Framework::cases() as $framework)
                    <x-input.radio
                        wire:model.live="framework"
                        :wire:key="$framework->value"
                        :value="$framework->value"
                        :label="$framework->value"
                    />
                @endforeach
            </div>

            <x-support.config
                :framework="$this->selectedFramework"
                :port="$this->mailbox->port"
                :host="$this->mailbox->host()"
            />
        </div>
    </div>
</div>
