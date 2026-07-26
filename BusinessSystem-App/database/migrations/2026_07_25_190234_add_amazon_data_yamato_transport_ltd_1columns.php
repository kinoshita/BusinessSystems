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
        Schema::table('amazon_data_yamato_transport_ltd', function (Blueprint $table) {
            $table->string("内容品")
                ->nullable()
                ->after('product-name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('amazon_data_yamato_transport_ltd', function (Blueprint $table) {
            //
        });
    }
};
