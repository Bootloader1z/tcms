<?php

namespace App\Http\Controllers;

use App\Models\Archives;
use App\Models\ApprehendingOfficer;
use App\Models\TrafficViolation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArchiveController extends Controller
{
    public function create()
    {
        $officers = ApprehendingOfficer::select('officer', 'department')->get();
        $violations = TrafficViolation::orderBy('code', 'asc')->get();
        
        return view('case_archive.manage', compact('officers', 'violations'));
    }

    public function index()
    {
        $archives = Archives::orderByDesc('case_no')->get();
        
        foreach ($archives as $archive) {
            $archive->relatedofficer = ApprehendingOfficer::where('officer', $archive->apprehending_officer)->get();
            
            $remarks = is_string($archive->remarks) ? json_decode($archive->remarks, true) ?? [] : ($archive->remarks ?? []);
            $archive->remarks = $remarks;

            $violations = json_decode($archive->violation);
            if ($violations) {
                $relatedViolations = is_array($violations)
                    ? TrafficViolation::whereIn('code', $violations)->get()
                    : TrafficViolation::where('code', $violations)->get();
            } else {
                $relatedViolations = collect();
            }
            $archive->relatedViolations = $relatedViolations;
        }

        return view('case_archive.view', compact('archives'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'tas_no' => 'required|string|unique:archives,tas_no',
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

            $archive = new Archives($validatedData);
            $archive->violation = json_encode(explode(', ', $validatedData['violation']));
            
            if ($request->hasFile('file_attachment')) {
                $filePaths = [];
                foreach ($request->file('file_attachment') as $index => $file) {
                    $fileName = "ARC-" . $validatedData['tas_no'] . "_doc_" . ($index + 1) . "_" . time();
                    $file->storeAs('attachments', $fileName, 'public');
                    $filePaths[] = 'attachments/' . $fileName;
                }
                $archive->file_attach = json_encode($filePaths);
            }

            $archive->save();

            Log::info('Archive created', ['tas_no' => $archive->tas_no]);

            DB::commit();

            return redirect()->back()->with('success', 'Archive submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Archive creation failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to create archive');
        }
    }
}
