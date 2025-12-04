<?php

namespace App\Http\Controllers;

use App\Models\Admitted;
use App\Models\ApprehendingOfficer;
use App\Models\TrafficViolation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdmittedCaseController extends Controller
{
    public function create()
    {
        $officers = ApprehendingOfficer::select('officer', 'department')->get();
        $violations = TrafficViolation::orderBy('code', 'asc')->get();
        
        return view('admitted.manage', compact('officers', 'violations'));
    }

    public function index()
    {
        $admitted = Admitted::orderByDesc('admittedno')->get();
        
        foreach ($admitted as $admit) {
            $admit->relatedofficer = ApprehendingOfficer::where('officer', $admit->apprehending_officer)->get();
            
            $remarks = is_string($admit->remarks) ? json_decode($admit->remarks, true) ?? [] : ($admit->remarks ?? []);
            $admit->remarks = $remarks;

            $violations = json_decode($admit->violation);
            if ($violations) {
                $relatedViolations = is_array($violations)
                    ? TrafficViolation::whereIn('code', $violations)->get()
                    : TrafficViolation::where('code', $violations)->get();
            } else {
                $relatedViolations = collect();
            }
            $admit->relatedViolations = $relatedViolations;
        }

        return view('admitted.view', compact('admitted'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'admittedno' => 'required|string|unique:admitteds,admittedno',
                'top' => 'nullable|string',
                'driver' => 'required|string',
                'apprehending_officer' => 'required|string',
                'violation' => 'required|string',
                'transaction_no' => 'nullable|string',
                'transaction_date' => 'required|date',
                'contact_no' => 'required|string',
                'plate_no' => 'required|string',
                'status' => 'required|string|in:closed,in-progress,settled,unsettled',
                'file_attachment' => 'nullable|array',
                'file_attachment.*' => 'nullable|file|max:512000',
                'typeofvehicle' => 'required|string',
            ]);

            DB::beginTransaction();

            $admitted = new Admitted($validatedData);
            $admitted->violation = json_encode(explode(', ', $validatedData['violation']));
            
            if ($request->hasFile('file_attachment')) {
                $filePaths = [];
                foreach ($request->file('file_attachment') as $index => $file) {
                    $fileName = "ADM-" . $validatedData['admittedno'] . "_doc_" . ($index + 1) . "_" . time();
                    $file->storeAs('attachments', $fileName, 'public');
                    $filePaths[] = 'attachments/' . $fileName;
                }
                $admitted->file_attach = json_encode($filePaths);
            }

            $admitted->save();

            Log::info('Admitted case created', ['admittedno' => $admitted->admittedno]);

            DB::commit();

            return redirect()->back()->with('success', 'Admitted case submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admitted case creation failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to create admitted case');
        }
    }

    public function addRemark(Request $request)
    {
        $request->validate([
            'remarks' => 'required|string',
            'admitted_dataid' => 'required|exists:admitteds,id',
        ]);

        try {
            $admitted = Admitted::findOrFail($request->input('admitted_dataid'));
            $existingRemarks = json_decode($admitted->remarks, true) ?? [];
            
            $timestamp = Carbon::now('Asia/Manila')->format('g:ia m/d/y');
            $newRemark = $request->input('remarks') . ' - ' . $timestamp . ' - ' . auth()->user()->fullname;
            $existingRemarks[] = $newRemark;

            DB::beginTransaction();
            $admitted->update(['remarks' => json_encode($existingRemarks)]);
            DB::commit();

            return response()->json(['message' => 'Remarks saved successfully.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving remarks', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
