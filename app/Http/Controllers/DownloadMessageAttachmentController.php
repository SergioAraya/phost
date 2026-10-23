<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadMessageAttachmentController extends Controller
{
    public function __invoke(Message $message, int $index): StreamedResponse
    {
        $part = $message->parsed->getAttachmentPart($index);

        abort_if($part === null, 404);

        $filename = $message->attachmentFilename($part, $index);

        return response()->streamDownload(function () use ($part): void {
            echo $part->getBinaryContentStream()->getContents();
        }, $filename, [
            'Content-Type' => $part->getContentType('application/octet-stream'),
        ]);
    }
}
