<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmazonYamatoItem extends Model
{
    //
    use HasFactory;
    protected $table = 'amazon_data_yamato_transport_ltd';
    protected $guarded = [
        'id',
    ];

    public function csvHeader() : array
    {
        return [
            "order-id", "order-item-id", "purchase-date", "payments-date", "reporting-date", "promise-date",
            "days-past-promise", "buyer-email", "buyer-name", "buyer-phone-number", "sku", "product-name",
            "quantity-purchased", "quantity-shipped", "quantity-to-ship", "ship-service-level", "recipient-name",
            "ship-address-1", "ship-address-2", "ship-address-3", "ship-city",	"ship-state",	"ship-postal-code",
            "ship-country",	"payment-method",	"cod-collectible-amount",	"already-paid",	"payment-method-fee",
            "scheduled-delivery-start-date",	"scheduled-delivery-end-date",	"points-granted",	"is-prime",
            "verge-of-cancellation", "verge-of-lateShipment"
        ];
    }

    public function csvHeaderForExcelExport(){
        return [
            "order_id", "order_item_id", "purchase_date", "payments_date", "reporting_date", "promise_date",
            "days_past_promise", "buyer_email", "buyer_name", "buyer_phone_number", "sku", "product_name",
            "quantity_purchased", "quantity_shipped", "quantity_to_ship", "ship_service_level", "recipient_name",
            "ship_address_1", "ship_address_2", "ship_address_3", "ship_city",	"ship_state",	"ship_postal_code",
            "ship_country",	"payment_method",	"cod_collectible_amount",	"already_paid",	"payment_method_fee",
            "scheduled_delivery_start_date",	"scheduled_delivery_end_date",	"points_granted",	"is_prime",
            "verge_of_cancellation", "verge_of_lateShipment"
        ];

    }


}
