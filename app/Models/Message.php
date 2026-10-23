<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ZBateson\MailMimeParser\Message as ParsedMessage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ZBateson\MailMimeParser\Header\ParameterHeader;
use ZBateson\MailMimeParser\IMessage as ParsedMessageContract;
use ZBateson\MailMimeParser\Message\IMessagePart;

/**
 * @property ParsedMessageContract $parsed
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'bookmarked',
        'content',
        'read_at',
        'mailbox_id',
    ];

    protected $casts = [
        'read_at' => 'immutable_datetime',
        'bookmarked' => 'boolean',
    ];

    public static function fromContent(string $content, int $mailboxId): self
    {
        return self::create([
            'content' => $content,
            'mailbox_id' => $mailboxId,
        ]);
    }

    public function mailbox(): BelongsTo
    {
        return $this->belongsTo(Mailbox::class);
    }

    //---------------------------------------------------------------
    // Scope
    //---------------------------------------------------------------
    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    //---------------------------------------------------------------
    // Attributes
    //---------------------------------------------------------------
    public function parsed(): Attribute
    {
        return Attribute::make(
            get: fn (): ParsedMessageContract => ParsedMessage::from($this->content, true)
        )->shouldCache();
    }

    public function size(): Attribute
    {
        // NOTE: Number::fileSize doesn't work since NativePHP is missing the 'intl' extension
        return Attribute::make(
            get: function () {
                $base = log(strlen($this->content)) / log(1024);
                $suffix = ['', 'KB', 'MB', 'GB', 'TB'];
                $floor = floor($base);

                return round(pow(1024, $base - $floor), 1) . $suffix[$floor];
            }
        )->shouldCache();
    }

    //---------------------------------------------------------------
    // Helpers
    //---------------------------------------------------------------
    public function toggleBookmark(): void
    {
        $this->update([
            'bookmarked' => ! $this->bookmarked,
        ]);
    }

    /**
     * @return list<array{index: int, filename: string, mime: string}>
     */
    public function attachments(): array
    {
        return collect($this->parsed->getAllAttachmentParts())
            ->map(function (IMessagePart $part, int $index): array {
                return [
                    'index' => $index,
                    'filename' => $this->attachmentFilename($part, $index),
                    'mime' => $part->getContentType('application/octet-stream'),
                ];
            })
            ->all();
    }

    public function attachmentFilename(IMessagePart $part, int $index): string
    {
        $name = $part->getFilename()
            ?: $this->headerParameter($part, 'Content-Disposition', 'name')
            ?: $this->headerParameter($part, 'Content-Type', 'name')
            ?: 'attachment-'.($index + 1);

        return str_replace(["\0", '/', '\\'], '-', $name);
    }

    public function markRead(): void
    {
        if ($this->read_at) {
            return;
        }

        $this->update([
            'read_at' => now(),
        ]);
    }

    private function headerParameter(IMessagePart $part, string $header, string $name): ?string
    {
        $value = $part->getHeader($header);

        if (! $value instanceof ParameterHeader) {
            return null;
        }

        $parameter = $value->getValueFor($name);

        return $parameter !== null && $parameter !== '' ? $parameter : null;
    }
}
