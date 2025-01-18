<x-app-layout>
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Clients</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ url('admin/dashboard') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item {{ Request::is('clients') ? 'active' : '' }}">
                    <a href="{{ url('admin/clients') }}">Clients</a>
                </li>

            </ul>
        </div>
        <div class="row">
            <div class="col-12">

                <div class="d-flex align-items-center justify-content-end mb-3">
                    <button class="btn btn-primary" id="addNewClient" data-toggle="modal" data-target="#clientModal">
                        <i class="fas fa-plus-circle mr-2 me-2"></i>Add Client
                    </button>
                </div>

                <div class="card">

                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-striped  mt-3">
                                <thead>
                                    <tr>
                                        <th scope="col">Status</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Phone</th>
                                        <th scope="col">Booking Date</th>
                                        <th scope="col">Venue</th>
                                        <th scope="col" class="text-center">Action</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($clients) > 0)
                                        @foreach ($clients as $client)
                                            <tr id="client-row-{{ $client->id }}">
                                                @if ($client->is_active)
                                                    <td class="text-success"> Confirmed</td>
                                                @else
                                                    <td class="text-danger"> Unconfirmed</td>
                                                @endif
                                                <td>{{ $client->created_at->format('d-M') }}</td>
                                                <td>{{ $client->name }}</td>
                                                <td>{{ $client->email }}</td>
                                                <td>{{ $client->number }}</td>
                                                <td>{{ $client->date }}</td>
                                                <td>{{ $client->venue }}</td>
                                                <td class="d-flex align-content-center justify-content-start">

                                                    <button class="btn editClient" data-id="{{ $client->id }}">
                                                        <i class="fas fa-edit text-success  "></i>
                                                    </button>

                                                    <button class="btn deleteClient" data-id="{{ $client->id }}">
                                                        <i class="fas fa-trash text-danger "></i>
                                                    </button>

                                                    @if (!$client->is_active)
                                                        {{-- <form method="POST" action="{{ route('confirm-bookings') }}" class="d-inline">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="id"
                                                                value="{{ $client->id }}">
                                                            <input type="hidden" name="is_active" value="1">
                                                            <button type="submit"
                                                                class="btn btn-sm badge badge-success mt-2 me-1">Accept</button>
                                                        </form> --}}


                                                        <a class="btn btn-sm badge badge-success mt-2 me-1"
                                                            href="javascript:void(0)" data-id="{{ $client->id }}"
                                                            data-status="1" onclick="updateBooking(this)">Accept</a>
                                                        <a class="btn btn-sm badge badge-danger mt-2"
                                                            href="javascript:void(0)" data-id="{{ $client->id }}"
                                                            data-status="0" onclick="updateBooking(this)">Cancel</a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center text-danger fw-bold">No Records
                                                Found...
                                            </td>
                                        </tr>
                                    @endif


                                </tbody>
                            </table>
                        </div>
                        {{ $clients->links() }}
                    </div>
                </div>

            </div>

        </div>
    </div>



    <!-- Modal for Add/Edit Client -->
    <div class="modal fade" id="clientModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientModalLabel">Add Client</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="clientForm">

                        @csrf

                        <input type="hidden" id="client_id" name="client_id">

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Enter name">
                            </div>

                            <div class="col-md-6">
                                <label for="number">Number</label>
                                <input type="text" class="form-control" id="number" name="number"
                                    placeholder="Enter phone number">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="venue">Venue</label>
                                <input type="text" class="form-control" id="venue" name="venue"
                                    placeholder="Enter Venue">
                            </div>

                            <div class="col-md-6">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Enter email">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" class="form-control" id="date" name="date"
                                placeholder="Enter date">
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message" placeholder="Enter message" rows="3"></textarea>
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Save Client</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
        <script>
            function updateBooking(button) {

                let bookingId = $(button).data('id');
                let status = $(button).data('status');

                // Show a confirmation alert before sending the request
                swal({
                    title: "Are you sure?",
                    text: status == 1 ? "You are accepting this booking!" : "You are canceling this booking!",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: null,
                            visible: true,
                            className: "btn btn-secondary",
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, proceed!",
                            value: true,
                            visible: true,
                            className: "btn btn-primary",
                            closeModal: false
                        }
                    }
                }).then((isConfirmed) => {
                    if (isConfirmed) {
                        // Make the AJAX request
                        $.ajax({
                            url: '/admin/confirm-bookings',
                            type: 'PUT', 
                            data: {
                                id: bookingId,
                                is_active: status,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                console.log(response);

                                if (response.success) {
                                    swal({
                                        title: "Success!",
                                        text: "Booking Updated!",
                                        icon: "success",
                                        buttons: {
                                            confirm: {
                                                text: "OK",
                                                className: "btn btn-success"
                                            }
                                        }
                                    }).then(() => {
                                        // Refresh the page after the alert is closed
                                        location.reload();
                                    });
                                } else {
                                    swal({
                                        title: "Error!",
                                        text: response.message ||
                                            "Failed to book the client.",
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
                            error: function(xhr, status, error) {
                                console.error('Error:', error);
                                swal({
                                    title: "Error!",
                                    text: "An error occurred. Please try again.",
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
            }


            $(document).ready(function() {

                // Show Add Client Modal
                $('#addNewClient').click(function() {
                    $('#clientForm')[0].reset(); // Reset the form
                    $('#client_id').val(''); // Clear the client_id
                    $('#clientModalLabel').text('Add New Client'); // Set modal title
                    $('#clientModal').modal('show'); // Show the modal
                });






                // Edit Client
                $('.editClient').click(function() {
                    const clientId = $(this).data('id'); // Get client ID from the button

                    $.ajax({
                        url: `/admin/clients/${clientId}`, // Fetch the client data
                        method: 'GET',
                        success: function(response) {
                            if (response.status === 'success') {
                                const client = response.client;

                                $('#client_id').val(client.id);
                                $('input[name="name"]').val(client.name);
                                $('input[name="email"]').val(client.email);
                                $('input[name="phone"]').val(client.number);
                                $('input[name="venue"]').val(client.venue);
                                $('input[name="date"]').val(client.date);
                                $('textarea[name="message"]').val(client.message);


                                // Change modal title and show it
                                $('#clientModalLabel').text('Edit Client');
                                $('#clientModal').modal('show');
                            } else {
                                swal({
                                    title: "Error!",
                                    text: "Unable to fetch client details.",
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
                                text: "An unexpected error occurred while fetching client data.",
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


                // Save Client (Create/Update)
                $('#clientForm').submit(function(e) {
                    e.preventDefault(); // Prevent default form submission

                    const formData = $(this).serialize(); // Serialize form data
                    const clientId = $('#client_id').val(); // Get client ID from the hidden input
                    const url = clientId ? `/admin/clients/${clientId}` :
                        '/admin/clients'; // Determine the URL (POST for create, PUT for update)
                    const method = clientId ? 'PUT' : 'POST'; // Use PUT if editing, POST if creating

                    $.ajax({
                        url: url,
                        method: method,
                        data: formData, // Send form data
                        success: function(response) {
                            if (response.status === 'success') {
                                $('#clientModal').modal('hide'); // Close the modal on success
                                swal({
                                    title: "Success!",
                                    text: response.message, // Display success message
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
                                        `<div class="invalid-feedback">${messages[0]}</div>` // Show error message
                                    );
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


                // Delete Client
                $('.deleteClient').click(function() {
                    const clientId = $(this).data('id');
                    const $row = $(this).closest('tr');

                    swal({
                        title: "Are you sure?",
                        text: "Once deleted, you will not be able to recover this client!",
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
                                url: `/admin/clients/${clientId}`,
                                method: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    if (response.status === 'success') {
                                        swal({
                                            title: "Deleted!",
                                            text: "Client has been deleted successfully.",
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
                                        text: "Failed to delete client. Please try again.",
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
                $('#clientModal').on('hidden.bs.modal', function() {
                    $('#clientForm')[0].reset(); // Reset the form
                    $('.is-invalid').removeClass('is-invalid'); // Clear error classes
                    $('.invalid-feedback').remove(); // Remove error messages
                    $('#client_id').val(''); // Clear the hidden client_id field
                });
            });
        </script>
    @endpush

</x-app-layout>
