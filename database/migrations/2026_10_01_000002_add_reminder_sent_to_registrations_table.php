<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Rappel J-1 suivi par inscription : les inscriptions créées
            // après le récapitulatif organisateur doivent aussi être rappelées.
            $table->boolean('reminder_sent')->default(false)->after('consent');
            $table->index(['event_id', 'reminder_sent']);
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'reminder_sent']);
            $table->dropColumn('reminder_sent');
        });
    }
};
