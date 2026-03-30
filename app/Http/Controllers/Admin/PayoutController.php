<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    public function getIndex()
    {
        return view('admin.payouts.index');
    }

    public function getList(Request $request)
    {
        $query = PayoutRequest::with([
            'user:id,name,role_id,type,code',
            'wallet:id,user_id,balance',
            'paymentAccount:id,type'
        ])
        ->when($request->astrologer_id, function ($q) use ($request) {
            $q->whereHas('user', function ($uq) use ($request) {
                $uq->where('id', $request->astrologer_id)
                ->where('role_id', 2);
            });
        })
        ->when($request->user_id, function ($q) use ($request) {
            $q->whereHas('user', function ($uq) use ($request) {
                $uq->where('id', $request->user_id)
                ->where('role_id', 3);
            });
        })
        ->when($request->status, fn ($q) => $q->where('status', $request->status))
        ->when(
            $request->start_date && $request->end_date,
            fn ($q) => $q->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ])
        );

        return \DataTables::of($query)
            ->addColumn('user_type', fn ($row) =>
                $row->user->role_id == 2 ? 'Astrologer' : 'User'
            )
            ->addColumn('user_name', fn ($row) => $row->user->name)
            ->addColumn('amount', fn ($row) => '₹ ' . number_format($row->amount, 2))
            ->addColumn('method', fn ($row) => strtoupper($row->paymentAccount->type))
            ->addColumn('status_badge', function ($row) {
                return match ($row->status) {
                    'approved' => '<span class="badge bg-success">Approved</span>',
                    'rejected' => '<span class="badge bg-danger">Rejected</span>',
                    default    => '<span class="badge bg-warning">Pending</span>',
                };
            })
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    public function getUpdate($id)
    {
        $payout = PayoutRequest::with(['user', 'wallet', 'paymentAccount'])
            ->findOrFail($id);

        return view('admin.payouts.update', compact('payout'));
    }

    public function getView($id)
    {
        $payout = PayoutRequest::with([
            'user',
            'wallet',
            'paymentAccount'
        ])->findOrFail($id);

        return view('admin.payouts.view', compact('payout'));
    }

    public function approve($id)
    {
        DB::transaction(function () use ($id) {

            $payout = PayoutRequest::where('id', $id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->firstOrFail();

            $wallet = Wallet::where('id', $payout->wallet_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->balance < $payout->amount) {
                abort(422, 'Insufficient wallet balance');
            }

            // Wallet update
            $wallet->balance -= $payout->amount;
            $wallet->total_withdrawn += $payout->amount;
            $wallet->save();

            // Payout update
            $payout->status = 'approved';
            $payout->save();
        });

        return response()->json(['success' => true]);
    }

    public function reject($id)
    {
        PayoutRequest::where('id', $id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        return response()->json(['success' => true]);
    }

    public function getDelete(Request $request)
    {
        PayoutRequest::findOrFail($request->id)->delete();

        return response()->json([
            'message' => 'Payout deleted successfully'
        ]);
    }
}
