<?php

namespace App\Http\Controllers;

use App\Models\TasFile;
use App\Models\ApprehendingOfficer;
use App\Models\TrafficViolation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TasFileController extends Controller
{
    /**
     * Show form to manage TAS files
     */
    public function create()
    {
        $officers = ApprehendingOfficer::select('officer', 'department')->get();
        $violations = TrafficViolation::orderBy('code', 'asc')->get();
        
        return view('tas.manage', compact('officers', 'violations'));
    }

    /**
     * Store a new TAS file
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'case_no' => 'required|string|unique:tas_files,case_no',
                'top' => 'nullable|string',
                'driver' => 'required|string',
                'apprehending_officer' => 'required|string',
                'violation' => 'required|string',
                'transaction_no' => 'nullable|string',
                'date_received' => 'required|date',
                'contact_no' => 'required|string',
                'plate_no' => 'required|string',
                'status' => 'required|string|in:closed,in-progress,settled,unsettled',
                'file_attachment' => 'nullable|array',
                'file_attachment.*' => 'nullable|file|max:512000',
                'typeofvehicle' => 'required|string',
            ]);

            DB::beginTransaction();

            $tasFile = new TasFile([
                'case_no' => $validatedData['case_no'],
                'top' => $validatedData['top'],
                'driver' => $validatedData['driver'],
                'apprehending_officer' => $validatedData['apprehending_officer'],
                'violation' => json_encode(explode(', ', $validatedData['violation'])),
                'transaction_no' => $validatedData['transaction_no'] ? "TRX-LETAS-" . $validatedData['transaction_no'] : null,
                'plate_no' => $validatedData['plate_no'],
                'date_received' => $validatedData['date_received'],
                'contact_no' => $validatedData['contact_no'],
                'status' => $validatedData['status'],
                'typeofvehicle' => $validatedData['typeofvehicle'],
            ]);

            if ($request->hasFile('file_attachment')) {
                $filePaths = [];
                $cx = 1;
                foreach ($request->file('file_attachment') as $file) {
                    $fileName = "CS-" . $validatedData['case_no'] . "_documents_" . $cx . "_" . time();
                    $file->storeAs('attachments', $fileName, 'public');
                    $filePaths[] = 'attachments/' . $fileName;
                    $cx++;
                }
                $tasFile->file_attach = json_encode($filePaths);
            }

            $tasFile->save();

            Log::info('TAS file created', ['case_no' => $tasFile->case_no, 'user_id' => auth()->id()]);

            DB::commit();

            return redirect()->back()->with('success', 'TAS file submitted successfully!');
        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TAS file creation failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to create TAS file: ' . $e->getMessage());
        }
    }

    /**
     * Display all TAS files
     */
    public function index()
    {
        try {
            $tasFiles = TasFile::orderByDesc('case_no')->get();
            
            foreach ($tasFiles as $tasFile) {
                $tasFile->checkCompleteness();
                $tasFile->handleDeletion();

                $officerName = $tasFile->apprehending_officer;
                $tasFile->relatedofficer = ApprehendingOfficer::where('officer', $officerName)->get();
                
                $remarks = is_string($tasFile->remarks) ? json_decode($tasFile->remarks, true) ?? [] : ($tasFile->remarks ?? []);
                $tasFile->remarks = $remarks;

                $violations = json_decode($tasFile->violation);
                if ($violations) {
                    $relatedViolations = is_array($violations) 
                        ? TrafficViolation::whereIn('code', $violations)->get()
                        : TrafficViolation::where('code', $violations)->get();
                } else {
                    $relatedViolations = collect();
                }
                $tasFile->relatedViolations = $relatedViolations;
            }

            return view('tas.view', compact('tasFiles'));
        } catch (\Exception $e) {
            Log::error('Error viewing TAS files', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error loading TAS files');
        }
    }

    /**
     * Add remarks to TAS file
     */
    public function addRemark(Request $request)
    {
        $request->validate([
            'remarks' => 'required|string',
            'tas_file_id' => 'required|exists:tas_files,id',
        ]);

        try {
            $tasFile = TasFile::findOrFail($request->input('tas_file_id'));
            $existingRemarks = json_decode($tasFile->remarks, true) ?? [];
            
            $timestamp = Carbon::now('Asia/Manila')->format('g:ia m/d/y');
            $newRemark = $request->input('remarks') . ' - ' . $timestamp . ' - ' . auth()->user()->fullname;
            $existingRemarks[] = $newRemark;

            DB::beginTransaction();
            $tasFile->update(['remarks' => json_encode($existingRemarks)]);
            DB::commit();

            return response()->json(['message' => 'Remarks saved successfully.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving remarks', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update TAS file status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $tasFile = TasFile::findOrFail($id);
            $tasFile->status = $request->input('status');
            $tasFile->save();

            Log::info('TAS file status updated', ['id' => $id, 'status' => $tasFile->status]);

            return redirect()->back()->with('success', 'Status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Status update failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to update status');
        }
    }

    /**
     * Finish/close a case
     */
    public function finishCase($id)
    {
        try {
            $tasFile = TasFile::findOrFail($id);
            $tasFile->status = 'closed';
            $tasFile->save();

            Log::info('TAS case finished', ['id' => $id]);

            return redirect()->back()->with('success', 'Case finished successfully.');
        } catch (\Exception $e) {
            Log::error('Finish case failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to finish case');
        }
    }
}
