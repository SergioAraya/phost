@props(['message'])

@php
    $attachments = $message->attachments();
@endphp

@if ($attachments !== [])
    <div class="mb-3 flex items-center space-x-2">
        @foreach ($attachments as $attachment)
            <a
                href="{{ route('messages.attachments.download', ['message' => $message, 'index' => $attachment['index']]) }}"
                download="{{ $attachment['filename'] }}"
                class="inline-flex items-center space-x-2 rounded-md bg-neutral-100 px-3 py-1 text-sm text-neutral-700 ring-1 ring-neutral-200 dark:bg-neutral-800 dark:text-neutral-200 dark:ring-neutral-700"
            >
                <x-heroicon-o-paper-clip class="size-4" />
                <span>{{ $attachment['filename'] }}</span>
            </a>
        @endforeach
    </div>
@endif
