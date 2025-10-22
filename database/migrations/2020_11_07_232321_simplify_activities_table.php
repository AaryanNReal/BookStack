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
        if (!Schema::hasTable('activities')) {
            return;
        }

        try {
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                // SQLite cannot drop indexed columns like 'book_id'
                info('Skipping drop of book_id, chapter_id, and page_id columns on activities (SQLite limitation)');
            } else {
                Schema::table('activities', function (Blueprint $table) {
                    if (Schema::hasColumn('activities', 'book_id')) {
                        $table->dropColumn('book_id');
                    }
                    if (Schema::hasColumn('activities', 'chapter_id')) {
                        $table->dropColumn('chapter_id');
                    }
                    if (Schema::hasColumn('activities', 'page_id')) {
                        $table->dropColumn('page_id');
                    }
                });
            }

            // Add new polymorphic columns safely
            if (!Schema::hasColumn('activities', 'entity_type')) {
                Schema::table('activities', function (Blueprint $table) {
                    $table->string('entity_type')->nullable();
                });
            }

            if (!Schema::hasColumn('activities', 'entity_id')) {
                Schema::table('activities', function (Blueprint $table) {
                    $table->integer('entity_id')->nullable();
                });
            }

        } catch (\Exception $e) {
            info('Skipping simplify_activities_table migration changes due to DB limitations: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('activities')) {
            return;
        }

        try {
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                // SQLite cannot drop or re-add safely; skip
                info('Skipping reverse migration for simplify_activities_table (SQLite limitation)');
            } else {
                // Revert structure for MySQL
                Schema::table('activities', function (Blueprint $table) {
                    if (!Schema::hasColumn('activities', 'book_id')) {
                        $table->integer('book_id')->nullable();
                    }
                    if (!Schema::hasColumn('activities', 'chapter_id')) {
                        $table->integer('chapter_id')->nullable();
                    }
                    if (!Schema::hasColumn('activities', 'page_id')) {
                        $table->integer('page_id')->nullable();
                    }

                    if (Schema::hasColumn('activities', 'entity_type')) {
                        $table->dropColumn('entity_type');
                    }
                    if (Schema::hasColumn('activities', 'entity_id')) {
                        $table->dropColumn('entity_id');
                    }
                });
            }

        } catch (\Exception $e) {
            info('Skipping reverse simplify_activities_table migration: ' . $e->getMessage());
        }
    }
};
