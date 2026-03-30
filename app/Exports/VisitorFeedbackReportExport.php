<?php

namespace App\Exports;

use App\Models\VisitorFeedbackReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class VisitorFeedbackReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = Auth::user();

        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        $allowedClientIds = User::whereIn('id', $allowedUserIds)
            ->pluck('client_id')
            ->filter()
            ->flatMap(fn($ids) => explode(',', $ids))
            ->unique()
            ->toArray();

        $query = VisitorFeedbackReport::with([
                'promoter:id,code,name',
                'store:id,code,name'
            ])
            ->select('visitor_feedback_reports.*')
            ->leftJoin('stores', 'visitor_feedback_reports.store_id', '=', 'stores.id');

        // For client type user, filter stores
        // if ($authUser->type === 'client') {
        //     $mappedStoreIds = \App\Models\StoreUserMapping::where('client_id', $authUser->id)
        //         ->pluck('store_id');
        //     $query->whereIn('visitor_feedback_reports.store_id', $mappedStoreIds);
        // }

        // // For staff, filter by client_id
        // if ($authUser->role_id != 1) {
        //     $clientIds = array_filter(explode(',', $authUser->client_id));
        //     $query->whereIn('stores.client_id', $clientIds);
        // }

        if ($authUser->role_id != 1) {
            $query->whereIn('stores.client_id', $allowedClientIds)
                ->whereIn('visitor_feedback_reports.created_by', $allowedUserIds);
        }

        // Apply filters from request
        if (!empty($this->filters['promoter_id'])) {
            $query->where('visitor_feedback_reports.promoter_id', $this->filters['promoter_id']);
        }

        if (!empty($this->filters['visitor_code'])) {
            $query->where('visitor_feedback_reports.visitor_code', $this->filters['visitor_code']);
        }

        if (!empty($this->filters['store_id'])) {
            $query->where('visitor_feedback_reports.store_id', $this->filters['store_id']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween(\DB::raw('DATE(visitor_feedback_reports.created_at)'), [
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        }

        return $query
            ->orderBy('visitor_feedback_reports.created_at', 'desc')
            ->get();
    }

    public function map($row): array
    {
        return [
            $row->promoter?->code ?? 'N/A',
            $row->promoter?->name ?? 'N/A',
            $row->visitor_code ?? 'N/A',
            $row->visitor_name ?? 'N/A',
            $row->store?->code ?? 'N/A',
            $row->store?->name ?? 'N/A',
            $this->formatFeedback($row->feedback),
            $row->comment ?? 'N/A',
            $row->created_at?->format('d-m-Y, H:i:s') ?? 'N/A',
            $row->updated_at?->format('d-m-Y, H:i:s') ?? 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Promoter Code',
            'Promoter Name',
            'Visitor Code',
            'Visitor Name',
            'Store Code',
            'Store Name',
            'Feedback',
            'Comment',
            'Created Date & Time',
            'Modified Date & Time',
        ];
    }

    /**
     * Format feedback JSON for export
     */
    protected function formatFeedback($feedback): string
    {
        if (empty($feedback) || !is_array($feedback)) {
            return 'N/A';
        }

        $result = [];

        foreach ($feedback as $item) {
            foreach ($item as $question => $answer) {
                if (is_string($answer) && preg_match('/\.(webp|png|jpg|jpeg)$/i', $answer)) {
                    $url = url("storage/visitor_feedback/{$answer}");
                    $result[] = "{$question}: {$url}";
                } else {
                    $result[] = "{$question}: {$answer}";
                }
            }
        }

        return implode(' | ', $result);
    }
}
