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
        if (!Schema::hasTable('joint_permissions')) {
            return;
        }

        try {
            // Detect if we're running SQLite
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                // SQLite cannot drop a primary key column, skip this safely
                info('Skipping drop of primary key column "id" on joint_permissions (SQLite does not support it)');
            } else {
                Schema::table('joint_permissions', function (Blueprint $table) {
                    $table->dropColumn('id');
                });
            }
        } catch (\Exception $e) {
            info('Skipping drop of id column on joint_permissions due to DB limitation: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('joint_permissions')) {
            return;
        }

        try {
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                // SQLite cannot add PK columns easily; skip
                info('Skipping recreation of id column (SQLite limitation)');
            } else {
                Schema::table('joint_permissions', function (Blueprint $table) {
                    $table->increments('id')->first();
                });
            }
        } catch (\Exception $e) {
            info('Skipping recreation of id column due to DB limitation: ' . $e->getMessage());
        }
    }
};
