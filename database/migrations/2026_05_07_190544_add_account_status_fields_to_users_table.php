<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('restricted_at')->nullable()->after('warning_count');
            $table->foreignId('restricted_by')->nullable()->after('restricted_at')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['restricted_by']);
            $table->dropColumn(['restricted_at', 'restricted_by']);
            $table->dropSoftDeletes();
        });
    }
};
