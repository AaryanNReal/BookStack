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
        $tables = ['books', 'chapters', 'pages'];

        try {
            $driver = DB::getDriverName();

            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                if ($driver === 'sqlite') {
                    // SQLite cannot drop indexed columns
                    info("Skipping drop of 'restricted' column on {$table} (SQLite limitation)");
                    continue;
                }

                // Drop the column safely on non-SQLite DBs
                if (Schema::hasColumn($table, 'restricted')) {
                    Schema::table($table, function (Blueprint $t) use ($table) {
                        $t->dropColumn('restricted');
                    });
                }
            }
        } catch (\Exception $e) {
            info('Skipping drop_entity_restricted_field migration: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['books', 'chapters', 'pages'];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            try {
                $driver = DB::getDriverName();

                if ($driver === 'sqlite') {
                    // SQLite can’t re-add easily, skip
                    info("Skipping re-add of 'restricted' column on {$table} (SQLite limitation)");
                    continue;
                }

                if (!Schema::hasColumn($table, 'restricted')) {
                    Schema::table($table, function (Blueprint $t) {
                        $t->boolean('restricted')->default(false);
                        $t->index('restricted');
                    });
                }
            } catch (\Exception $e) {
                info("Skipping reverse migration for {$table}: " . $e->getMessage());
            }
        }
    }
};
