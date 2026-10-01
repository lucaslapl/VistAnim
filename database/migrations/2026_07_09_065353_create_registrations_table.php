<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('firstname', 100);
            $table->string('lastname', 100);
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->unsignedTinyInteger('nb_participants')->default(1);
            $table->string('token', 64)->unique();
            $table->timestamp('registered_at')->useCurrent();
            $table->boolean('consent')->default(false);
            $table->string('user_ip', 45)->nullable();
            $table->string('payment_status', 20)->nullable();
            $table->string('payment_intent_id', 100)->nullable();
            $table->timestamps();

            $table->index('event_id');
            $table->index('email');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
