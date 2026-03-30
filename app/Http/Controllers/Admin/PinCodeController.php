<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Imports\PinCodeImport;
use App\Exports\PinCodeExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Maatwebsite\Excel\Validators\ValidationException;

class PinCodeController extends AdminController
{
    public function getIndex(Request $request)
    {
        \Can::access('pin_codes');

        return view('admin.pin_codes.index');
    }

    public function getList(Request $request)
    {
        $list = \App\Models\PinCode::select('pin_codes.*', 'cities.name AS city', 'states.name AS state', 'countries.name AS country')
            ->leftJoin('cities', 'cities.id', '=', 'pin_codes.city_id')
            ->leftJoin('states', 'states.id', '=', 'pin_codes.state_id')
            ->leftJoin('countries', 'countries.id', '=', 'pin_codes.country_id')
            ->orderByDesc('updated_at'); // ✅ Latest created or updated records on top

        return \DataTables::of($list)->make();
    }

    public function getLocationViaPinCode(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'pin_code' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $dataObj = objFromPost(['pin_code']);

        $output = ['city' => '', 'state' => '', 'country' => '', 'city_id' => '', 'state_id' => '', 'country_id' => ''];

        if ($result = \App\Models\PinCode::wherePinCode($dataObj->pin_code)->whereStatus(1)->first()) {
            $output = [
                'city' =>  $result->city_id ? \App\Models\City::whereId($result->city_id)->value('name') : '',
                'state' =>  $result->state_id ? \App\Models\State::whereId($result->state_id)->value('name') : '',
                'country' =>  $result->country_id ? \App\Models\Country::whereId($result->country_id)->value('name') : '',
                'city_id' => $result->city_id,
                'state_id' => $result->state_id,
                'country_id' => $result->country_id,
            ];
        }

        return response()->json($output);
    }

    public function getCreate(Request $request)
    {
        \Can::access('pin_codes', 'create');

        $countries = \App\Models\Country::select('id', 'name')
            ->whereStatus(1)
            ->orderBy('name')
            ->get();

        return view('admin.pin_codes.create', compact('countries'));
    }

    public function postCreate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'pincode' => "required",
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'status' => 'nullable|in:1,0',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $dataObj = objFromPost(['pincode', 'country', 'state', 'city', 'status']);

        $dataObj->created_by = auth()->id();

        try {
            $pin_code = new \App\Models\PinCode();
            $pin_code->pin_code = $dataObj->pincode;
            $pin_code->country_id = $dataObj->country;
            $pin_code->state_id = $dataObj->state;
            $pin_code->city_id = $dataObj->city;
            $pin_code->status = (int)($dataObj->status == 1);
            $pin_code->created_by = $dataObj->created_by;
            $pin_code->save();

            return response()->json(['message' => 'Your request processed successfully.']);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
        }
    }

    public function getUpdate(Request $request)
    {
        \Can::access('pin_codes', 'update');

        $pin_code = \App\Models\PinCode::findOrFail($request->id);

        $countries = \App\Models\Country::select('id', 'name')
            ->whereStatus(1)
            ->orderBy('name')
            ->get();

        return view('admin.pin_codes.update', compact('pin_code', 'countries'));
    }

    public function postUpdate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'pincode' => "required",
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'status' => 'nullable|in:1,0',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $dataObj = objFromPost(['pincode', 'country', 'state', 'city', 'status']);

        $dataObj->modified_by = auth()->id();

        try {
            $pin_code = \App\Models\PinCode::find($request->id);
            if (!blank($pin_code)) {
                $pin_code->pin_code = $dataObj->pincode;
                $pin_code->country_id = $dataObj->country;
                $pin_code->state_id = $dataObj->state;
                $pin_code->city_id = $dataObj->city;
                $pin_code->status = (int)($dataObj->status == 1);
                $pin_code->modified_by = $dataObj->modified_by;
                $pin_code->save();
            }

            return response()->json(['message' => 'Your request processed successfully.']);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
        }
    }

    public function getDelete(Request $request)
    {
        \Can::access('pin_codes', 'delete');

        \App\Models\PinCode::whereId($request->id)->delete();
        return response()->json(['message' => 'Your request processed successfully.']);
    }

    public function getChangeStatus(Request $request)
    {
        \Can::access('pin_codes', 'update');

        $pin_code = \App\Models\PinCode::findOrFail($request->id);
        if (!blank($pin_code)) {
            $pin_code->status = (int)!$pin_code->status;
            $pin_code->save();
        }

        return response()->json(['message' => 'Your request processed successfully.']);
    }

    // Import Xlsx
    public function getXlsxImport(Request $request)
    {
        return view('admin.pin_codes.import-xlsx');
    }

    public function getXlsxImportSampleDownload(Request $request)
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $sheet->setCellValue('A1', 'Pin Code');
        $sheet->setCellValue('B1', 'City');
        $sheet->setCellValue('C1', 'State');
        $sheet->setCellValue('D1', 'Country');
        $sheet->setCellValue('E1', 'Status');

        // Add sample row
        $sheet->setCellValue('A2', '110044');
        $sheet->setCellValue('B2', 'Badarpur');
        $sheet->setCellValue('C2', 'New Delhi');
        $sheet->setCellValue('D2', 'India');
        $sheet->setCellValue('E2', 'Yes');

        // Create writer and temporary file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Pin_code Sample File.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        // Return as download response and delete temp file after sending
        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }

    public function postXlsxImport(Request $request)
    {
        // ✅ Step 1: Validate uploaded file pin_code (XLSX/XLS)
        $validator = Validator::make($request->all(), [
            'xlsx_file' => 'required|file|mimes:xlsx,xls|max:2048', // Added file max size limit (2MB example)
        ])->stopOnFirstFailure(true);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            // ✅ Step 2: Process import
            Excel::import(new PinCodeImport, $request->file('xlsx_file'));

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
        $fileName = 'pin_codes_' . date('Y_m_d_H_i_s') . '.xlsx';
        return Excel::download(new PinCodeExport, $fileName);
    }
    
}
