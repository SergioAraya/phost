<?php

namespace App\Livewire;

use Throwable;
use App\Models\Mailbox;
use App\Models\Message;
use Livewire\Component;
use App\Enums\Framework;
use App\Livewire\Concerns\Config;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Native\Laravel\Facades\ChildProcess;

/**
 * Note that the property casing is non-standard, because we're mapping directly to the config object
 */
class Settings extends Component
{
    use Config;

    public ?string $framework = 'Laravel';

    public ?int $mailboxId = null;

    #[Validate('required|in:light,dark')]
    public string $theme;

    #[Validate('required|in:en,es')]
    public string $locale;

    public function mount(?int $mailboxId = null)
    {
        $this->mailboxId = Mailbox::resolveId($mailboxId ?? $this->mailboxId);
        $this->theme = $this->config->theme;
        $this->locale = $this->config->locale;
    }

    //---------------------------------------------------------------
    // Public API
    //---------------------------------------------------------------
    public function save()
    {
        $oldLocale = $this->config->locale;

        $validated = $this->validate();

        $this->config->fill($validated)->save();

        $this->activateTheme();
        $this->dispatch('close-settings-dialog');

        if ($this->locale !== $oldLocale) {
            app()->setLocale($this->locale);
            $this->js('window.location.reload()');
        }
    }

    public function clearInbox()
    {
        Message::query()->where('mailbox_id', $this->mailboxId)->delete();

        return $this->redirectRoute('inbox', navigate: true);
    }

    public function deleteMailbox(): mixed
    {
        if (! $this->canDeleteMailbox) {
            return null;
        }

        $mailbox = $this->mailbox;
        $fallback = Mailbox::query()
            ->whereKeyNot($mailbox->id)
            ->orderByRaw("slug = 'general' desc")
            ->orderBy('name')
            ->first();

        $this->stopListener($mailbox);
        $mailbox->delete();

        return $this->redirect(route('inbox', ['mailboxId' => $fallback->id]), navigate: true);
    }

    protected function stopListener(Mailbox $mailbox): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        try {
            ChildProcess::stop($mailbox->processAlias());
        } catch (Throwable) {
            // The listener may already be stopped.
        }
    }

    //---------------------------------------------------------------
    // Hooks
    //---------------------------------------------------------------
    public function updatedTheme()
    {
        $this->activateTheme();
    }

    //---------------------------------------------------------------
    // Computed properties
    //---------------------------------------------------------------
    #[Computed()]
    public function mailbox(): Mailbox
    {
        return Mailbox::query()->findOrFail($this->mailboxId);
    }

    #[Computed]
    public function canDeleteMailbox(): bool
    {
        return Mailbox::query()->count() > 1;
    }

    #[Computed()]
    public function selectedFramework(): ?Framework
    {
        return Framework::tryFrom($this->framework);
    }

    //---------------------------------------------------------------
    // MISC
    //---------------------------------------------------------------
    public function activateTheme()
    {
        match ($this->theme) {
            'dark' => $this->js(<<< 'JS'
                document.documentElement.classList.remove('light');
                document.documentElement.classList.add('dark')
            JS),
            'light' => $this->js(<<< 'JS'
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light')
            JS),
            default => fn () => null
        };
    }
}
