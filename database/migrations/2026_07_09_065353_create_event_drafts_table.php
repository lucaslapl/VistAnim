<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->string('draft_label');
            $table->longText('draft_data');
            $table->timestamps();

            $table->index('organizer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_drafts');
    }
};
