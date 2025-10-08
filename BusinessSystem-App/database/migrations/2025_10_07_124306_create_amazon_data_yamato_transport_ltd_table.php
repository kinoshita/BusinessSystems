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
        Schema::create('amazon_data_yamato_transport_ltd', function (Blueprint $table) {
            $table->id();
	    $table->bigInteger("execute_id");
            $table->string("order-id")->comment("");
            $table->string("order-item-id")->comment("");
            $table->string("purchase-date")->comment("");
            $table->string("payments-date")->comment("");
            $table->string("reporting-date")->comment("");
            $table->string("promise-date")->comment("");
            $table->string("days-past-promise")->comment("");
            $table->string("buyer-email")->comment("");
            $table->string("buyer-name")->comment("");
            $table->string("buyer-phone-number")->comment("");

            $table->string("sku")->comment("");
            $table->string("product-name")->comment("");
            $table->string("quantity-purchased")->comment("");
            $table->string("quantity-shipped")->comment("");
            $table->string("quantity-to-ship")->comment("");
            $table->string("ship-service-level")->comment("");
            $table->string("recipient-name")->comment("");
            $table->string("ship-address-1")->comment("");
            $table->string("ship-address-2")->comment("");
            $table->string("ship-address-3")->comment("");

            $table->string("ship-city")->comment("");
            $table->string("ship-state")->comment("");
            $table->string("ship-postal-code")->comment("");
            $table->string("ship-country")->comment("");
            $table->string("payment-method")->comment("");
            $table->string("cod-collectible-amount")->comment("");
            $table->string("already-paid")->comment("");
            $table->string("payment-method-fee")->comment("");
            $table->string("scheduled-delivery-start-date")->comment("");
            $table->string("scheduled-delivery-end-date")->comment("");

            $table->string("points-granted")->comment("");
            $table->string("is-prime")->comment("");
            $table->string("verge-of-cancellation")->comment("");
            $table->string("verge-of-lateShipment")->comment("");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amazon_data_yamato_transport_ltd');
    }
};
