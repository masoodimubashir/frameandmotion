<x-app-layout>
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Users</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ url('admin/dashboard') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item {{ Request::is('users') ? 'active' : '' }}">
                    <a href="{{ url('admin/users') }}">Users</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-end mb-3">
                    <button class="btn btn-primary" id="addNewUser" data-toggle="modal" data-target="#userModal">
                        <i class="fas fa-plus-circle mr-2 me-2"></i>Add User
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped mt-3">
                                <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Username</th>
                                        <th scope="col">Role</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($users) > 0)
                                        @foreach ($users as $user)
                                            <tr id="user-row-{{ $user->id }}">
                                                <td>
                                                    <a href="{{ url('/admin/' . $user->id . '/view-milestone') }}">
                                                        {{ $user->name }}
                                                    
                                                    </a>

                                                </td>
                                                <td>{{ $user->username }}</td>
                                                <td>{{ ucfirst($user->role_name) }}</td>
                                                <td class="d-flex align-content-center justify-content-start">
                                                    <button class="btn editUser" data-id="{{ $user->id }}">
                                                        <i class="fas fa-edit text-success fs-5 mr-1"></i>
                                                    </button>

                                                    <button class="btn deleteUser" data-id="{{ $user->id }}">
                                                        <i class="fas fa-trash text-danger fs-5 mr-1"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center text-danger fw-bold">No Records
                                                Found...</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Add User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        @csrf
                        <input type="hidden" id="user_id" name="user_id">

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" >
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" >
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>

                        <div class="form-group">
                            <label for="booking_id">Booking</label>
                            <select class="form-control" id="booking_id" name="booking_id">
                                <option value="">Select Booking</option>
                                @foreach ($bookings as $booking)
                                    <option value="{{ $booking->id }}">{{ $booking->booking_date }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Save User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
        <script>
            $(document).ready(function() {
                // Show Add User Modal
                $('#addNewUser').click(function() {
                    resetForm();
                    $('#userModalLabel').text('Add New User');
                    $('#userModal').modal('show');
                });

                // Edit User
                $('.editUser').click(function() {
                    const userId = $(this).data('id');
                    resetForm();

                    $.ajax({
                        url: `/admin/users/${userId}`,
                        method: 'GET',
                        beforeSend: function() {
                            $('#userModalLabel').text('Loading...');
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                const user = response.user;
                                $('#user_id').val(user.id);
                                $('#name').val(user.name);
                                $('#username').val(user.username);
                                $('#password').prop('required',
                                    false); // Password is optional on edit
                                $('#booking_id').val(user.booking_id || '');

                                $('#userModalLabel').text('Edit User');
                                $('#userModal').modal('show');
                            } else {
                                swal("Error!", "Unable to fetch user details.", "error");
                            }
                        },
                        error: function() {
                            swal("Error!", "An unexpected error occurred.", "error");
                        }
                    });
                });

                // Save User (Create/Update)
                $('#userForm').submit(function(e) {
                    e.preventDefault();

                    const formData = $(this).serialize();
                    const userId = $('#user_id').val();
                    const url = userId ? `/admin/users/${userId}` : '/admin/users';
                    const method = userId ? 'PUT' : 'POST';

                    $.ajax({
                        url: url,
                        method: method,
                        data: formData,
                        beforeSend: function() {
                            $('#userForm button[type="submit"]').prop('disabled', true).text(
                                'Saving...');
                        },
                        success: function(response) {
                            $('#userModal').modal('hide');
                            swal("Success!", response.message, "success").then(() => location
                                .reload());
                        },
                        error: function(xhr) {
                            $('#userForm button[type="submit"]').prop('disabled', false).text(
                                'Save User');
                            $('.is-invalid').removeClass('is-invalid');
                            $('.invalid-feedback').remove();

                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                $.each(errors, function(field, messages) {
                                    const $input = $(`[name="${field}"]`);
                                    $input.addClass('is-invalid');
                                    $input.after(
                                        `<div class="invalid-feedback">${messages[0]}</div>`
                                    );
                                });
                            } else {
                                swal("Error!", "An unexpected error occurred.", "error");
                            }
                        }
                    });
                });

                // Delete User
                $('.deleteUser').click(function() {
                    const userId = $(this).data('id');
                    const $row = $(this).closest('tr');

                    swal({
                        title: "Are you sure?",
                        text: "This user will be permanently deleted.",
                        icon: "warning",
                        buttons: {
                            cancel: "Cancel",
                            confirm: "Delete"
                        },
                        dangerMode: true
                    }).then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                url: `/admin/users/${userId}`,
                                method: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    if (response.status === 'success') {
                                        swal("Deleted!", response.message, "success").then(
                                            () => $row.remove());
                                    }
                                },
                                error: function() {
                                    swal("Error!", "Unable to delete user.", "error");
                                }
                            });
                        }
                    });
                });

                // Reset Form and Clear Errors
                function resetForm() {
                    $('#userForm')[0].reset();
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                    $('#is_active_checkbox').prop('checked', false);
                }

                // Clear form on modal close
                $('#userModal').on('hidden.bs.modal', function() {
                    resetForm();
                });
            });
        </script>
    @endpush


</x-app-layout>
