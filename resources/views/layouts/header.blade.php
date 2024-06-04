<header id="header" class="header fixed-top d-flex align-items-center">
  <!-- Loading Screen -->
  <div id="pageLoader" class="page-loader">
    <img src="{{asset('assets/img/logo.png')}}" alt="">
    <div class="spinner-border" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <strong>Loading...</strong>
    
</div>

    <div class="d-flex align-items-center justify-content-between">
      <a href="{{url('/')}}" class="logo d-flex align-items-center">
      <img src="{{asset('assets/img/logo.png')}}" alt="">
                  <span class="d-none d-lg-block">Traffic Adjudication Service </span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

   
    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <div>
          Time&ensp;: <span class="badge badge-primary"style="background-color: white; color: black;" id="horas">NULL</span>
          </div>

        <li class="nav-item dropdown">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
        <script>
          var myVar=setInterval(function(){myTimer()},1000);
          function myTimer() {
              var d = new Date();
              document.getElementById("horas").innerHTML = d.toLocaleTimeString();
          }
          </script>



          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">4</span>
          </a><!-- End Notification Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">
              You have 4 new notifications
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <div>
                <h4>Lorem Ipsum</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>30 min. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-x-circle text-danger"></i>
              <div>
                <h4>Atque rerum nesciunt</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>1 hr. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-check-circle text-success"></i>
              <div>
                <h4>Sit rerum fuga</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>2 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-info-circle text-primary"></i>
              <div>
                <h4>Dicta reprehenderit</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>4 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>
            <li class="dropdown-footer">
              <a href="#">Show all notifications</a>
            </li>

          </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->

  

        <li class="nav-item dropdown pe-3">
          <style>
            .profile-pic {
              width: 100px;
              height: 100px;
              border-radius: 50%;
              object-fit: cover;
            }
          </style>
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            @if(Auth::user()->profile_pic)
                <img src="{{ asset(Auth::user()->profile_pic) }}" alt="User's Profile Picture" class="profile-pic" style="width: 50px; height: 50px;">
            @else
                <img src="{{ asset('assets/img/pzpx.png') }}" alt="Default User Image" style="width: 100px; height: auto; border-radius: 50%;">
            @endif
            <span class="d-none d-md-block dropdown-toggle ps-2">{{Auth::user()->fullname}}</span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              @if(Auth::user()->profile_pic)
                <img src="{{ asset(Auth::user()->profile_pic) }}" alt="User's Profile Picture" class="profile-pic" style="width: 100px; height: 100px;">
              @else
                  <img src="{{ asset('assets/img/pzpx.png') }}" alt="Default User Image" style="width: 100px; height: auto; border-radius: 50%;">
              @endif
              <h6>{{Auth::user()->fullname}}</h6>
              @if (Auth::user()->role == 9)
                  <span>Administrator</span>
              @else
                  <span>Employee</span>
              @endif
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="{{ route('profile', ['id' => Auth::id()]) }}">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            @if (Auth::user()->role == 9)
            <li>
              <a class="dropdown-item d-flex align-items-center" href="{{route('user_management')}}">
                <i class="bi bi-person-fill-add"></i>
                <span>User Management</span>
              </a>
            </li>
            @endif
            <li>
              <a class="dropdown-item d-flex align-items-center" href="{{route('logout')}}">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->