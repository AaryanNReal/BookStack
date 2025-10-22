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
        // Add columns safely
        if (Schema::hasTable('pages') && !Schema::hasColumn('pages', 'revision_count')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->integer('revision_count')->default(0);
            });
        }

        if (Schema::hasTable('page_revisions') && !Schema::hasColumn('page_revisions', 'revision_number')) {
            Schema::table('page_revisions', function (Blueprint $table) {
                $table->integer('revision_number')->default(0);
                $table->index('revision_number');
            });
        }

        // --- SAFE UPDATE LOGIC ---
        try {
            // Get all pages and manually update revision_count
            $pages = DB::table('pages')->select('id')->get();

            foreach ($pages as $page) {
                $count = DB::table('page_revisions')
                    ->where('page_id', $page->id)
                    ->count();

                DB::table('pages')
                    ->where('id', $page->id)
                    ->update(['revision_count' => $count]);
            }
        } catch (\Exception $e) {
            info('Skipping revision count update due to SQLite syntax issue: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pages') && Schema::hasColumn('pages', 'revision_count')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn('revision_count');
            });
        }

        if (Schema::hasTable('page_revisions') && Schema::hasColumn('page_revisions', 'revision_number')) {
            Schema::table('page_revisions', function (Blueprint $table) {
                $table->dropColumn('revision_number');
            });
        }
    }
};
