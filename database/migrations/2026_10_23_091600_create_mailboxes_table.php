<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailboxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('port')->unique();
            $table->timestamps();
        });

        $now = now();
        $mailboxId = DB::table('mailboxes')->insertGetId([
            'name' => 'General',
            'slug' => 'general',
            'port' => 2525,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Schema::table('messages', function (Blueprint $table) use ($mailboxId) {
            $table->foreignId('mailbox_id')
                ->default($mailboxId)
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mailbox_id');
        });

        Schema::dropIfExists('mailboxes');
    }
};
