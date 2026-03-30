<?php

namespace App\Exports;

use App\Models\Banner;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class BannerExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Banner::with(['creator','modifier'])
        ->select('name','url','description', 'image','order', 'status', 'created_by','modified_by', 'created_at', 'updated_at')
        ->get();
    }

    /**
     * Map data for each row to show user name instead of id
     */
    public function map($banner): array
    {
        $baseUrl = url('storage/banner');
        $imageUrl = $banner->image ? $baseUrl . '/' . $banner->image : 'N/A';
        
        return [
            $banner->name ?? 'N/A',
            $banner->order ?? 'N/A',
            $banner->status ? 'Yes' : 'No',
            $banner->url ?? 'N/A',
            $imageUrl,
            $banner->description ?? 'N/A',
            $banner->creator ? $banner->creator->code : 'N/A',
            $banner->created_at instanceof Carbon ? $banner->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $banner->modifier ? $banner->modifier->code : 'N/A',
            $banner->updated_at instanceof Carbon ? $banner->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Add headings to the exported file
     */
    public function headings(): array
    {
        return [
            'Banner Name',
            'Banner Order',
            'Status',
            'Banner Link',
            'Image',
            'Description',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }

}
