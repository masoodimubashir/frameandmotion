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

                {{-- <div class="d-flex align-items-center justify-content-end mb-3">
                    <button class="btn btn-primary" id="addNewBooking" data-toggle="modal" data-target="#bookingModal">
                        <i class="fas fa-plus-circle mr-2 me-2"></i>Add Booking
                    </button>
                </div> --}}

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped mt-3">
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
                            <select class="form-control" style="cursor: pointer" id="client_id" name="client_id">
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
                // Show Add Booking Modal
                $('#addNewBooking').click(function() {
                    $('#bookingForm')[0].reset(); // Reset the form
                    $('#booking_id').val(''); // Clear the booking_id
                    $('#bookingModalLabel').text('Add New Booking'); // Set modal title
                    $('#bookingModal').modal('show'); // Show the modal
                });

                // Edit Booking
                $('.editBooking').click(function() {
                    const bookingId = $(this).data('id'); // Get booking ID

                    $.ajax({
                        url: `/admin/bookings/${bookingId}`, // Fetch the booking data
                        method: 'GET',
                        success: function(response) {
                            if (response.status === 'success') {
                                const booking = response.booking;

                                $('#booking_id').val(booking.id);
                                $('#client_id').val(booking.client_id);
                                $('#booking_date').val(booking.booking_date);
                                $('#ceremony_date').val(booking.ceremony_date);

                                // Change modal title and show it
                                $('#bookingModalLabel').text('Edit Booking');
                                $('#bookingModal').modal('show');
                            } else {
                                swal("Error!", "Unable to fetch booking details.", "error");
                            }
                        },
                        error: function() {
                            swal("Error!", "An unexpected error occurred.", "error");
                        }
                    });
                });

                // Save Booking (Create/Update)
                $('#bookingForm').submit(function(e) {
                    e.preventDefault();

                    const formData = $(this).serialize();
                    const bookingId = $('#booking_id').val();
                    const url = bookingId ? `/admin/bookings/${bookingId}` : '/admin/bookings';
                    const method = bookingId ? 'PUT' : 'POST';

                    $.ajax({
                        url: url,
                        method: method,
                        data: formData,
                        success: function(response) {
                            if (response.status === 'success') {
                                $('#bookingModal').modal('hide');
                                swal("Success!", response.message, "success").then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;

                                // Display validation errors
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

                // Delete Booking
                $('.deleteBooking').click(function() {
                    const bookingId = $(this).data('id');
                    const $row = $(this).closest('tr');

                    swal({
                        title: "Are you sure?",
                        text: "Once deleted, this booking cannot be recovered!",
                        icon: "warning",
                        buttons: ["Cancel", "Delete"],
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                url: `/admin/bookings/${bookingId}`,
                                method: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    if (response.status === 'success') {
                                        $row.remove();
                                        swal("Deleted!", "Booking deleted successfully.",
                                            "success");
                                    }
                                },
                                error: function() {
                                    swal("Error!", "Failed to delete booking.", "error");
                                }
                            });
                        }
                    });
                });

                // Clear form errors on modal close
                $('#bookingModal').on('hidden.bs.modal', function() {
                    $('#bookingForm')[0].reset();
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                    $('#booking_id').val('');
                });
            });
        </script>
    @endpush

</x-app-layout>
