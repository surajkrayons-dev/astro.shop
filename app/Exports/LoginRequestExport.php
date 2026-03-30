<?php

namespace App\Exports;

use App\Models\LoginRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class LoginRequestExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return LoginRequest::with('user')->get();
    }

    public function map($row): array
    {
        $created_at = $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y, H:i:s') : 'N/A';
        $updated_at = $row->updated_at ? Carbon::parse($row->updated_at)->format('d-m-Y, H:i:s') : 'N/A';

        return [
            $row->user->code ?? 'N/A',
            $row->user->name ?? 'N/A',
            $row->user->username ?? 'N/A',
            $this->formatStatus($row->status),
            $row->hash_token ?? 'N/A',
            $created_at,
            $updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Username',
            'Status',
            'Hash Token',
            'Created At',
            'Updated At',
        ];
    }

    private function formatStatus($status): string
    {
        return match ($status) {
            'pending' => 'Pending',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
            'logged_out' => 'Logged Out',
            default => ucfirst($status),
        };
    }
}
