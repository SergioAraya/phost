<?php

use App\Models\Message;
use App\Settings\Config;
use App\Livewire\Settings;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can toggle color schemes')
    ->livewire(Settings::class)
    ->set('theme', 'dark')
    ->call('save')
    ->assertHasNoErrors()
    ->defer(function () {
        expect(resolve(Config::class))->theme->toBe('dark');
    })
    ->set('theme', 'light')
    ->call('save')
    ->assertHasNoErrors()
    ->defer(function () {
        expect(resolve(Config::class))->theme->toBe('light');
    });

it('can delete all messages')
    ->defer(function () {
        Message::factory()->count(2)->create();
        expect(Message::count())->toBe(2);
    })
    ->livewire(Settings::class)
    ->call('clearInbox')
    ->assertRedirect()
    ->defer(function () {
        expect(Message::count())->toBeEmpty();
    });

it('defaults to spanish', function () {
    expect(resolve(Config::class)->locale)->toBe('es');
});

it('translates the notification title from the saved locale', function () {
    app()->setLocale('en');

    app()->getProvider(AppServiceProvider::class)->boot();

    expect(app()->getLocale())->toBe('es')
        ->and(__('notification.received'))->toBe('¡Tienes correo en Phost!');
});

it('can switch to english', function () {
    $this->livewire(Settings::class)
        ->set('locale', 'en')
        ->call('save')
        ->assertHasNoErrors();

    expect(resolve(Config::class)->locale)->toBe('en');
});
