<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\DeliveryRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryRateController extends AdminController
{
    public function getIndex(Request $request)
    {
        $states = DB::table('delivery_rates')
            ->select('state')
            ->whereNotNull('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');
            
        return view('admin.delivery_rates.index', compact('states'));
    }

    public function getList(Request $request)
    {
        $list = DeliveryRate::query()
            ->select([
                'id',
                'state',
                'delivery_charge',
                'status',
                'created_at'
            ])
            ->when(
                $request->state !== null &&
                $request->state !== "",
                fn($q) => $q->where('state', $request->state)
            )
            ->when(
                $request->status !== null &&
                $request->status !== "",
                fn($q) => $q->where('status', $request->status)
            )
            ->orderBy('id', 'desc');

        return \DataTables::of($list)

            ->addColumn('status_badge', function ($row) {

                if ($row->status) {
                    return '<span class="badge bg-success">Active</span>';
                }

                return '<span class="badge bg-danger">Inactive</span>';
            })

            ->addColumn('action', function ($row) {

                $edit = route(
                    'admin.delivery_rates.update.index',
                    ['id' => $row->id]
                );

                $delete = route(
                    'admin.delivery_rates.delete',
                    ['id' => $row->id]
                );

                $status = route(
                    'admin.delivery_rates.change.status',
                    ['id' => $row->id]
                );

                return '
                    <a href="' . $edit . '" 
                       class="btn btn-sm btn-primary">
                        Edit
                    </a>

                    <button 
                        data-url="' . $status . '"
                        class="btn btn-sm btn-warning change-status-btn">
                        Status
                    </button>

                    <button 
                        data-url="' . $delete . '"
                        class="btn btn-sm btn-danger delete-btn">
                        Delete
                    </button>
                ';
            })

            ->rawColumns([
                'status_badge',
                'action'
            ])

            ->make(true);
    }

    public function getCreate()
    {
        $states = DB::table('india_pincodes')
            ->select('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');
            
        return view('admin.delivery_rates.create', compact('states'));
    }

    public function postCreate(Request $request)
    {
        $request->validate([
            'state' => 'required|string|max:255',
            'delivery_charge' => 'required|numeric|min:0',
            'status' => 'nullable|in:0,1'
        ]);

        DB::beginTransaction();

        try {

            DeliveryRate::create([
                'state' => $request->state,
                'delivery_charge' => $request->delivery_charge,
                'status' => (int) $request->status
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Delivery rate created successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error($e);

            return response()->json([
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function getUpdate(Request $request)
    {
        $delivery_rate = DeliveryRate::findOrFail($request->id);

        $states = DB::table('india_pincodes')
            ->select('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');

        return view('admin.delivery_rates.update', compact('delivery_rate', 'states'));
    }

    public function postUpdate(Request $request, $id)
    {
        $delivery_rate = DeliveryRate::findOrFail($id);

        $request->validate([
            'state' => 'required|string|max:255',
            'delivery_charge' => 'required|numeric|min:0',
            'status' => 'nullable|in:0,1'
        ]);

        DB::beginTransaction();

        try {

            $delivery_rate->update([
                'state' => $request->state,
                'delivery_charge' => $request->delivery_charge,
                'status' => (int) $request->status
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Delivery rate updated successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error($e);

            return response()->json([
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function getDelete(Request $request)
    {
        $delivery_rate = DeliveryRate::findOrFail($request->id);

        $delivery_rate->delete();

        return response()->json([
            'message' => 'Delivery rate deleted successfully'
        ]);
    }

    public function getChangeStatus(Request $request)
    {
        $delivery_rate = DeliveryRate::findOrFail($request->id);

        $delivery_rate->status = !$delivery_rate->status;

        $delivery_rate->save();

        return response()->json([
            'message' => 'Status updated successfully'
        ]);
    }
}