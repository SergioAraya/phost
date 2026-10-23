<?php

use App\Http\Controllers\DownloadMessageAttachmentController;
use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::get('messages/{message}/attachments/{index}', DownloadMessageAttachmentController::class)
    ->whereNumber('index')
    ->name('messages.attachments.download');

Route::get('{selectedMessageId?}', Livewire\Inbox::class)->name('inbox');
