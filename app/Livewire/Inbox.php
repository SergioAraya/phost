<?php

namespace App\Livewire;

use App\Models\Mailbox;
use App\Models\Message;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use App\Events\MessageReceived;
use Native\Laravel\Facades\App;
use App\Livewire\Concerns\Config;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use Native\Laravel\Facades\ChildProcess;
use App\Livewire\Concerns\MessageControls;
use Native\Laravel\Events\ChildProcess\ProcessExited;
use Native\Laravel\Events\ChildProcess\ProcessSpawned;

/**
 * @property ?Message $message
 * @property Collection $inbox
 * @property Collection $mailboxes
 */
#[Title('Phost | Inbox')]
class Inbox extends Component
{
    use Config;
    use MessageControls;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $mailboxId = null;

    #[Validate('required|string|max:80')]
    public string $newMailboxName = '';

    public bool $online = false;

    public ?int $selectedMessageId = null;

    public function mount($messageId = null)
    {
        $this->mailboxId = Mailbox::resolveId($this->mailboxId);

        $this->updateBadgeCount();

        if ($messageId) {
            $message = Message::query()->find($messageId);

            if ($message) {
                $this->mailboxId = $message->mailbox_id;
                $this->selectMessage($message->id);
            }
        }
    }

    public function updatedMailboxId(): void
    {
        $this->selectedMessageId = null;
        $this->search = '';
        unset($this->inbox, $this->message);
        $this->refreshOnlineState();
    }

    public function createMailbox(): void
    {
        $this->validateOnly('newMailboxName');

        $mailbox = Mailbox::createFromName($this->newMailboxName);
        $this->newMailboxName = '';
        $this->mailboxId = $mailbox->id;
        unset($this->mailboxes, $this->inbox, $this->message);
        $this->selectedMessageId = null;
        $this->startServer($mailbox);
        $this->refreshOnlineState();
        $this->dispatch('close-mailbox-dialog');
    }

    //---------------------------------------------------------------
    // Public API
    //---------------------------------------------------------------
    public function selectMessage(int $id)
    {
        $this->selectedMessageId = $id;

        $this->message?->markRead();

        $this->updateBadgeCount();
    }

    //---------------------------------------------------------------
    // Computed properties
    //---------------------------------------------------------------
    #[Computed()]
    public function message(): ?Message
    {
        if (! $this->selectedMessageId) {
            return null;
        }

        return Message::find($this->selectedMessageId);
    }

    #[Computed()]
    public function inbox(): Collection
    {
        return Message::query()
            ->where('mailbox_id', $this->mailboxId)
            ->when($this->search, fn ($query) => $query
                ->where('content', 'like', '%' . trim($this->search) . '%')
            )
            ->orderByDesc('bookmarked')
            ->latest()
            ->get();
    }

    #[Computed()]
    public function mailboxes(): Collection
    {
        return Mailbox::query()->orderBy('name')->get();
    }

    //---------------------------------------------------------------
    // Listeners
    //---------------------------------------------------------------
    #[On('native:' . MessageReceived::class)]
    public function messageReceived(int $mailboxId = 0)
    {
        if ($mailboxId === (int) $this->mailboxId) {
            unset($this->inbox);
        }

        $this->updateBadgeCount();
    }

    #[On('native:' . ProcessSpawned::class)]
    public function serverStarted($alias)
    {
        if ($alias !== 'smtp-'.$this->mailboxId) {
            return $this->skipRender();
        }

        $this->online = true;
    }

    #[On('native:' . ProcessExited::class)]
    public function serverStopped($alias)
    {
        if ($alias !== 'smtp-'.$this->mailboxId) {
            return $this->skipRender();
        }

        $this->online = false;
    }

    public function heartbeat()
    {
        if (app()->runningUnitTests()) {
            return;
        }

        foreach (Mailbox::query()->get() as $mailbox) {
            if (! ChildProcess::get($mailbox->processAlias())) {
                $this->startServer($mailbox);
            }
        }

        $this->refreshOnlineState();
    }

    protected function startServer(?Mailbox $mailbox = null)
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $mailbox ??= Mailbox::query()->find($this->mailboxId);

        if (! $mailbox) {
            return;
        }

        ChildProcess::artisan(
            cmd: ['smtp:serve', (string) $mailbox->port],
            alias: $mailbox->processAlias(),
            persistent: false
        );
    }

    protected function refreshOnlineState(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $this->online = (bool) ChildProcess::get('smtp-'.$this->mailboxId);
    }

    protected function updateBadgeCount()
    {
        if (app()->runningUnitTests()) {
            return;
        }

        App::badgeCount(
            Message::unread()->where('mailbox_id', $this->mailboxId)->count()
        );
    }
}
