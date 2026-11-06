<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commentaires', function (Blueprint $table): void {
            $table->id();
            $table->text('texte');
            $table->foreignId('auteur_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('date')->useCurrent();
            $table->foreignId('activite_id')->constrained('activites')->cascadeOnDelete();
            $table->index(['activite_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commentaires');
    }
};
