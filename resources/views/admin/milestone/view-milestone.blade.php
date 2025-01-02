<x-app-layout>
    <div class="page-inner">

        <div class="page-header">

            <h3 class="fw-bold mb-3">Milestone</h3>

            <ul class="breadcrumbs mb-3">

                <li class="nav-home">
                    <a href="{{ url('admin/dashboard') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>

                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>

                <li class="nav-item {{ Request::is('admin/users') ? 'active' : '' }}">
                    <a href="{{ url('admin/users') }}">Users</a>
                </li>



            </ul>

        </div>

        <div class="row">

            <div class="col-12">

                <div class="d-flex align-items-center justify-content-end mb-3">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal">
                        <i class="fas fa-plus-circle mr-2 me-2"></i>Add Milestone
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped mt-3">
                                <thead>
                                    <tr>
                                        <th scope="col">Milestone Name</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">User</th>
                                        <th scope="col">Progress Status</th>
                                        <th scope="col">Completion Status</th>
                                        <th scope="col">Start Date</th>
                                        <th scope="col">Completion Date</th>
                                        <th scope="col">Action</th>

                                    </tr>
                                </thead>
                                <tbody id="milestone-table-body">
                                    <!-- Milestone data will be appended here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientModalLabel">Add New Milestone</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="milestoneForm">
                        <!-- Required fields first -->
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="nameError">Name is required</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            <div class="invalid-feedback" id="descriptionError">Description is required</div>
                        </div>

                        <!-- Status checkboxes -->
                        <div class="form-group mb-3">
                            <label class="form-label d-block">Status</label>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="is_in_progress"
                                    name="is_in_progress">
                                <label class="form-check-label" for="is_in_progress">In Progress</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="is_completed" name="is_completed">
                                <label class="form-check-label" for="is_completed">Completed</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveMilestone">Save Milestone</button>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            const tableBody = `
                <tbody id="milestone-table-body">
                    <!-- Loading placeholder -->
                    <tr id="loading-row" style="display: none;">
                        <td colspan="5" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                    <!-- No records placeholder -->
                    <tr id="no-records-row" style="display: none;">
                        <td colspan="5" class="text-center text-danger fw-bold">
                            No Records Found...
                        </td>
                    </tr>
                </tbody>
            `;

            $(document).ready(function() {



                $('#clientModal').on('show.bs.modal', function() {
                    resetForm();
                });


                $('#saveMilestone').click(function() {

                    const form = $('#milestoneForm');

                    const userId = getUserIdFromUrl();

                    $('.is-invalid').removeClass('is-invalid');

                    $('.invalid-feedback').empty();

                    const formData = {
                        name: $('#name').val(),
                        description: $('#description').val(),
                        user_id: userId, // Use the URL user ID instead of form select
                        start_date: $('#start_date').val(),
                        completion_date: $('#completion_date').val(),
                        is_in_progress: $('#is_in_progress').is(':checked'),
                        is_completed: $('#is_completed').is(':checked')
                    };

                    $.ajax({
                        url: `/admin/milestone`,
                        method: 'POST',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {

                            $('#clientModal').modal('hide');

                            Swal({
                                title: "Success!",
                                text: "Milestone created successfully.",
                                icon: "success",
                                confirmButtonText: "OK",
                                customClass: {
                                    confirmButton: "btn btn-success"
                                }
                            });

                            loadMilestones();
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;

                                Object.keys(errors).forEach(function(field) {
                                    $(`#${field}`).addClass('is-invalid');
                                    $(`#${field}Error`).text(errors[field][0]);
                                });

                            } else {
                                Swal({
                                    title: "Error!",
                                    text: "Failed to create milestone.",
                                    icon: "error",
                                    confirmButtonText: "OK",
                                    customClass: {
                                        confirmButton: "btn btn-danger"
                                    }
                                });
                            }
                        }
                    });
                });

                function resetForm() {
                    const form = $('#milestoneForm');
                    form[0].reset();
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').empty();
                }

                fetchMilestones(); // Fetch the first page of milestones

                $(document).on('click', '.deletemilestone', function() {

                    const milestoneId = $(this).data('id');

                    swal({
                        title: "Are you sure?",
                        text: "Once deleted, this milestone cannot be recovered!",
                        icon: "warning",
                        buttons: ["Cancel", "Delete"],
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            // Perform the AJAX DELETE request
                            $.ajax({
                                url: `/admin/milestone/${milestoneId}`,
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    // Handle successful deletion
                                    swal({
                                        title: 'Deleted!',
                                        text: 'The milestone has been deleted.',
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    });

                                    // Remove the milestone row from the table
                                    $(`#milestone-row-${milestoneId}`).remove();
                                },
                                error: function(xhr) {

                                    swal({
                                        title: "Error!",
                                        text: "Failed to delete milestone.",
                                        icon: "error",
                                        confirmButtonText: "OK",
                                        customClass: {
                                            confirmButton: "btn btn-danger"
                                        }
                                    });
                                }
                            });
                        }
                    });
                });

            });

            // Function to get the userId from the URL
            function getUserIdFromUrl() {
                const pathArray = window.location.pathname.split('/');
                return pathArray[2]; // Assuming the userId is the third part of the URL
            }

            // Get the userId
            const userId = getUserIdFromUrl();

            // Function to fetch milestones with pagination
            function fetchMilestones(page = 1) {
                $.ajax({
                    url: `/admin/milestone/${userId}?page=${page}`, // Include page query parameter
                    method: 'GET',
                    success: function(response) {
                        console.log(response); // Log the full response to check its structure

                        $('#milestone-table-body').empty(); // Clear the table body before appending new rows

                        response.user.milestones.forEach(function(milestone) {

                            console.log(milestone);

                            const formatDate = (dateString) => {
                                return dateString ? new Date(dateString).toLocaleDateString() :
                                    'Not Set';
                            };

                            const row = `
                                <tr id="milestone-row-${milestone.id}">
                                    <td>${milestone.name || 'N/A'}</td>
                                    <td>${milestone.description || 'No description'}</td>
                                    <td>
                                        ${response.user.name}
                                    </td>
                                    <td class="text-center">
                                        ${milestone.is_in_progress === 1 ? '<i class="fa fa-check"/> ' : 'Unknown'}
                                    </td>
                                    <td class="text-center">
                                        ${milestone.is_completed === 1 ? '<i class="fa fa-check"/>' : 'Unknown'}
                                    </td>
                                    <td>
                                        ${milestone.start_date || 'Not Set'}
                                    </td>
                                    <td>
                                        ${milestone.completion_date || 'Not Set'}
                                    </td>
                                    <td>
                                        <button class="btn btn-sm editmilestone" data-id="${milestone.id}">
                                            <i class="fas fa-edit text-success fs-5"></i>
                                        </button>
                                        <button class="btn btn-sm deletemilestone" data-id="${milestone.id}">
                                            <i class="fas fa-trash text-danger fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;

                            // Append the row to the table body
                            $('#milestone-table-body').append(row);
                        });

                        // Set up pagination
                        setupPagination(response.user.milestones);
                    },
                    error: function(xhr) {
                        console.error('Error loading milestones:', xhr);
                        // Show error message if needed
                        Swal({
                            title: "Error!",
                            text: "Failed to load milestones.",
                            icon: "error",
                            confirmButtonText: "OK",
                            customClass: {
                                confirmButton: "btn btn-danger"
                            }
                        });
                    }
                });
            }

            // Function to set up pagination buttons
            function setupPagination(pagination) {
                const paginationContainer = $('#pagination-container');
                paginationContainer.empty(); // Clear existing pagination buttons

                if (pagination.current_page > 1) {
                    paginationContainer.append(
                        `<button class="btn btn-sm btn-primary" onclick="fetchMilestones(${pagination.current_page - 1})">Previous</button>`
                    );
                }

                if (pagination.current_page < pagination.last_page) {
                    paginationContainer.append(
                        `<button class="btn btn-sm btn-primary" onclick="fetchMilestones(${pagination.current_page + 1})">Next</button>`
                    );
                }
            }





            //  Edit The Milestoen here

            const milestoneId = $(this).data('id');

            // You can retrieve the row to be edited
            const row = $(`#milestone-row-${milestoneId}`);

            // Get the data from the row, such as name, description, dates, etc.
            const name = row.find('td:nth-child(2)').text();
            const description = row.find('td:nth-child(3)').text();
            const startDate = row.find('td:nth-child(7) input').val();
            const completionDate = row.find('td:nth-child(8) input').val();
            const isInProgress = row.find('td:nth-child(5) input').prop('checked');
            const isCompleted = row.find('td:nth-child(6) input').prop('checked');

            // Here you can show a modal or populate the fields to edit the milestone
            Swal.fire({
                title: 'Edit Milestone',
                html: `
                    <input id="editName" class="swal2-input" value="${name}">
                    <textarea id="editDescription" class="swal2-textarea">${description}</textarea>
                    <input type="date" id="editStartDate" class="swal2-input" value="${startDate}">
                    <input type="date" id="editCompletionDate" class="swal2-input" value="${completionDate}">
                    <label>
                        In Progress
                        <input type="checkbox" id="editIsInProgress" ${isInProgress ? 'checked' : ''}>
                    </label>
                    <label>
                        Completed
                        <input type="checkbox" id="editIsCompleted" ${isCompleted ? 'checked' : ''}>
                    </label>
                `,
                showCancelButton: true,
                confirmButtonText: 'Save Changes',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const updatedMilestone = {
                        name: $('#editName').val(),
                        description: $('#editDescription').val(),
                        start_date: $('#editStartDate').val(),
                        completion_date: $('#editCompletionDate').val(),
                        is_in_progress: $('#editIsInProgress').prop('checked') ? 1 : 0,
                        is_completed: $('#editIsCompleted').prop('checked') ? 1 : 0
                    };

                    // Perform the AJAX PUT request
                    $.ajax({
                        url: `/admin/milestone/${milestoneId}`,
                        method: 'PUT',
                        data: updatedMilestone,
                        success: function(response) {
                            // Handle successful update
                            Swal.fire({
                                title: 'Success!',
                                text: 'Milestone updated successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });

                            // Update the table row with the new data
                            row.find('td:nth-child(2)').text(updatedMilestone.name);
                            row.find('td:nth-child(3)').text(updatedMilestone.description);
                            row.find('td:nth-child(7) input').val(updatedMilestone.start_date);
                            row.find('td:nth-child(8) input').val(updatedMilestone.completion_date);
                            row.find('td:nth-child(5) input').prop('checked', updatedMilestone
                                .is_in_progress);
                            row.find('td:nth-child(6) input').prop('checked', updatedMilestone
                                .is_completed);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to update milestone.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        </script>
    @endpush

</x-app-layout>





{{-- function loadUsers() {
    $.ajax({
        url: '/admin/milestone',
        method: 'GET',
        success: function(response) {

            const userSelect = $('#user_id');

            userSelect.empty().append('<option value="">Select User</option>');

            response.users.forEach(function(user) {
                userSelect.append(
                    `<option value="${user.id}">${user.name}</option>`);
            });
        },
        error: function() {
            Swal({
                title: "Error!",
                text: "Unable to load users.",
                icon: "error",
                confirmButtonText: "OK",
                customClass: {
                    confirmButton: "btn btn-danger"
                }
            });
        }
    });
} --}}
