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
        Schema::table('amazon_data', function (Blueprint $table) {
            //
            $table->string("file_type")->after('type')->comment("1: 2:  9:その他");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('amazon_data', function (Blueprint $table) {
            //
        });
    }
};
