<?php

use App\Livewire\Inbox;
use App\Livewire\Settings;
use App\Models\Mailbox;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps existing messages in the general mailbox', function () {
    $general = Mailbox::query()->where('slug', 'general')->first();

    expect($general)->not->toBeNull()
        ->port->toBe(2525);

    $message = Message::factory()->create();

    expect($message->mailbox_id)->toBe($general->id);
});

it('hides messages that belong to another mailbox', function () {
    $general = Mailbox::query()->where('slug', 'general')->first();
    $other = Mailbox::createFromName('Facturación');

    Message::factory()->content(['title' => 'Only general'])->create([
        'mailbox_id' => $general->id,
    ]);
    Message::factory()->content(['title' => 'Only billing'])->create([
        'mailbox_id' => $other->id,
    ]);

    $this->livewire(Inbox::class)
        ->assertSee('Only general')
        ->assertDontSee('Only billing')
        ->set('mailboxId', $other->id)
        ->assertSee('Only billing')
        ->assertDontSee('Only general');
});

it('creates a mailbox with its own slug and port', function () {
    $this->livewire(Inbox::class)
        ->set('newMailboxName', 'Facturación')
        ->call('createMailbox')
        ->assertHasNoErrors();

    $mailbox = Mailbox::query()->where('slug', 'facturacion')->first();

    expect($mailbox)->not->toBeNull()
        ->port->toBe(2526)
        ->host()->toBe('facturacion.localhost');
});

it('deletes the selected mailbox only after the action runs', function () {
    $general = Mailbox::query()->where('slug', 'general')->first();
    $other = Mailbox::createFromName('Facturación');
    $message = Message::factory()->create([
        'mailbox_id' => $other->id,
    ]);

    $this->livewire(Settings::class, ['mailboxId' => $other->id])
        ->assertSee('Eliminar buzón')
        ->assertSee('¿Eliminar el buzón Facturación?')
        ->assertSee('Sus correos se borrarán y el puerto 2526 dejará de recibir. ¿Continuar?')
        ->assertSee('Última confirmación: esta acción no se puede deshacer. ¿Eliminar Facturación?')
        ->call('deleteMailbox')
        ->assertRedirect(route('inbox', ['mailboxId' => $general->id]));

    expect(Mailbox::query()->find($other->id))->toBeNull()
        ->and(Message::query()->find($message->id))->toBeNull()
        ->and(Mailbox::query()->find($general->id))->not->toBeNull();
});

it('keeps the only mailbox', function () {
    $general = Mailbox::query()->where('slug', 'general')->first();

    $this->livewire(Settings::class, ['mailboxId' => $general->id])
        ->assertDontSee('Eliminar buzón')
        ->call('deleteMailbox')
        ->assertNoRedirect();

    expect(Mailbox::query()->find($general->id))->not->toBeNull();
});

it('opens a remaining mailbox when general was deleted', function () {
    $other = Mailbox::createFromName('Facturación');
    Mailbox::query()->where('slug', 'general')->delete();

    $this->livewire(Inbox::class)
        ->assertOk()
        ->assertSet('mailboxId', $other->id);

    $this->livewire(Settings::class)
        ->assertSet('mailboxId', $other->id);
});

it('shows the selected mailbox host and port in the connection snippet', function () {
    $mailbox = Mailbox::createFromName('Facturación');

    $this->livewire(Settings::class, ['mailboxId' => $mailbox->id])
        ->assertSee('facturacion.localhost')
        ->assertSee('MAIL_PORT='.$mailbox->port)
        ->assertSee('MAIL_HOST=facturacion.localhost');
});
