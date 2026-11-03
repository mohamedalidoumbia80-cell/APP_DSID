<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activites', function (Blueprint $table): void {
            $table->timestamp('validated_at')->nullable()->after('axe_sdsi_id');
            $table->foreignId('validated_by')->nullable()->after('validated_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activites', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('validated_by');
            $table->dropColumn('validated_at');
        });
    }
};
