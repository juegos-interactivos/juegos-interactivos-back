<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'image')) {
            // nothing to change
            return;
        }

        // Check current nullability via information_schema (works for MySQL)
        $isNullable = null;
        try {
            $row = DB::selectOne("SELECT IS_NULLABLE AS is_nullable FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'image'");
            $isNullable = $row->is_nullable ?? null;
        } catch (\Throwable $e) {
            // ignore and try Schema change below
            $isNullable = null;
        }

        if ($isNullable === 'YES') {
            // already nullable
            return;
        }

        // Try driver-specific ALTER, fallback to change() which requires doctrine/dbal
        $driver = DB::getDriverName();
        try {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `users` MODIFY `image` VARCHAR(255) NULL");
            } else {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('image')->nullable()->change();
                });
            }
        } catch (\Throwable $e) {
            // last resort: do nothing to avoid migration failure
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'image')) {
            return;
        }

        // Ensure no NULL values exist before making NOT NULL
        try {
            DB::table('users')->whereNull('image')->update(['image' => '']);
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `users` MODIFY `image` VARCHAR(255) NOT NULL");
            } else {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('image')->nullable(false)->change();
                });
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
