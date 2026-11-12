<?php

declare(strict_types=1);

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
        Schema::create('meeting_reminders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->integer('remind_before_minutes')->default(15);
            $table->string('channel')->default('telegram')->index();
            $table->boolean('is_sent')->default(false)->index();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->index(['is_sent', 'remind_before_minutes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_reminders');
    }
};
