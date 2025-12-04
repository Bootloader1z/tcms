@if (Auth::check() && Auth::user()->role == 9)

@include('layouts.title')
<!-- Include jQuery from CDN -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<body>
<style>
    .table {
        table-layout: fixed;
    }
    .table td {
        white-space: initial; /* Change 'nowrap' to 'initial' to allow text to wrap */
        overflow: hidden; /* Prevent overflow */
        text-overflow: ellipsis; /* Add an ellipsis for text overflow */
        max-width: 100px; /* Set a maximum width for the text before it wraps */
    }
</style>
  <!-- Include Header -->
  @include('layouts.header')
  <!-- Include Sidebar -->
  @include('layouts.sidebar')
  <main id="main" class="main">
    <div class="container">
        <div class="row">
            <div class="col-md-7">
                <h1>User Management</h1>
            </div>
            <div class="col-md-5 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    Add User
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr> 
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->fullname }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->role == 9)
                            <span class="text-primary">Administrator</span>
                            @elseif($user->role == 2)
                            <span class="text-primary">Encoder</span>
                            @else
                            <span class="text-success">Employee</span>
                            @endif
                        </td>
                        <td>
                            @if ($user->isactive == 1)
                            <span class="text-success">Online</span>
                            @else
                            <span class="text-danger">Offline</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit Profile</a>
                            <a href="{{ route('users.profile', $user->id) }}" class="btn btn-sm btn-info mt-2">Show Profile</a>
                            <button type="button" class="btn btn-sm btn-warning mt-2" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $user->id }}">
                                <i class="bi bi-key"></i> Reset Password
                            </button>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mt-2" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reset Password Modals -->
    @foreach ($users as $user)
    <div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1" aria-labelledby="resetPasswordModalLabel{{ $user->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="resetPasswordModalLabel{{ $user->id }}">
                        <i class="bi bi-key"></i> Reset Password for {{ $user->fullname }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('users.reset-password', $user->id) }}" method="POST">
                        @csrf
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> You are resetting the password for <strong>{{ $user->username }}</strong>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password{{ $user->id }}" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password{{ $user->id }}" name="password" required minlength="8">
                            <div class="form-text">
                                Password must contain:
                                <ul class="small">
                                    <li>At least 8 characters</li>
                                    <li>One uppercase letter (A-Z)</li>
                                    <li>One lowercase letter (a-z)</li>
                                    <li>One number (0-9)</li>
                                    <li>One special character (@$!%*?&)</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_confirmation{{ $user->id }}" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation{{ $user->id }}" name="password_confirmation" required minlength="8">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-key"></i> Reset Password
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        @csrf
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required>
                            <div class="invalid-tooltip">
                                Please enter a valid full name.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <div class="invalid-tooltip">
                                Please enter a valid username.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-tooltip">
                                Please enter a valid email address.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="8">
                            <div class="invalid-tooltip">
                                Please enter a valid password (at least 8 characters).
                            </div>
                            <div class="form-text">
                                Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option selected="selected" disabled="disabled">Select Role</option>
                                <option value="0">Employee</option>
                                <option value="2">Encoder</option>
                                <option value="9">Administrator</option>
                            </select>
                            <div class="invalid-tooltip">
                                Please select a role.
                            </div>
                        </div>
                        <button type="button" id="addUserBtn" class="btn btn-primary">Add User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $("#addUserBtn").click(function(event) {
            event.preventDefault();
            // Validation for required fields
            if ($("#fullname").val() === '' || $("#username").val() === '' || $("#email").val() === '' || $("#password").val() === '' || $("#role").val() === '') {
                if ($("#fullname").val() === '') {
                    $("#fullname").addClass("is-invalid");
                    $("#fullname").next(".invalid-tooltip").text("Please enter a valid full name.");
                }
                if ($("#username").val() === '') {
                    $("#username").addClass("is-invalid");
                    $("#username").next(".invalid-tooltip").text("Please enter a valid username.");
                }
                if ($("#email").val() === '') {
                    $("#email").addClass("is-invalid");
                    $("#email").next(".invalid-tooltip").text("Please enter a valid email address.");
                }
                if ($("#password").val() === '') {
                    $("#password").addClass("is-invalid");
                    $("#password").next(".invalid-tooltip").text("Please enter a valid password (at least 8 characters).");
                }
                if ($("#role").val() === '') {
                    $("#role").addClass("is-invalid");
                    $("#role").next(".invalid-tooltip").text("Please select a role.");
                }
                return false;
            }
            var formData = {
                fullname: $("#fullname").val(),
                username: $("#username").val(),
                email: $("#email").val(),
                password: $("#password").val(),
                role: $("#role").val(),
                _token: $('input[name="_token"]').val()
            };
            $.ajax({
                url: "{{route('store.user')}}",
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#addUserModal').modal('hide');
                    alert('User added successfully!');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    // Show the error message from the server
                    var errorMessage = xhr.responseJSON.message;
                    alert('Error: ' + errorMessage);
                }
            });
        });
        // Clear validation classes and messages on focus
        $("#fullname, #username, #email, #password, #role").focus(function() {
            $(this).removeClass("is-invalid").next(".invalid-tooltip").text("");
        });
    });
</script>
<script>
// Password validation for add user form
$(document).ready(function() {
    // Password strength indicator
    $('#password').on('input', function() {
        var password = $(this).val();
        var strength = 0;
        
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[@$!%*?&]/.test(password)) strength++;
        
        var strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        var strengthColor = ['danger', 'warning', 'info', 'primary', 'success'];
        
        if (password.length > 0) {
            $(this).next('.invalid-tooltip').html(
                '<span class="badge bg-' + strengthColor[strength - 1] + '">' + 
                strengthText[strength - 1] + 
                '</span>'
            );
        }
    });
});
</script>
  <!-- Include Footer -->
  @include('layouts.footer')
</body>

</html>
@else
<script>window.location = '{{route('dashboard')}}';</script>
@endif