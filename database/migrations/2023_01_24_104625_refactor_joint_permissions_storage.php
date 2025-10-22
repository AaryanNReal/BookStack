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
                info('Skipping drop of has_permission column on joint_permissions (SQLite limitation)');
                return;
            }

            // Drop old columns on non-SQLite DBs
            Schema::table('joint_permissions', function (Blueprint $table) {
                if (Schema::hasColumn('joint_permissions', 'has_permission')) {
                    $table->dropColumn('has_permission');
                }
                if (Schema::hasColumn('joint_permissions', 'has_permission_own')) {
                    $table->dropColumn('has_permission_own');
                }
            });
        } catch (\Exception $e) {
            info('Skipping refactor_joint_permissions_storage migration due to DB limitation: ' . $e->getMessage());
        }

        // Create new simplified permissions structure (safe for all)
        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('role_id');
                $table->string('permission');
                $table->timestamps();

                $table->index('role_id');
                $table->index('permission');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                info('Skipping reverse of refactor_joint_permissions_storage (SQLite limitation)');
                return;
            }

            Schema::table('joint_permissions', function (Blueprint $table) {
                if (!Schema::hasColumn('joint_permissions', 'has_permission')) {
                    $table->boolean('has_permission')->default(false);
                }
                if (!Schema::hasColumn('joint_permissions', 'has_permission_own')) {
                    $table->boolean('has_permission_own')->default(false);
                }
            });

            Schema::dropIfExists('role_permissions');
        } catch (\Exception $e) {
            info('Skipping reverse migration for refactor_joint_permissions_storage: ' . $e->getMessage());
        }
    }
};
