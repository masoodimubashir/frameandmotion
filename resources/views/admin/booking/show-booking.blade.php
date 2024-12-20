<x-app-layout>

    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Bookings</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ url('admin/dashboard') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item {{ Request::is('bookings') ? 'active' : '' }}">
                    <a href="{{ url('admin/bookings') }}">Bookings</a>
                </li>

            </ul>
        </div>
        <div class="row">
            <div class="col-12">

                <div class="d-flex align-items-center justify-content-end mb-3">
                    <button class="btn btn-primary" id="addNewBooking" data-toggle="modal" data-target="#bookingModal">
                        <i class="fas fa-plus-circle mr-2 me-2"></i>Add Booking
                    </button>
                </div>

                <div class="card">

                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-striped  mt-3">
                                <thead>
                                    <tr>
                                        <th scope="col">Date</th>
                                        <th scope="col">Client</th>
                                        <th scope="col">Booking Date</th>
                                        <th scope="col">Ceremony Date</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>


                                    @if (count($bookings) > 0)
                                        @foreach ($bookings as $booking)
                                            <tr id="booking-row-{{ $booking->id }}">

                                                <td>{{ $booking->created_at->format('d-M') }}</td>
                                                <td>{{ $booking->client->name }}</td>
                                                <td>{{ $booking->booking_date }}</td>
                                                <td>{{ $booking->ceremony_date }}</td>
                                                <td class="d-flex align-content-center justify-content-start">

                                                    <button class="btn editBooking" data-id="{{ $booking->id }}">
                                                        <i class="fas fa-edit text-success fs-5 mr-1"></i>
                                                    </button>

                                                    <button class="btn deleteBooking" data-id="{{ $booking->id }}">
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
                        {{ $bookings->links() }}
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Modal for Add/Edit Booking -->
    <div class="modal fade" id="bookingModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Add Booking</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="bookingForm">
                        @csrf

                        <input type="hidden" id="booking_id" name="booking_id">

                        <div class="form-group">
                            <label for="client_id">Client</label>
                            <select class="form-control" id="client_id" name="client_id">
                                <option value="" disabled selected>Select a client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="booking_date">Booking Date</label>
                            <input type="date" class="form-control" id="booking_date" name="booking_date">
                        </div>

                        <div class="form-group">
                            <label for="ceremony_date">Ceremony Date</label>
                            <input type="date" class="form-control" id="ceremony_date" name="ceremony_date">
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Save Booking</button>
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
                    $('#userForm')[0].reset(); // Reset the form
                    $('#user_id').val(''); // Clear the user_id
                    $('#userModalLabel').text('Add New User'); // Set modal title
                    $('#userModal').modal('show'); // Show the modal
                });

                // Edit User
                $('.editUser').click(function() {
                    const userId = $(this).data('id'); // Get user ID from the button

                    $.ajax({
                        url: `/admin/users/${userId}`, // Fetch the user data
                        method: 'GET',
                        success: function(response) {
                            if (response.status === 'success') {
                                const user = response.user;

                                $('#user_id').val(user.id);
                                $('input[name="name"]').val(user.name);
                                $('input[name="email"]').val(user.email);
                                $('input[name="password"]').val(''); // Do not pre-fill password
                                $('select[name="role"]').val(user.role);

                                // Change modal title and show it
                                $('#userModalLabel').text('Edit User');
                                $('#userModal').modal('show');
                            } else {
                                swal({
                                    title: "Error!",
                                    text: "Unable to fetch user details.",
                                    icon: "error",
                                    buttons: {
                                        confirm: {
                                            text: "OK",
                                            className: "btn btn-danger"
                                        }
                                    }
                                });
                            }
                        },
                        error: function() {
                            swal({
                                title: "Error!",
                                text: "An unexpected error occurred while fetching user data.",
                                icon: "error",
                                buttons: {
                                    confirm: {
                                        text: "OK",
                                        className: "btn btn-danger"
                                    }
                                }
                            });
                        }
                    });
                });

                // Save User (Create/Update)
                $('#userForm').submit(function(e) {
                    e.preventDefault(); // Prevent default form submission

                    const formData = $(this).serialize(); // Serialize form data
                    const userId = $('#user_id').val(); // Get user ID from the hidden input
                    const url = userId ? `/admin/users/${userId}` : '/admin/users'; // Determine the URL
                    const method = userId ? 'PUT' : 'POST'; // Use PUT if editing, POST if creating

                    $.ajax({
                        url: url,
                        method: method,
                        data: formData, // Send form data
                        success: function(response) {
                            if (response.status === 'success') {
                                $('#userModal').modal('hide'); // Close the modal on success
                                swal({
                                    title: "Success!",
                                    text: response.message,
                                    icon: "success",
                                    buttons: {
                                        confirm: {
                                            text: "OK",
                                            className: "btn btn-success"
                                        }
                                    }
                                }).then(() => {
                                    location.reload(); // Reload the page to reflect changes
                                });
                            }
                        },
                        error: function(xhr) {
                            // Clear previous error messages
                            $('.is-invalid').removeClass('is-invalid');
                            $('.invalid-feedback').remove();

                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;

                                // Display validation errors
                                $.each(errors, function(field, messages) {
                                    const $input = $(`[name="${field}"]`);
                                    $input.addClass('is-invalid'); // Add error class
                                    $input.after(
                                        `<div class="invalid-feedback">${messages[0]}</div>`
                                    ); // Show error message
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: "An unexpected error occurred. Please try again.",
                                    icon: "error",
                                    buttons: {
                                        confirm: {
                                            text: "OK",
                                            className: "btn btn-danger"
                                        }
                                    }
                                });
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
                        text: "Once deleted, you will not be able to recover this user!",
                        icon: "warning",
                        buttons: {
                            cancel: {
                                text: "Cancel",
                                visible: true,
                                className: "btn btn-secondary"
                            },
                            confirm: {
                                text: "Delete",
                                className: "btn btn-danger"
                            }
                        },
                        dangerMode: true,
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
                                        swal({
                                            title: "Deleted!",
                                            text: "User has been deleted successfully.",
                                            icon: "success",
                                            buttons: {
                                                confirm: {
                                                    text: "OK",
                                                    className: "btn btn-success"
                                                }
                                            }
                                        }).then(() => {
                                            $row
                                        .remove(); // Remove the row from the table
                                        });
                                    }
                                },
                                error: function() {
                                    swal({
                                        title: "Error!",
                                        text: "Failed to delete user. Please try again.",
                                        icon: "error",
                                        buttons: {
                                            confirm: {
                                                text: "OK",
                                                className: "btn btn-danger"
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    });
                });

                // Clear form errors on modal close
                $('#userModal').on('hidden.bs.modal', function() {
                    $('#userForm')[0].reset(); // Reset the form
                    $('.is-invalid').removeClass('is-invalid'); // Clear error classes
                    $('.invalid-feedback').remove(); // Remove error messages
                    $('#user_id').val(''); // Clear the hidden user_id field
                });
            });
        </script>
    @endpush


</x-app-layout>
