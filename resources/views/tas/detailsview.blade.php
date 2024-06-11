<div class="modal fade" id="finishModal-{{ $tasFile->id }}" tabindex="-1" role="dialog" aria-labelledby="finishModalLabel-{{ $tasFile->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('finish.case', ['id' => $tasFile->id]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="finishModalLabel-{{ $tasFile->id }}">Finish Case</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="fine_fee">Fine Fee</label>
                        <input type="number" step="0.01" class="form-control" id="fine_fee" name="fine_fee" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Finish</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Case Information</h5>
            </div>
            <div class="table-responsive">
            <table class="table table-bordered table-striped">
    <tbody>
        <tr>
            <td class="fw-bold" style="width: 30%;">Case No:</td>
            <td>{{ $tasFile->case_no }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Driver:</td>
            <td>{{ $tasFile->driver }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Contact No:</td>
            <td>{{ $tasFile->contact_no }}</td>
        </tr>
        <tr>
            <td class="fw-bold">TOP:</td>
            <td>{{ $tasFile->top ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Transaction No:</td>
            <td>{{ $tasFile->transaction_no ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Received Date:</td>
            <td>{{ $tasFile->date_received }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Plate No:</td>
            <td>{{ $tasFile->plate_no }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Apprehending Officer:</td>
            <td>{{ $tasFile->apprehending_officer ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Date Recorded:</td>
            <td>{{ $tasFile->created_at }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Case Status:</td>
            <td>{{ $tasFile->status }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Type of Vehicle:</td>
            <td>{{ $tasFile->typeofvehicle }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Record Status</td>
            <td> </td>
        </tr>
    </tbody>
</table>


</div>
</div>
  
 
    </div>

    <div class="col-md-6">
    <div class="card border-0 shadow">
        <div class="card-header">
            <h5 class="card-title mb-0">Violation Details</h5>
        </div>
        <div class="card-body mt-3">
            <div class="mb-4">
                <h6 class="text-muted">Violations:</h6>
                @if (isset($relatedViolations) && !is_array($relatedViolations) && $relatedViolations->count() > 0)
                    <ul class="list-unstyled">
                        @foreach ($relatedViolations as $violation)
                            <li>{{ $violation->code }} - {{ $violation->violation }}</li>
                        @endforeach
                    </ul>
                @else
                    <p>No violations recorded.</p>
                @endif
            </div>
        </div>
    </div>
</div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Remarks</h5>
            </div>
            <div class="card-body mt-3">
                @include('remarksupdate', ['remarks' => $remarks])

                <form action="{{ route('save.remarks') }}" id="remarksForm" method="POST" class="remarksForm">
                    @csrf
                    <input type="hidden" name="tas_file_id" value="{{ $tasFile->id }}">
                    <div class="mt-3">
                        <label for="remarks" class="form-label">Add Remark</label>
                        <hr>
                        <textarea class="form-control" name="remarks" id="remarks" rows="5"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3" id="saveRemarksBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Save Remarks
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">File Attachments</h5>
            </div>
            <div class="card-body mt-3">
                @if (!is_null($tasFile->file_attach))
                    @php
                        $decodedFiles = json_decode($tasFile->file_attach, true);
                    @endphp
                    @if (!is_null($decodedFiles))
                        <ol>
                            @foreach ($decodedFiles as $filePath)
                                <li>
                                    <i class="bi bi-paperclip me-1"></i>
                                    <a href="{{ asset('storage/' . $filePath) }}" target="_blank">{{ basename($filePath) }}</a>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p>No attachments available.</p>
                    @endif
                @else
                    <p>No attachments available.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    {{-- <a href="{{ route('print.sub', ['id' => $tasFile->id]) }}" class="btn btn-primary" onclick="openInNewTabAndPrint('{{ route('print.sub', ['id' => $tasFile->id]) }}'); return false;">
        <span class="bi bi-printer"></span> Print Subpeona
    </a>  --}}
    <form action="{{ route('print.sub', ['id' => $tasFile->id]) }}" method="GET" target="_blank">

        <button type="submit" class="btn btn-primary " name="details" value="motionrelease1">Motion w/Manual Resolution</button>
        <button type="submit" class="btn btn-primary " name="details" value="motionrelease2">Motion w/out Manual Resolution</button>
        <button type="submit" class="btn btn-primary " name="details" value="subpeona">Subpeona</button>
    </form>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#finishModal{{ $tasFile->id }}">Finish</button>
    <form action="{{ route('update.status', ['id' => $tasFile->id]) }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-warning" name="status" value="settled">Settled</button>
        <button type="submit" class="btn btn-danger" name="status" value="Unsettled">Unsettled</button>
    </form>
</div>
