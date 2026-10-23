<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Mailbox extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'port',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function host(): string
    {
        return $this->slug.'.localhost';
    }

    public function processAlias(): string
    {
        return 'smtp-'.$this->id;
    }

    public static function resolveId(?int $mailboxId): ?int
    {
        if ($mailboxId !== null && static::query()->whereKey($mailboxId)->exists()) {
            return $mailboxId;
        }

        $id = static::query()->where('slug', 'general')->value('id')
            ?? static::query()->orderBy('id')->value('id');

        return $id === null ? null : (int) $id;
    }

    public static function nextPort(): int
    {
        return max(2525, ((int) static::query()->max('port')) + 1);
    }

    public static function createFromName(string $name): self
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'buzon';
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return static::query()->create([
            'name' => $name,
            'slug' => $slug,
            'port' => static::nextPort(),
        ]);
    }
}
