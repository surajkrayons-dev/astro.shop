<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserService;
use App\Models\PayoutClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateClientPayouts extends Command
{
    protected $signature = 'payout:client-generate';
    protected $description = 'Generate monthly payouts for clients based on their services';

    public function __construct()
    {
        parent::__construct();
    }

    // public function handle()
    // {
    //     $now = Carbon::now();
    //     $services = \App\Models\Service::where('status', 1)->get();

    //     foreach ($services as $service) {
    //         $created = Carbon::parse($service->created_at);

    //         // 1) Count already-generated payouts for this service
    //         $count = PayoutClient::where('service_id', $service->id)->count();

    //         if ($count === 0) {
    //             // First-ever payout → from created_at to +1 month immediately
    //             $periodStart = $created;
    //             $periodEnd   = $created->copy()->addMonth();
    //         } else {
    //             // Subsequent → roll-forward based on how many exist
    //             $periodStart = $created->copy()->addMonths($count);
    //             $periodEnd   = $periodStart->copy()->addMonth();
    //             // Only if that period has fully passed should we generate
    //             if ($now->lessThan($periodEnd)) {
    //                 continue;
    //             }
    //         }

    //         // Double-check to avoid dupes (shouldn't happen, but safe)
    //         $exists = PayoutClient::where('service_id', $service->id)
    //             ->whereMonth('created_at', $periodEnd->month)
    //             ->whereYear('created_at',  $periodEnd->year)
    //             ->exists();

    //         if ($exists) {
    //             continue;
    //         }

    //         // Create the payout
    //         try {
    //             DB::beginTransaction();

    //             PayoutClient::create([
    //                 'invoice_no'   => $this->generateInvoiceNumber(),
    //                 'client_id'    => $service->client_id,
    //                 'service_id'   => $service->id,
    //                 'service_cost' => $service->service_cost,
    //                 'description'  => 'Payout for ' . $periodStart->toDateString() . ' to ' . $periodEnd->toDateString() . ' | Service: ' . $service->code . ' - ' . $service->name,
    //                 'status'       => 'unpaid',
    //                 'payment_mode' => null,
    //                 'created_by'   => 1,
    //             ]);

    //             DB::commit();
    //             $this->info("Payout created: {$periodStart->toDateString()} → {$periodEnd->toDateString()} for service {$service->id}");
    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             Log::error("Failed payout for service {$service->id}: " . $e->getMessage());
    //         }
    //     }

    //     $this->info('Client payouts generation completed.');
    // }

    public function handle()
    {
        $now = Carbon::now();
        $userServices = UserService::all();

        foreach ($userServices as $userService) {
            $created = Carbon::parse($userService->created_at);

            // 1) Count already-generated payouts for this service
            $count = PayoutClient::where('user_service_id', $userService->id)->count();

            if ($count === 0) {
                // First-ever payout → from created_at to +1 month immediately
                $periodStart = $created;
                $periodEnd   = $created->copy()->addMonth();
            } else {
                // Subsequent → roll-forward based on how many exist
                $periodStart = $created->copy()->addMonths($count);
                $periodEnd   = $periodStart->copy()->addMonth();
                // Only if that period has fully passed should we generate
                if ($now->lessThan($periodEnd)) {
                    continue;
                }
            }

            // Double-check to avoid dupes (shouldn't happen, but safe)
            $exists = PayoutClient::where('user_service_id', $userService->id)
                ->whereMonth('created_at', $periodEnd->month)
                ->whereYear('created_at',  $periodEnd->year)
                ->exists();

            if ($exists) {
                continue;
            }

            // Create the payout
            try {
                DB::beginTransaction();

                PayoutClient::create([
                    'invoice_no'        => $this->generateInvoiceNumber(),
                    'client_id'         => $userService->client_id,
                    'user_service_id'   => $userService->id,
                    'service_cost'      => $userService->service_cost,
                    // 'description'       => 'Payout for ' . $periodStart->toDateString() . ' to ' . $periodEnd->toDateString() . ' | Service: ' . $userService->services,
                    'description'       => 'Payout for ' . $periodStart->format('d-m-Y') . ' to ' . $periodEnd->format('d-m-Y') . ' | Service: ' . $userService->services,
                    'status'            => 'unpaid',
                    'payment_mode'      => null,
                    'created_by'        => 1,
                ]);

                DB::commit();
                // $this->info("Payout created: {$periodStart->toDateString()} → {$periodEnd->toDateString()} for service {$userService->id}");
                $this->info("Payout created: {$periodStart->format('d-m-Y')} → {$periodEnd->format('d-m-Y')} for service {$userService->id}");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed payout for service {$userService->id}: " . $e->getMessage());
            }
        }

        $this->info('Client payouts generation completed.');
    }

    protected function generateInvoiceNumber()
    {
        return 'INV-' . strtoupper(uniqid());
    }
}
