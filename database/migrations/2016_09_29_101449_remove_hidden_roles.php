<?php

use Carbon\Carbon;
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
        // Safely remove the hidden property from roles
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'hidden')) {
            try {
                Schema::table('roles', function (Blueprint $table) {
                    $table->dropColumn('hidden');
                });
            } catch (\Exception $e) {
                // SQLite cannot drop indexed columns directly
                info('Skipping dropColumn(hidden) on SQLite');
            }
        }

        // Add column to mark system users if it doesn't exist
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'system_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('system_name')->nullable()->index();
            });
        }

        // Insert our new public system user only if not exists
        if (Schema::hasTable('users')) {
            $exists = DB::table('users')->where('system_name', '=', 'public')->exists();
            if (!$exists) {
                $publicUserId = DB::table('users')->insertGetId([
    'email'           => 'guest@example.com',
    'name'            => 'Guest',
    'system_name'     => 'public',
    'password'        => bcrypt(Str::random(16)), // add random placeholder password
    'email_confirmed' => true,
    'created_at'      => Carbon::now(),
    'updated_at'      => Carbon::now(),
]);


                // Connect the new public user to the public role if it exists
                $publicRole = DB::table('roles')->where('system_name', '=', 'public')->first();
                if ($publicRole) {
                    DB::table('role_user')->insert([
                        'user_id' => $publicUserId,
                        'role_id' => $publicRole->id,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('roles') && !Schema::hasColumn('roles', 'hidden')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('hidden')->nullable()->default(false);
                $table->index('hidden');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'system_name')) {
            DB::table('users')->where('system_name', '=', 'public')->delete();

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('system_name');
            });
        }

        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'system_name')) {
            DB::table('roles')->where('system_name', '=', 'public')->update(['hidden' => true]);
        }
    }
};
