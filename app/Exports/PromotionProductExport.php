<?php

namespace App\Exports;

use App\Models\PromotionProduct;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class PromotionProductExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        $query = PromotionProduct::with(['createdBy', 'modifiedBy'])
            ->select('promotion_products.*');

        // ✅ Apply filters properly
        if (!empty($this->filters['code'])) {
            $query->where('code', 'like', '%' . $this->filters['code'] . '%');
        }

        if (!empty($this->filters['name'])) {
            $query->where('name', 'like', '%' . $this->filters['name'] . '%');
        }

        if ($this->filters['status'] !== '' && $this->filters['status'] !== null) {
            $query->where('status', $this->filters['status']);
        }

        // ✅ Apply subordinate restriction
        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
            $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
            $allowedUserIds = array_merge([$authUser->id], $subordinateIds);
            $query->whereIn('created_by', $allowedUserIds);
        }

        // ✅ Always return a collection
        return $query->orderByDesc('updated_at')->get();
    }
    
    /**
     * Map data for each row to show user name instead of id
     */
    public function map($promotion_product): array
    {
        return [
            $promotion_product->code ?? 'N/A',
            $promotion_product->name ?? 'N/A',
            $promotion_product->status ? 'Yes' : 'No',
            $promotion_product->createdBy ? $promotion_product->createdBy->code : 'N/A',
            $promotion_product->created_at instanceof Carbon ? $promotion_product->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $promotion_product->modifiedBy ? $promotion_product->modifiedBy->code : 'N/A',
            $promotion_product->updated_at instanceof Carbon ? $promotion_product->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Add headings to the exported file
     */
    public function headings(): array
    {
        return [
            'Promotion Product Code',
            'Promotion Product name',
            'Status',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }

}
