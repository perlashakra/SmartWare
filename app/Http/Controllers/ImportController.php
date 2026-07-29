<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateFileRequest;
use App\Imports\ProductsImport;
use App\Models\ImportFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function import(ValidateFileRequest $request)
    {
        // Validate request
        // Store uploaded file
        $file_path = $request->file('file')->store("imports/facility_{$request->facility_id}/products", 'private');
        // Create import_files record 

        DB::beginTransaction();
        try {
            DB::commit();
            ImportFile::create([
                'uploaded_by' => auth()->user()->id,
                'facility_id' => $request->facility_id,
                'file_name' => $request->file('file')->getClientOriginalName(),
                'file_path' => $file_path,
                'uploaded_at' => now(),
            ]);
            // Dispatch a queue job

            // Choose importer

            // Start import
            Excel::import(new ProductsImport(), $request->file('file'));

            //Retry if failed 
            //Excel::import(new ProductsImport(), storage_path('app/private/'.$importFile->file_path));
            return response()->json(['message' => 'File uploaded successfully!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        // Return response
    }

}
