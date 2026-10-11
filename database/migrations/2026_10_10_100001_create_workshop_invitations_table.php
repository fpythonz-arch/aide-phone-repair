<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invitations de techniciens : lien à usage unique créé par le responsable d'un atelier.
 * Seul l'empreinte (hash) du jeton est stockée : le jeton lui-même n'est montré qu'une fois.
 *
 * Idempotente : peut être rejouée après docs/sql/lot4-invitations.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workshop_invitations')) {
            return;
        }

        Schema::create('workshop_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained('workshops')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('role');
            $table->string('email')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index('workshop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_invitations');
    }
};
