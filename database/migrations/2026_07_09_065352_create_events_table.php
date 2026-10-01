<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('event_date');
            $table->string('location', 200)->nullable();
            $table->string('rdv_point', 200)->nullable();
            $table->string('event_duration', 100)->nullable();
            $table->string('audience_type', 100)->nullable();
            $table->unsignedInteger('max_participants')->nullable();
            $table->unsignedInteger('min_participants')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->text('price_details')->nullable();
            $table->decimal('price_amount', 8, 2)->nullable();
            $table->string('image', 100)->nullable();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index('event_date');
            $table->index('organizer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
