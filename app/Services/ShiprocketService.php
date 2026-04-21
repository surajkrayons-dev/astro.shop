<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShiprocketService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.shiprocket.base_url');
    }

    public function getToken()
    {
        $response = Http::post($this->baseUrl . 'auth/login', [
            'email' => config('services.shiprocket.email'),
            'password' => config('services.shiprocket.password'),
        ]);

        return $response->json()['token'] ?? null;
    }

    public function createOrder($order, $items)
    {
        $token = $this->getToken();

        $orderItems = [];

        foreach ($items as $item) {
            $orderItems[] = [
                "name" => $item->product->name ?? 'Product',
                "sku" => $item->product->slug ?? 'sku',
                "units" => $item->quantity,
                "selling_price" => $item->price_at_time,
            ];
        }

        return Http::withToken($token)->post($this->baseUrl . 'orders/create/adhoc', [
            "order_id" => $order->order_number,
            "order_date" => now(),
            "pickup_location" => "work",

            "billing_customer_name" => $order->name,
            "billing_last_name" => ".",
            "billing_address" => $order->address,
            "billing_city" => $order->city, 
            "billing_pincode" => $order->pincode,
            "billing_state" => $order->state,
            "billing_country" => $order->country,
            "billing_email" => $order->email ?? optional($order->user)->email ?? 'noreply@yourdomain.com',
            "billing_phone" => $order->mobile,

            "shipping_is_billing" => true, 

            "order_items" => $orderItems,

            "payment_method" => "Prepaid",
            "sub_total" => $order->total_amount,

            "length" => 10,
            "breadth" => 10,
            "height" => 10,
            "weight" => 0.5
        ])->json();
    }

    public function assignAwb($shipmentId)
    {
        $token = $this->getToken();

        return Http::withToken($token)->post($this->baseUrl . 'courier/assign/awb', [
            "shipment_id" => $shipmentId
        ])->json();
    }

    public function generatePickup($shipmentId)
    {
        $token = $this->getToken();

        return Http::withToken($token)->post($this->baseUrl . 'courier/generate/pickup', [
            "shipment_id" => [$shipmentId]
        ])->json();
    }
}