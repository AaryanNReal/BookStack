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
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                // SQLite cannot drop indexed columns
                info('Skipping drop of "action" and "has_permission_own" columns on joint_permissions (SQLite limitation)');
            } else {
                Schema::table('joint_permissions', function (Blueprint $table) {
                    if (Schema::hasColumn('joint_permissions', 'action')) {
                        $table->dropColumn('action');
                    }
                    if (Schema::hasColumn('joint_permissions', 'has_permission_own')) {
                        $table->dropColumn('has_permission_own');
                    }
                });
            }
        } catch (\Exception $e) {
            info('Skipping drop_joint_permission_type migration due to DB limitation: ' . $e->getMessage());
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
                // SQLite cannot re-add easily; skip safely
                info('Skipping reverse of drop_joint_permission_type migration (SQLite limitation)');
            } else {
                Schema::table('joint_permissions', function (Blueprint $table) {
                    if (!Schema::hasColumn('joint_permissions', 'action')) {
                        $table->string('action')->nullable();
                    }
                    if (!Schema::hasColumn('joint_permissions', 'has_permission_own')) {
                        $table->boolean('has_permission_own')->default(false);
                    }
                });
            }
        } catch (\Exception $e) {
            info('Skipping reverse drop_joint_permission_type due to DB limitation: ' . $e->getMessage());
        }
    }
};
