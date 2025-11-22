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
        Schema::create('rakuten_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("execute_rakuten_id");
            $table->string("order_id");
            $table->string("order_last_name");
            $table->string("order_first_name");
            $table->string("post_code_1");
            $table->string("post_code_2");
            $table->string("destination_last_name");
            $table->string("destination_first_name");
            $table->string("prefectures");
            $table->string("city");
            $table->string("address");
            $table->string("telephone_number_1");
            $table->string("telephone_number_2");
            $table->string("telephone_number_3");
            $table->string("quantity");
            $table->string("product_name");
            $table->string("unit_price");
            $table->string("total_product_amount");
            $table->string("content")->comment("内容品がないので、商品と対になるように内容品と定義する");
            $table->string("file_type")->comment("1:クリックポスト,2:レターパック,3:ヤマト運輸");
            $table->string("type");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rakuten_data');
    }
};
