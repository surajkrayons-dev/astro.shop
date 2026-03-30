<?php

namespace App\Exports;

use App\Models\CompetitionProduct;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class CompetitionProductExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        //  Current user ke client aur subordinate users
        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = function_exists('getAllSubordinateIds')
            ? getAllSubordinateIds($authUser->id, $clientIds)
            : [];
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        $filters = $this->filters;

        $query = CompetitionProduct::with(['category', 'brand', 'creator', 'modifier'])
            ->select('competition_products.*');

        // Apply filters
        $query->when(!empty($filters['competition_category_id']), function ($q) use ($filters) {
            $q->where('competition_category_id', $filters['competition_category_id']);
        });

        $query->when(!empty($filters['competition_brand_id']), function ($q) use ($filters) {
            $q->where('competition_brand_id', $filters['competition_brand_id']);
        });

        $query->when(!empty($filters['code']), function ($q) use ($filters) {
            $q->where('code', 'like', '%' . $filters['code'] . '%');
        });

        $query->when(!empty($filters['name']), function ($q) use ($filters) {
            $q->where('name', 'like', '%' . $filters['name'] . '%');
        });

        //  Subordinate restriction
        if ($authUser->role_id != 1) {
            $query->where(function ($q) use ($allowedUserIds) {
                $q->whereIn('competition_products.created_by', $allowedUserIds)
                    ->orWhereHas('category', function ($subQ) use ($allowedUserIds) {
                        $subQ->whereIn('created_by', $allowedUserIds);
                    })
                    ->orWhereHas('brand', function ($subQ) use ($allowedUserIds) {
                        $subQ->whereIn('created_by', $allowedUserIds);
                    });
            });
        }

        return $query->orderByDesc('updated_at')->get();
    }

    /**
     * Map each row according to headings.
     */
    public function map($row): array
    {
        // If there's no MRP, show 'N/A'
        if (is_null($row->mrp)) {
            $mrpFormatted = 'N/A';
        } else {
            // Convert numeric MRP to a string
            $number = (string) $row->mrp;

            // Handle negative numbers
            $negative = false;
            if (substr($number, 0, 1) === '-') {
                $negative = true;
                $number = substr($number, 1);
            }

            // Separate out any decimals
            $decimalPart = '';
            if (strpos($number, '.') !== false) {
                [$number, $decimalPart] = explode('.', $number, 2);
                $decimalPart = '.' . $decimalPart; // re-append the dot
            }

            // If length <= 3, no extra commas needed
            if (strlen($number) <= 3) {
                $mrpFormatted = ($negative ? '-' : '') . $number . $decimalPart;
            } else {
                // Last 3 digits
                $last3Digits = substr($number, -3);
                // Remaining digits
                $rest = substr($number, 0, -3);
                // Insert commas every 2 digits in the rest
                $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
                // Combine them
                $mrpFormatted = ($negative ? '-' : '') . $rest . ',' . $last3Digits . $decimalPart;
            }
        }

        return [
            $row->category->code ?? 'N/A',
            $row->category->name ?? 'N/A',
            $row->brand->code ?? 'N/A',
            $row->brand->name ?? 'N/A',
            $row->code ?? 'N/A',
            $row->name ?? 'N/A',
            $mrpFormatted,
            // $row->order ?? 'N/A',
            $row->creator->code ?? 'N/A',
            $row->created_at instanceof Carbon ? $row->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $row->modifier->code ?? 'N/A',
            $row->updated_at instanceof Carbon ? $row->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Headings in the same order as map().
     */
    public function headings(): array
    {
        return [
            'competition Category Code',
            'competition Category Name',
            'competition Brand Code',
            'competition Brand Name',
            'Code',
            'Name',
            'MRP',
            // 'Order',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }
}
