<?php

namespace App\Exports;

use App\Models\VisibilityReport;
use App\Models\User;
use App\Models\Store;
use App\Models\Posm;
use App\Models\VisibilityReportAudit;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VisibilityReportExport implements FromCollection, WithHeadings, WithMapping
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

        // Get allowed client IDs (login + subordinate)
        $allowedClientIds = \App\Models\User::whereIn('id', $allowedUserIds)
            ->pluck('client_id')
            ->filter()
            ->flatMap(fn($ids) => explode(',', $ids))
            ->unique()
            ->toArray();

        $query = VisibilityReport::with(['promoter', 'store', 'posm', 'audit.auditor'])
            ->select('visibility_reports.*')
            ->leftJoin('stores', 'visibility_reports.store_id', '=', 'stores.id')
            ->leftJoin('posms', 'visibility_reports.posm_id', '=', 'posms.id');

        // if ($authUser->type === 'client') {
        //     $query->where('stores.client_id', $authUser->id);
        // } elseif ($authUser->role_id != 1) {
        //     $clientIds = array_filter(explode(',', $authUser->client_id));
        //     $query->whereIn('stores.client_id', $clientIds);
        // }

        if ($authUser->role_id != 1) {
            $query->whereIn('stores.client_id', $allowedClientIds)
                  ->whereIn('posms.created_by', $allowedUserIds);
        }

        if (!empty($this->filters['promoter_id'])) {
            $query->where('visibility_reports.promoter_id', $this->filters['promoter_id']);
        }

        if (!empty($this->filters['store_id'])) {
            $query->where('visibility_reports.store_id', $this->filters['store_id']);
        }

        if (!empty($this->filters['posm_id'])) {
            $query->where('visibility_reports.posm_id', $this->filters['posm_id']);
        }

        if (!empty($this->filters['visibility_action'])) {
            $query->where('visibility_reports.visibility_action', $this->filters['visibility_action']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween(\DB::raw('DATE(visibility_reports.created_at)'), [
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        }

        return $query->get();
    }

    /**
     * Map each row according to headings.
     */
    public function map($report): array
    {
        $baseUrl = url('storage/visibility_reports');
        $audit = $report->audit;

        $photoLeft = $report->photo_left_side ? $baseUrl . '/' . $report->photo_left_side : 'N/A';
        $photoCloseUp = $report->photo_close_up ? $baseUrl . '/' . $report->photo_close_up : 'N/A';
        $photoRight = $report->photo_right_side ? $baseUrl . '/' . $report->photo_right_side : 'N/A';

        $auditorCode = $audit?->auditor?->code ?? 'N/A';
        $auditorName = $audit?->auditor?->name ?? 'N/A';
        $formattedFeedback = $audit ? $this->formatFeedback($audit->feedback) : 'N/A';

        return [
            $report->promoter->code ?? 'N/A',
            $report->promoter->name ?? 'N/A',
            $report->store->code ?? 'N/A',
            $report->store->name ?? 'N/A',
            $report->posm->code ?? 'N/A',
            $report->posm->name ?? 'N/A',
            $report->visibility_action ?? '',
            $report->is_adhoc_visibility_available ? 'Yes' : 'No',
            $report->stock_as_per_planogram ? 'Yes' : 'No',
            $report->is_stock_available ? 'Yes' : 'No',
            $report->branding_condition ?? 'N/A',
            $report->visibility_type ?? 'N/A',
            $report->visibility_brand ?? 'N/A',
            $report->reason ?? 'N/A',
            $photoLeft,
            $photoCloseUp,
            $photoRight,
            $auditorCode,
            $auditorName,
            $formattedFeedback,
            $report->created_at instanceof Carbon ? $report->created_at->format('d-m-Y H:i:s') : 'N/A',
            $report->updated_at instanceof Carbon ? $report->updated_at->format('d-m-Y H:i:s') : 'N/A',
        ];
    }

    /**
     * Excel column headings.
     */
    public function headings(): array
    {
        return [
            'Promoter Code',
            'Promoter Name',
            'Store Code',
            'Store Name',
            'Posm Code',
            'Posm Name',
            'Visibility Action',
            'Is Adhoc Visibility Available',
            'Stock as per Planogram',
            'Is Stock Available',
            'Branding Condition',
            'visibility_type',
            'visibility_brand',
            'Reason',
            'Photo Left Side',
            'Photo Close Up',
            'Photo Right Side',
            'Auditor Code',
            'Auditor Name',
            'Feedback',
            'Created Date & Time',
            'Modified Date & Time',
        ];
    }

    /**
     * Format feedback for export.
     */
    protected function formatFeedback($feedback): string
    {
        if (empty($feedback) || !is_array($feedback)) {
            return 'N/A';
        }

        $result = [];

        foreach ($feedback as $question => $answer) {
            if (is_string($answer) && preg_match('/\.(webp|png|jpg|jpeg)$/i', $answer)) {
                $url = url("storage/visibility_audit_feedback/{$answer}");
                $result[] = "{$question}: {$url}";
            } else {
                if ($answer === '1' || $answer === 1) {
                    $answer = 'Yes';
                } elseif ($answer === '0' || $answer === 0) {
                    $answer = 'No';
                }

                $result[] = "{$question}: {$answer}";
            }
        }

        return implode(' | ', $result);
    }

}
