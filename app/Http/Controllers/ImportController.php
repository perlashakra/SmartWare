<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateFileRequest;
use App\Imports\InventoryImport;
use App\Models\ImportFile;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function import(ValidateFileRequest $request)
    {
        DB::beginTransaction();
        try{
            $section = Section::findOrFail($request->section_id);
            $user = Auth::user();

            if(!$user->managedWarehouses()->whereKey($section->warehouse_id)->exists()){
                return response()->json(['message' => 'Unauthorized. You do not own this warehouse.'], 403);
            }

            $file = $request->file('file');
            $filePath = $file->store("imports/facility_{$request->facility_id}/inventory", 'private');
            
            $import_file = ImportFile::create([
                'uploaded_by' => Auth::id(),
                'facility_id' => $section->warehouse_id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'status' => 'processing',
                'uploaded_at' => now(),
            ]);

            Excel::import(
                new InventoryImport(
                    $section->id,
                    $section->company_id
                ),
                $file
            );

            $import_file->update(['status' => 'success']);

            DB::commit();
            return response()->json(['message' => 'Inventory imported successfully', 'import_file_id' => $import_file->id], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
