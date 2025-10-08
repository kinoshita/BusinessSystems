<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmazonItem extends Model
{
    //
    use HasFactory;
    protected $table = 'amazon_data';
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

    public function csvExchangeHeader() : array
    {
        return [
            "buyer-name",	"ship-postal-code",	"recipient-name", "kari-sama",	"ship-state",
            "ship-address-1", "ship-address-2", "ship-address-3", "内容品", 	"quantity-to-ship",	"product-name"
        ];
    }

    public function csvExchangeHeader2() : array
    {
        return [
            "buyer-name","buyer-phone-number",	"ship-postal-code",	"recipient-name", 	"ship-state",
            "ship-address-1", "ship-address-2", "ship-address-3", "内容品", 	"quantity-to-ship",	"product-name"
        ];
    }




}
