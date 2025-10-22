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
        if (!Schema::hasTable('users')) {
            return;
        }

        try {
            if (!Schema::hasColumn('users', 'slug')) {
                Schema::table('users', function (Blueprint $table) {
                    // SQLite requires nullable columns when adding new fields
                    $table->string('slug')->nullable()->index();
                });

                // Fill slug values for existing users
                $users = DB::table('users')->select('id', 'name')->get();

                foreach ($users as $user) {
                    $slug = Str::slug($user->name ?? 'user-' . $user->id);
                    DB::table('users')->where('id', $user->id)->update(['slug' => $slug]);
                }

                // If possible, make slug non-nullable after data is populated
                try {
                    Schema::table('users', function (Blueprint $table) {
                        $table->string('slug')->nullable(false)->change();
                    });
                } catch (\Exception $e) {
                    // SQLite can't modify constraints easily — ignore
                    info('Skipping non-nullable slug constraint on SQLite');
                }
            }
        } catch (\Exception $e) {
            info('Skipping add_user_slug migration for SQLite: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'slug')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
