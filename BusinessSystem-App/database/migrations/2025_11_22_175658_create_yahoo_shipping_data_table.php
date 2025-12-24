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
        Schema::create('yahoo_shipping_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('OrderId');
	        $table->bigInteger('execute_yahoo_id');
            $table->bigInteger('LineId');
            $table->string("ItemId");
            $table->string("Title");
            $table->string("SubCode");
            $table->string("Quantity");
            $table->string("content");
            $table->string("file_type");
            $table->string("type");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yahoo_shipping_data');
    }
};
