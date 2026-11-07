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
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            $table->string('status', 20)->default('active')->change();
            $table->dropUnique('users_email_unique');
        });

        // Partial unique index: only non-deleted users must have unique emails
        // Prevents collision when a soft-deleted email is re-registered (PostgreSQL & SQLite)
        DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email) WHERE deleted_at IS NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_email_unique;');

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['two_factor_secret', 'two_factor_confirmed_at']);
            $table->unique('email', 'users_email_unique');
        });
    }
};
