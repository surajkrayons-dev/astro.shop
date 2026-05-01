<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\ShipwayService;

class SyncShipwayOrders extends Command
{
    protected $signature = 'sync:shipway';
    protected $description = 'Sync Shipway Orders';

    public function handle()
    {
        $orders = Order::whereNull('awb_code')
            ->whereIn('shipping_status', ['pending','created'])
            ->limit(20)
            ->get();

        foreach ($orders as $order) {

            $response = ShipwayService::getOrderDetails($order);

            if (!$response) continue;

            // 👇 response structure adjust karna pad sakta hai
            $data = $response['data'] ?? null;

            if (!$data) continue;

            $order->update([
                'awb_code' => $data['awb_code'] ?? $order->awb_code,
                'shipment_id' => $data['shipment_id'] ?? $order->shipment_id,
                'courier_name' => $data['courier_name'] ?? $order->courier_name,
                'shipping_status' => $data['status'] ?? $order->shipping_status,
            ]);
        }

        $this->info('Shipway sync done');

        return 0;
    }
}