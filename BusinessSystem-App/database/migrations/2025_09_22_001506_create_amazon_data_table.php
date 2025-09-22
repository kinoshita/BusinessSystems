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
        Schema::create('amazon_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("execute_id");
            $table->string("buyer-name");
            $table->string("ship-postal-code");
            $table->string("recipient-name");
            $table->string("ship-state");
            $table->string("ship-address-1");
            $table->string("ship-address-2")->nullable();
            $table->string("ship-address-3")->nullable();
            $table->string("内容品")->nullable();
            $table->string("quantity-to-ship");
            $table->string("product-name");
            $table->string("type")->comment("1: 2:  9:その他");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amazon_data');
    }
};
