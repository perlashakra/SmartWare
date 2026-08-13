<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnounceWorkerRequest;
use App\Models\EmployeeAnnouncement;
use App\Models\User;
use Illuminate\Http\Request;

class WarehouseManagerController extends Controller
{
    public function announceWorker(AnnounceWorkerRequest $request)
    {
        $validatedData = $request->validated();
        $warehouse = $request->user()->facilities()->find($validatedData['facility_id']);
        if(!$warehouse){
            return response()->json([
                'error' => 'Facility not found or access denied.'
            ], 404);
        }
        $employeeAnnouncement = EmployeeAnnouncement::create([
            'employmentWarehouse_id' => $validatedData['facility_id'],
            'manager_id' => $request->user()->id,
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'national_id' => $validatedData['national_id'],
            'claimed' => false,
        ]);
        return response()->json([
            'message' => 'Announcement created.',
            'data' => $employeeAnnouncement
        ]);
    }

    public function terminateJob(Request $request)
    {
        $validatedData = $request->validate([
            'worker_id' => ['required', 'exists:users,id'],
        ]);

        $worker = User::where('id', $validatedData['worker_id'])->first();

        if (!$worker || $worker->manager_id != $request->user()->id) {
            return response()->json(['error' => 'Worker not found or access denied.'], 404);
        }

        // ADD ->first() TO EXECUTE THE QUERY AND GET THE MODEL
        $employeeAnnouncement = EmployeeAnnouncement::where('worker_id', $validatedData['worker_id'])->first();

        if (!$employeeAnnouncement) {
            return response()->json(['error' => 'Announcement not found.'], 404);
        }

        if ($employeeAnnouncement->status === 'terminated') {
            return response()->json(['error' => 'Worker already terminated.'], 400);
        }

        $employeeAnnouncement->status = 'terminated';
        $employeeAnnouncement->save();

        return response()->json(['message' => 'Worker terminated.']);
    }
}
