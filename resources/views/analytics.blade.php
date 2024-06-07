

@section('title', env('APP_NAME'))

@include('layouts.title')

<body>
  
  <!-- ======= Header ======= -->
@include('layouts.header')

  <!-- ======= Sidebar ======= -->
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

<!-- Button to trigger modal -->
<div class="container-fluid">
  <div class="row justify-content-center">
      <div class="col-lg-8">
          <div class="card">
              <div class="card-body">
                <h5 class="card-title">Months Report</h5>
                  <!-- Form Start -->
                  <form action="{{ route('filterByMonth') }}" method="get" target="_blank" class="row g-3 needs-validation" novalidate>
                    @csrf <!-- CSRF protection -->
          
                    <div class="col-md-6">
                        <label for="validationTooltipdate" class="form-label">Date</label>
                        <input type="month" name="date_received" class="form-control" id="validationTooltipdate" required>
                        <div class="invalid-tooltip">
                            Please input date.
                        </div>
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
{{-- </div>
<section class="mt-3">
  <div class="form-container">
      
  </div>
</section> --}}



  </main><!-- End #main -->

 @include('layouts.footer')
</body>
<script>
  (function () {
      'use strict'

      // Fetch all the forms we want to apply custom Bootstrap validation styles to
      var forms = document.querySelectorAll('.needs-validation')

      // Loop over them and prevent submission
      Array.prototype.slice.call(forms)
          .forEach(function (form) {
              form.addEventListener('submit', function (event) {
                  if (!form.checkValidity()) {
                      event.preventDefault()
                      event.stopPropagation()
                  }

                  form.classList.add('was-validated')
              }, false)
          })
  })()
</script>
</html>
