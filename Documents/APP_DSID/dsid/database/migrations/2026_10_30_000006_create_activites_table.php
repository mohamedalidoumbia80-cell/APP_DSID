<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activites', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('type', 30)->index();
            $table->date('start_date');
            $table->date('due_date')->nullable()->index();
            $table->string('status', 30)->default('a_demarrer')->index();
            $table->unsignedSmallInteger('progress')->default(0);
            $table->string('priority', 20)->default('moyenne')->index();
            $table->foreignId('domaine_id')->constrained('domaines')->restrictOnDelete();
            $table->foreignId('responsable_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('axe_sdsi_id')->nullable()->constrained('axes_sdsi')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'due_date']);
            $table->index(['domaine_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activites');
    }
};
