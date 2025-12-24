<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    // yahooダウンロードデータ clicに相当

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('yahoo_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("execute_yahoo_id");
            $table->bigInteger("OrderId");
            $table->string("BillName");
            $table->string("ShipZipCode");
            $table->string("ShipName");
            $table->string("ShipPrefecture");
            $table->string("ShipCity");
            $table->string("ShipAddress1");
            $table->string("ShipAddress2");
            $table->string("ShipSection1");
            $table->string("ShipSection2");
            $table->string("ShipPhoneNumber");
            $table->string("QuantityDetail");
            $table->string("BillMailAddress");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yahoo_data');
    }
};
