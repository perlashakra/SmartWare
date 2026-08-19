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
        $section = Section::findOrFail($request->section_id);

        $user = Auth::user();

        if ($user->role !== 'warehouse_admin' && !$user->warehouses()->whereKey($section->warehouse_id)->exists()) {
            return response()->json(['message' => 'Unauthorized. You do not own this warehouse.',], 403);
        }

        $file = $request->file('file');

        $filePath = $file->store("imports/facility_{$section->warehouse_id}/inventory", 'private');

        $importFile = ImportFile::create([
            'uploaded_by' => $user->id,
            'facility_id' => $section->warehouse_id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'status' => 'processing',
            'uploaded_at' => now(),
        ]);

        try {
            DB::transaction(function () use (
                $file,
                $section
            ) {
                Excel::import(
                    new InventoryImport(
                        $section,
                        new InventoryService(),
                    ),
                    $file
                );
            });

            $importFile->update(['status' => 'success']);

            return response()->json(['message' => 'Inventory imported successfully.', 'import_file_id' => $importFile->id,], 200);

        } catch (\Throwable $e) {
            $importFile->update(['status' => 'failed']);
            throw $e;
        }
    }
}