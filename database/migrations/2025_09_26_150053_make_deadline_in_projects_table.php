<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            // Hapus end_date dan tambahkan deadline
            $table->dropColumn('end_date');
            $table->date('deadline')->nullable()->after('start_date');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('deadline');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }
};