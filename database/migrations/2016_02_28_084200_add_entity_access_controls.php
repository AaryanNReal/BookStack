<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ---- IMAGES ----
        if (Schema::hasTable('images') && !Schema::hasColumn('images', 'uploaded_to')) {
            Schema::table('images', function (Blueprint $table) {
                $table->integer('uploaded_to')->nullable()->default(0);
                $table->index('uploaded_to');
            });
        }

        // ---- BOOKS ----
        if (Schema::hasTable('books') && !Schema::hasColumn('books', 'restricted')) {
            Schema::table('books', function (Blueprint $table) {
                $table->boolean('restricted')->nullable()->default(false);
                $table->index('restricted');
            });
        }

        // ---- CHAPTERS ----
        if (Schema::hasTable('chapters') && !Schema::hasColumn('chapters', 'restricted')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->boolean('restricted')->nullable()->default(false);
                $table->index('restricted');
            });
        }

        // ---- PAGES ----
        if (Schema::hasTable('pages') && !Schema::hasColumn('pages', 'restricted')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->boolean('restricted')->nullable()->default(false);
                $table->index('restricted');
            });
        }

        // ---- RESTRICTIONS TABLE ----
        if (!Schema::hasTable('restrictions')) {
            Schema::create('restrictions', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('restrictable_id')->nullable();
                $table->string('restrictable_type')->nullable();
                $table->integer('role_id')->nullable();
                $table->string('action')->nullable();
                $table->index('role_id');
                $table->index('action');
                $table->index(['restrictable_id', 'restrictable_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('images') && Schema::hasColumn('images', 'uploaded_to')) {
            Schema::table('images', function (Blueprint $table) {
                $table->dropColumn('uploaded_to');
            });
        }

        foreach (['books', 'chapters', 'pages'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'restricted')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('restricted');
                });
            }
        }

        if (Schema::hasTable('restrictions')) {
            Schema::drop('restrictions');
        }
    }
};
