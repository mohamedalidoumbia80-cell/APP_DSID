<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activites', function (Blueprint $table): void {
            $table->timestamp('late_notified_at')->nullable()->after('validated_by');
        });
    }

    public function down(): void
    {
        Schema::table('activites', function (Blueprint $table): void {
            $table->dropColumn('late_notified_at');
        });
    }
};
