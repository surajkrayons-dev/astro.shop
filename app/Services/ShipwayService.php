<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShipwayService
{
    public static function pushOrder($order)
    {
        try {

            $order->load(['items.product', 'user']);

            $username = trim(env('SHIPWAY_USERNAME'));
            $license  = trim(env('SHIPWAY_LICENSE'));

            $auth = base64_encode($username . ':' . $license);

            $email = $order->email 
                ?? $order->user->email 
                ?? 'customer@email.com';

            $fullName = trim($order->user->name ?? 'Customer');

            $nameParts = preg_split('/\s+/', $fullName, 2);

            $firstName = $nameParts[0] ?? 'Customer';
            $lastName  = $nameParts[1] ?? '';

            $country = $order->country ?? 'India';

            $products = $order->items->map(function ($item) {
                return [
                    "product"          => $item->product_name,
                    "price"            => (string) $item->price,
                    "product_code" => (string) ($item->product->code ?? 'SKU-'.$item->id),
                    "product_quantity" => (string) $item->quantity,
                    "discount"         => "0",
                    "tax_rate"         => "0", // GST already included
                    "tax_title"        => ""
                ];
            })->toArray();

            $body = [

                "order_id" => $order->order_number,

                "products" => $products,

                "discount" => "0",
                "shipping" => (string) $order->delivery_charge,
                "order_total" => (string) $order->total_amount,
                "taxes" => "0",
                "order_notes" => "All taxes (GST) are included in product price. No extra charges applicable.",
                "payment_type" => "P",

                "email" => $email,

                "billing_address"   => $order->address,
                "billing_city"      => $order->city,
                "billing_state"     => $order->state,
                "billing_country"   => $country,
                "billing_firstname" => $firstName,
                "billing_lastname"  => $lastName,
                "billing_phone"     => $order->mobile,
                "billing_zipcode"   => $order->pincode,

                "shipping_address"   => $order->address,
                "shipping_city"      => $order->city,
                "shipping_state"     => $order->state,
                "shipping_country"   => $country,
                "shipping_firstname" => $firstName,
                "shipping_lastname"  => $lastName,
                "shipping_phone"     => $order->mobile,
                "shipping_zipcode"   => $order->pincode,

                "order_weight" => (string) $order->total_weight,
                "box_length"   => (string) $order->box_length,
                "box_breadth"  => (string) $order->box_breadth,
                "box_height"   => (string) $order->box_height,

                "order_date" => now()->format('Y-m-d H:i:s'),
            ];

            $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . $auth,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])
                ->timeout(30)
                ->post('https://app.shipway.com/api/v2orders', $body);

            Log::info('SHIPWAY DEBUG', [
                'order_id' => $order->order_number,
                'email'    => $email,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['success'] ?? false)) {

                $order->update([
                    'shipping_status' => 'pending'
                ]);

                return $data;
            }

            Log::error('SHIPWAY FAILED', $data ?? []);

            $order->update([
                'shipping_status' => 'failed'
            ]);

            return $data;

        } catch (\Exception $e) {

            Log::error('SHIPWAY ERROR', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    public static function cancelShipment($order)
    {
        try {

            $username = trim(env('SHIPWAY_USERNAME'));
            $license  = trim(env('SHIPWAY_LICENSE'));

            $auth = base64_encode($username . ':' . $license);

            $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . $auth,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])
                ->post('https://app.shipway.com/api/Cancelorders/', [
                    "order_ids" => [$order->order_number]
                ]);

            \Log::info('SHIPWAY CANCEL RESPONSE', [
                'order_id' => $order->order_number,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return $response->json();

        } catch (\Exception $e) {
            \Log::error('SHIPWAY CANCEL ERROR', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function getOrderDetails($order)
    {
        try {

            $username = trim(env('SHIPWAY_USERNAME'));
            $license  = trim(env('SHIPWAY_LICENSE'));

            $auth = base64_encode($username . ':' . $license);

            $response = \Http::withHeaders([
                    'Authorization' => 'Basic ' . $auth,
                    'Accept'        => 'application/json',
                ])
                ->get('https://app.shipway.com/api/getOrders', [
                    'order_id' => $order->order_number
                ]);

            \Log::info('SHIPWAY FETCH', [
                'order_id' => $order->order_number,
                'response' => $response->body()
            ]);

            return $response->json();

        } catch (\Exception $e) {
            \Log::error('SHIPWAY FETCH ERROR', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}