<?php

namespace App\Exports;

use App\Models\KycReport;
use App\Models\KycReportAudit;
use Carbon\Carbon;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KycReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        // 🔹 Get subordinate and allowed user IDs
        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        // 🔹 Get all allowed client IDs from login user + subordinates
        $allowedClientIds = User::whereIn('id', $allowedUserIds)
            ->pluck('client_id')
            ->filter()
            ->flatMap(fn($ids) => explode(',', $ids))
            ->unique()
            ->toArray();

        $query = KycReport::with(['store', 'promoter', 'audit.auditor'])
            ->leftJoin('stores', 'stores.id', '=', 'kyc_reports.store_id')
            ->leftJoin('users as promoters', 'promoters.id', '=', 'kyc_reports.promoter_id')
            ->select('kyc_reports.*');

        if ($authUser->role_id != 1) {
            $query->whereIn('stores.client_id', $allowedClientIds);
        }

        if (!empty($this->filters['store_id'])) {
            $query->where('kyc_reports.store_id', $this->filters['store_id']);
        }

        if (!empty($this->filters['promoter_id'])) {
            $query->where('kyc_reports.promoter_id', $this->filters['promoter_id']);
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== null && $this->filters['status'] !== '') {
            $query->where('kyc_reports.status', $this->filters['status']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween(\DB::raw('DATE(kyc_reports.created_at)'), [
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        }

        return $query->orderByDesc('kyc_reports.updated_at')->get();
    }

    public function map($kyc): array
    {
        $data = $kyc->data ?? [];
        $audit = $kyc->audit;

        $business = $data['business_info'] ?? [];
        $identity = $data['identity_info'] ?? [];
        $account = $data['account_info'] ?? [];

        $baseUrl = url('storage/kyc');

        // Image URLs
        $registrationImage = !empty($business['registration_image']) ? $baseUrl . '/' . $business['registration_image'] : 'N/A';
        $gstImage = !empty($business['gst_image']) ? $baseUrl . '/' . $business['gst_image'] : 'N/A';
        $aadhaarFront = !empty($identity['aadhaar_front_image']) ? $baseUrl . '/' . $identity['aadhaar_front_image'] : 'N/A';
        $aadhaarBack = !empty($identity['aadhaar_back_image']) ? $baseUrl . '/' . $identity['aadhaar_back_image'] : 'N/A';
        $panCardImage = !empty($identity['pancard_image']) ? $baseUrl . '/' . $identity['pancard_image'] : 'N/A';
        $passbookImage = !empty($account['passbook_image']) ? $baseUrl . '/' . $account['passbook_image'] : 'N/A';

        // Get audit details if available
        $audit = KycReportAudit::where('kyc_report_id', $kyc->id)->first();
        $auditorCode = $audit?->auditor?->code ?? 'N/A';
        $auditorName = $audit?->auditor?->name ?? 'N/A';
        $feedback = $this->formatFeedback($audit?->feedback ?? []);
        $remark = $audit?->remark ?? 'N/A';
        $auditStatus = $audit?->status ?? 'N/A';

        return [
            $kyc->store->code ?? 'N/A',
            $kyc->store->name ?? 'N/A',
            $kyc->promoter->code ?? 'N/A',
            $kyc->promoter->name ?? 'N/A',
            $business['business_type'] ?? 'N/A',
            $business['owner_name'] ?? 'N/A',
            $business['mobile'] ?? 'N/A',
            $business['gst_no'] ?? 'N/A',
            $business['store_registration_name'] ?? 'N/A',
            $registrationImage,
            $gstImage,
            $identity['aadhaar_no'] ?? 'N/A',
            $aadhaarFront,
            $aadhaarBack,
            $identity['pancard_no'] ?? 'N/A',
            $panCardImage,
            $account['bank_name'] ?? 'N/A',
            $account['account_number'] ?? 'N/A',
            $account['ifsc_code'] ?? 'N/A',
            $account['account_holder_name'] ?? 'N/A',
            $passbookImage,
            $kyc->status ?? 'N/A',
            $auditorCode,
            $auditorName,
            $feedback,
            $remark,
            $auditStatus,
            $kyc->created_at instanceof Carbon ? $kyc->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $kyc->updated_at instanceof Carbon ? $kyc->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Store Code',
            'Store Name',
            'Promoter Code',
            'Promoter Name',
            'Business Type',
            'Owner Name',
            'Mobile',
            'GST No',
            'Store Registration Name',
            'Registration Image',
            'GST Image',
            'Aadhaar No',
            'Aadhaar Front Image',
            'Aadhaar Back Image',
            'PAN Card No',
            'PAN Card Image',
            'Bank Name',
            'Account Number',
            'IFSC Code',
            'Account Holder Name',
            'Passbook Image',
            'Status',
            'Auditor Code',
            'Auditor Name',
            'Feedback',
            'Remark',
            'Audit Status',
            'Created At',
            'Updated At',
        ];
    }

    protected function formatFeedback($feedback): string
    {
        if (empty($feedback) || !is_array($feedback)) {
            return 'N/A';
        }

        $result = [];

        foreach ($feedback as $question => $answer) {
            if (is_string($answer) && preg_match('/\.(webp|png|jpg|jpeg)$/i', $answer)) {
                $url = url("storage/kyc_audit_feedback/{$answer}");
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
