<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taches', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->string('status', 30)->default('a_demarrer')->index();
            $table->unsignedSmallInteger('progress')->default(0);
            $table->foreignId('activite_id')->constrained('activites')->restrictOnDelete();
            $table->foreignId('agent_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['activite_id', 'status']);
            $table->index(['agent_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taches');
    }
};
