<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ---- JOINT PERMISSIONS TABLE ----
        if (!Schema::hasTable('joint_permissions')) {
            Schema::create('joint_permissions', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('role_id');
                $table->string('entity_type');
                $table->integer('entity_id');
                $table->string('action');
                $table->boolean('has_permission')->default(false);
                $table->boolean('has_permission_own')->default(false);
                $table->integer('created_by');
                // Create indexes
                $table->index(['entity_id', 'entity_type']);
                $table->index('has_permission');
                $table->index('has_permission_own');
                $table->index('role_id');
                $table->index('action');
                $table->index('created_by');
            });
        }

        // ---- ROLES TABLE CHANGES ----
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                // Add only if missing
                if (!Schema::hasColumn('roles', 'system_name')) {
                    $table->string('system_name')->nullable()->index();
                }
                if (!Schema::hasColumn('roles', 'hidden')) {
                    $table->boolean('hidden')->nullable()->default(false);
                    $table->index('hidden');
                }
            });
        }

        // ---- TABLE RENAMES ----
        if (Schema::hasTable('permissions') && !Schema::hasTable('role_permissions')) {
            Schema::rename('permissions', 'role_permissions');
        }
        if (Schema::hasTable('restrictions') && !Schema::hasTable('entity_permissions')) {
            Schema::rename('restrictions', 'entity_permissions');
        }

        // ---- SEED PUBLIC ROLE ----
        if (Schema::hasTable('roles')) {
            $publicRoleData = [
                'name'         => 'public',
                'display_name' => 'Public',
                'description'  => 'The role given to public visitors if allowed',
                'system_name'  => 'public',
                'hidden'       => true,
                'created_at'   => Carbon::now()->toDateTimeString(),
                'updated_at'   => Carbon::now()->toDateTimeString(),
            ];

            // Ensure unique name
            while (DB::table('roles')->where('name', '=', $publicRoleData['display_name'])->count() > 0) {
                $publicRoleData['display_name'] = $publicRoleData['display_name'] . Str::random(2);
            }

            $publicRoleId = DB::table('roles')->insertGetId($publicRoleData);

            // Add new view permissions to public role
            if (Schema::hasTable('role_permissions') && Schema::hasTable('permission_role')) {
                $entities = ['Book', 'Page', 'Chapter'];
                $ops = ['View All', 'View Own'];

                foreach ($entities as $entity) {
                    foreach ($ops as $op) {
                        $name = strtolower($entity) . '-' . strtolower(str_replace(' ', '-', $op));
                        $permission = DB::table('role_permissions')->where('name', '=', $name)->first();

                        if ($permission) {
                            DB::table('permission_role')->insert([
                                'permission_id' => $permission->id,
                                'role_id'       => $publicRoleId,
                            ]);
                        }
                    }
                }
            }

            // Update admin role with system name if it exists
            DB::table('roles')->where('name', '=', 'admin')->update(['system_name' => 'admin']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('joint_permissions')) {
            Schema::drop('joint_permissions');
        }

        if (Schema::hasTable('role_permissions') && !Schema::hasTable('permissions')) {
            Schema::rename('role_permissions', 'permissions');
        }

        if (Schema::hasTable('entity_permissions') && !Schema::hasTable('restrictions')) {
            Schema::rename('entity_permissions', 'restrictions');
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('system_name', '=', 'public')->delete();

            Schema::table('roles', function (Blueprint $table) {
                if (Schema::hasColumn('roles', 'system_name')) {
                    $table->dropColumn('system_name');
                }
                if (Schema::hasColumn('roles', 'hidden')) {
                    $table->dropColumn('hidden');
                }
            });
        }
    }
};
