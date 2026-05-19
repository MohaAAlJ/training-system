<?php

use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mailboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'sender_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->tinyInteger('target_type')->default(0); // 0=all, 1=roles, 2=individual
            $table->json('target_roles')->nullable();        // mode 1: array of role ints
            $table->foreignIdFor(User::class, 'target_user_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('status')->default(Mailbox::STATUS_DRAFT);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('mailbox_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Mailbox::class, 'mailbox_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailbox_user');
        Schema::dropIfExists('mailboxes');
    }
};
