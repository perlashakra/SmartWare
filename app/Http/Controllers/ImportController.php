<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateFileRequest;
use App\Imports\InventoryImport;
use App\Models\ImportFile;
use App\Models\Section;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function import(ValidateFileRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $warehouse_id = $request->facility_id;

            if(!$user->warehouses()->whereKey($warehouse_id)->exists()) {
                return response()->json(['message' => 'Unauthorized. You do not own this warehouse.',], 403);
            }

            $section = Section::firstOrCreate(
                [
                    'warehouse_id' => $warehouse_id,
                    'name' => 'Main Storage',
                ],
                [
                    'capacity' => 0,
                ]
            );

            $file = $request->file('file');

            $filePath = $file->store("imports/facility_{$warehouse_id}/inventory", 'private');

            $importFile = ImportFile::create([
                'uploaded_by' => $user->id,
                'facility_id' => $warehouse_id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'status' => 'processing',
                'uploaded_at' => now(),
            ]);

            Excel::import(
                new InventoryImport(
                    $section,
                    new InventoryService(),
                ),
                $file
            );

            $importFile->update(['status' => 'success']);

            DB::commit();

            return response()->json(['message' => 'Inventory imported successfully.', 'import_file_id' => $importFile->id,], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}