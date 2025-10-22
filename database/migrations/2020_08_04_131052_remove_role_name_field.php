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
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                // SQLite cannot drop indexed columns like 'name'
                info('Skipping dropColumn("name") on roles due to SQLite limitations');
            } else {
                Schema::table('roles', function (Blueprint $table) {
                    $table->dropColumn('name');
                });
            }
        } catch (\Exception $e) {
            info('Skipping drop of name column on roles due to DB limitation: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        try {
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                // SQLite cannot re-add columns with unique constraints directly
                info('Skipping re-add of name column on roles (SQLite limitation)');
            } else {
                Schema::table('roles', function (Blueprint $table) {
                    $table->string('name')->unique()->nullable();
                });
            }
        } catch (\Exception $e) {
            info('Skipping re-add of name column due to DB limitation: ' . $e->getMessage());
        }
    }
};
