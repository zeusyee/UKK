<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Menambahkan kolom tambahan ke tabel cards yang sudah ada
        Schema::table('cards', function (Blueprint $table) {
            if (!Schema::hasColumn('cards', 'assigned_to')) {
                $table->integer('assigned_to')->nullable()->after('created_by');
                $table->foreign('assigned_to')->references('user_id')->on('users')->onDelete('set null');
            }
        });

        // Drop & recreate constraint status
        DB::statement("ALTER TABLE cards DROP CONSTRAINT chk_card_status");
        DB::statement("ALTER TABLE cards ADD CONSTRAINT chk_card_status CHECK (status IN ('todo', 'in_progress', 'review', 'done', 'blocked'))");

        // Drop & recreate constraint priority
        DB::statement("ALTER TABLE cards DROP CONSTRAINT chk_card_priority");
        DB::statement("ALTER TABLE cards ADD CONSTRAINT chk_card_priority CHECK (priority IN ('low', 'medium', 'high', 'urgent'))");
    }

    public function down()
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn('assigned_to');
        });

        // Drop & restore original constraints
        DB::statement("ALTER TABLE cards DROP CONSTRAINT chk_card_status");
        DB::statement("ALTER TABLE cards DROP CONSTRAINT chk_card_priority");

        DB::statement("ALTER TABLE cards ADD CONSTRAINT chk_card_status CHECK (status IN ('todo', 'in_progress', 'review', 'done'))");
        DB::statement("ALTER TABLE cards ADD CONSTRAINT chk_card_priority CHECK (priority IN ('low', 'medium', 'high'))");
    }
};
