<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        try {
            if (!Schema::hasColumn('roles', 'mfa_enforced')) {
                Schema::table('roles', function (Blueprint $table) {
                    // Add as nullable to avoid SQLite NOT NULL issue
                    $table->boolean('mfa_enforced')->nullable()->default(false);
                });

                // Populate sensible default
                DB::table('roles')->update(['mfa_enforced' => false]);

                // Try to make it non-nullable afterward (safe on MySQL only)
                try {
                    Schema::table('roles', function (Blueprint $table) {
                        $table->boolean('mfa_enforced')->default(false)->nullable(false)->change();
                    });
                } catch (\Exception $e) {
                    info('Skipping NOT NULL enforcement for SQLite: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            info('Skipping add_mfa_enforced_to_roles_table migration: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'mfa_enforced')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('mfa_enforced');
            });
        }
    }
};
