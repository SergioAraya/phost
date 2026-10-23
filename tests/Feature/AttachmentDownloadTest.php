<?php

use App\Livewire\Inbox;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Laravel\Http\Middleware\PreventRegularBrowserAccess;

uses(RefreshDatabase::class);

it('shows an attachment that only has a disposition name', function () {
    $message = Message::factory()->create([
        'content' => attachmentMessage('name="compras.xlsx"'),
    ]);

    $this->livewire(Inbox::class, [$message->id])
        ->assertSee('compras.xlsx')
        ->assertSeeHtml('1 adjuntos');
});

it('downloads the attachment bytes', function () {
    $this->withoutMiddleware(PreventRegularBrowserAccess::class);

    $message = Message::factory()->create([
        'content' => attachmentMessage('name="compras.xlsx"', base64_encode('spreadsheet-bytes')),
    ]);

    $this->get(route('messages.attachments.download', ['message' => $message, 'index' => 0]))
        ->assertOk()
        ->assertDownload('compras.xlsx')
        ->assertStreamedContent('spreadsheet-bytes');
});

it('returns not found for a missing attachment', function () {
    $this->withoutMiddleware(PreventRegularBrowserAccess::class);

    $message = Message::factory()->create();

    $this->get(route('messages.attachments.download', ['message' => $message, 'index' => 3]))
        ->assertNotFound();
});

function attachmentMessage(string $disposition, string $body = ''): string
{
    $encoded = $body !== '' ? $body : base64_encode('file');

    return implode("\n", [
        'From: Example <hello@example.com>',
        'To: inbox@phost.test',
        'Subject: With file',
        'MIME-Version: 1.0',
        'Content-Type: multipart/mixed; boundary=BOUND',
        '',
        '--BOUND',
        'Content-Type: text/plain; charset=utf-8',
        '',
        'Hello',
        '',
        '--BOUND',
        'Content-Type: application/octet-stream',
        'Content-Transfer-Encoding: base64',
        'Content-Disposition: attachment; '.$disposition.';',
        '',
        $encoded,
        '',
        '--BOUND--',
        '',
    ]);
}
