@include('layouts.title')

<body>

  <!-- Include Header -->
  @include('layouts.header')

  <!-- Include Sidebar -->
  @include('layouts.sidebar')

  <main id="main" class="main">
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
  @endif
    <div class="container-fluid"> <!-- Make the container wider -->
        <div class="row justify-content-center">
            <div class="col-lg-8"> <!-- Adjusted the width of the column -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Apprehending Officer</h5>
    
                        <form method="POST" action="{{ route('save.offi') }}" class="row g-3 needs-validation" novalidate enctype="multipart/form-data">
                            @csrf
                            
                            <div class="col-md-6 position-relative">
                                <label for="validationTooltipDepartment" class="form-label">Department</label>
                                <input type="text" name="department" class="form-control" id="validationTooltipDepartment"  required>
                                
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" type="submit">Submit form</button>
                            </div>
                        </form>
                        
                        
                        
                        <!-- Form End -->
                    </div>
                </div>
            </div>
        </div>
    </div>
  </main>

  <!-- Include Footer -->
  @include('layouts.footer')
</body>

</html>