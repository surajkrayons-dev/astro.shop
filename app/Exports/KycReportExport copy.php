<?php

namespace App\Exports;

use App\Models\KycReport;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class KycReportExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $authUser = auth()->user();

        $query = KycReport::with(['store', 'promoter', 'createdBy', 'modifiedBy'])
            ->leftJoin('stores', 'stores.id', '=', 'kyc_reports.store_id')
            ->leftJoin('users as promoters', 'promoters.id', '=', 'kyc_reports.promoter_id');

        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $query->whereIn('stores.client_id', $clientIds);
        }

        return $query->select('kyc_reports.*')->get();
    }

    /**
     * Map data for each row to include both code & name for related entities.
     */
    public function map($kyc): array
    {
        $baseUrl = url('storage/kyc');

        $passbookImage = $kyc->passbook_image ? $baseUrl . '/' . $kyc->passbook_image : 'N/A';
        $aadhaarFront = $kyc->aadhaar_card_front ? $baseUrl . '/' . $kyc->aadhaar_card_front : 'N/A';
        $aadhaarBack = $kyc->aadhaar_card_back ? $baseUrl . '/' . $kyc->aadhaar_card_back : 'N/A';
        $panCardImage = $kyc->pan_card_image ? $baseUrl . '/' . $kyc->pan_card_image : 'N/A';

        return [
            $kyc->store->code ?? 'N/A',
            $kyc->store->name ?? 'N/A',
            $kyc->promoter->code ?? 'N/A',
            $kyc->promoter->name ?? 'N/A',
            $kyc->bank_name ?? 'N/A',
            $kyc->account_number ?? 'N/A',
            $kyc->ifsc_code ?? 'N/A',
            $kyc->account_holder_name ?? 'N/A',
            $kyc->status ?? 'N/A',
            $kyc->remark ?? 'N/A',
            $passbookImage,
            $aadhaarFront,
            $aadhaarBack,
            $panCardImage,
            $kyc->createdBy ? $kyc->createdBy->code : 'N/A',
            $kyc->created_at instanceof Carbon ? $kyc->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $kyc->modifiedBy ? $kyc->modifiedBy->code : 'N/A',
            $kyc->updated_at instanceof Carbon ? $kyc->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Define headings for the exported file.
     */
    public function headings(): array
    {
        return [
            'Store Code',
            'Store Name',
            'Promoter Code',
            'Promoter Name',
            'Bank Name',
            'Account Number',
            'IFSC Code',
            'Account Holder Name',
            'Status',
            'Remark',
            'Passbook Image',
            'Aadhaar Card Front',
            'Aadhaar Card Back',
            'PAN Card Image',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
