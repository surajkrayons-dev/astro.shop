<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Imports\CityImport;
use App\Exports\CityExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Maatwebsite\Excel\Validators\ValidationException;

class CityController extends AdminController
{
    public function getIndex(Request $request)
    {
        \Can::access('cities');

        return view('admin.cities.index');
    }

    public function getList(Request $request)
    {
        $list = \App\Models\City::select('cities.*', 'states.name AS state', 'countries.name AS country')
            ->leftJoin('states', 'states.id', '=', 'cities.state_id')
            ->leftJoin('countries', 'countries.id', '=', 'cities.country_id')
            ->orderByDesc('updated_at'); // ✅ Latest created or updated records on top

        return \DataTables::of($list)->make();
    }

    public function getStateWiseList(Request $request)
    {
        $list = \App\Models\City::select('id', 'name')->whereStateId($request->state_id)->get();
        return response()->json($list);
    }

    public function getCreate(Request $request)
    {
        \Can::access('cities', 'create');

        $countries = \App\Models\Country::select('id', 'name')
            ->whereStatus(1)
            ->orderBy('name')
            ->get();

        return view('admin.cities.create', compact('countries'));
    }

    public function postCreate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'city_name' => "required",
            'country' => 'required',
            'state' => 'required',
            'status' => 'nullable|in:1,0',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $dataObj = objFromPost(['city_name', 'country', 'state', 'status']);

        $dataObj->created_by = auth()->id();

        try {
            $city = new \App\Models\City();
            $city->name = $dataObj->city_name;
            $city->country_id = $dataObj->country;
            $city->state_id = $dataObj->state;
            $city->status = (int)($dataObj->status == 1);
            $city->created_by = $dataObj->created_by;
            $city->save();

            return response()->json(['message' => 'Your request processed successfully.']);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
        }
    }

    public function getUpdate(Request $request)
    {
        \Can::access('cities', 'update');

        $city = \App\Models\City::findOrFail($request->id);

        $countries = \App\Models\Country::select('id', 'name')
            ->whereStatus(1)
            ->orderBy('name')
            ->get();

        return view('admin.cities.update', compact('city', 'countries'));
    }

    public function postUpdate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'city_name' => "required",
            'country' => 'required',
            'state' => 'required',
            'status' => 'nullable|in:1,0',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $dataObj = objFromPost(['city_name', 'country', 'state', 'status']);

        $dataObj->modified_by = auth()->id();

        try {
            $city = \App\Models\City::find($request->id);
            if (!blank($city)) {
                $city->name = $dataObj->city_name;
                $city->country_id = $dataObj->country;
                $city->state_id = $dataObj->state;
                $city->status = (int)($dataObj->status == 1);
                $city->modified_by = $dataObj->modified_by;
                $city->save();
            }

            return response()->json(['message' => 'Your request processed successfully.']);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
        }
    }

    public function getDelete(Request $request)
    {
        \Can::access('cities', 'delete');

        \App\Models\City::whereId($request->id)->delete();
        return response()->json(['message' => 'Your request processed successfully.']);
    }

    public function getChangeStatus(Request $request)
    {
        \Can::access('cities', 'update');

        $city = \App\Models\City::findOrFail($request->id);
        if (!blank($city)) {
            $city->status = (int)!$city->status;
            $city->save();
        }

        return response()->json(['message' => 'Your request processed successfully.']);
    }

    // Import Xlsx
    public function getXlsxImport(Request $request)
    {
        return view('admin.cities.import-xlsx');
    }

    public function getXlsxImportSampleDownload(Request $request)
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'State');
        $sheet->setCellValue('C1', 'Country');
        $sheet->setCellValue('D1', 'Status');

        // Add sample row
        $sheet->setCellValue('A2', 'Badarpur');
        $sheet->setCellValue('B2', 'New Delhi');
        $sheet->setCellValue('C2', 'India');
        $sheet->setCellValue('D2', 'Yes');

        // Create writer and temporary file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Citie Sample File.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        // Return as download response and delete temp file after sending
        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }

    public function postXlsxImport(Request $request)
    {
        // ✅ Step 1: Validate uploaded file citie (XLSX/XLS)
        $validator = Validator::make($request->all(), [
            'xlsx_file' => 'required|file|mimes:xlsx,xls|max:2048', // Added file max size limit (2MB example)
        ])->stopOnFirstFailure(true);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            // ✅ Step 2: Process import
            Excel::import(new CityImport, $request->file('xlsx_file'));

            return response()->json([
                'message' => 'Your request processed successfully.'
            ], 200);

        } catch (ValidationException $e) {
            // ✅ Step 3: Handle row-wise validation errors
            $messages = [];
            foreach ($e->failures() as $failure) {
                $messages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return response()->json([
                // 'message' => 'Validation errors occurred.',
                'message' => $messages
            ], 422);

        } catch (\Throwable $th) {
            // ✅ Step 4: Catch unexpected errors and log them
            \Log::error('XLSX Import Error: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString()
            ]);

            // return response()->json([
            //     'message' => 'Failed to process your request. Please try again later.'
            // ], 500);
            return response()->json([
                'message' => $th->getMessage()
            ], 422);
        }
    }

    public function exportXlsx()
    {
        $fileName = 'cities_' . date('Y_m_d_H_i_s') . '.xlsx';
        return Excel::download(new CityExport, $fileName);
    }
}
