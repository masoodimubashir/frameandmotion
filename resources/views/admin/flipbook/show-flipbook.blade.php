<x-app-layout>



    <style>
        .loading-overlay {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 8px;
            font-size: 18px;
            color: #333;
        }

        .loading-overlay i {
            margin-right: 10px;
        }

        .card {
            transition: box-shadow 0.3s ease;
        }

        .edit-button,
        .image-checkbox {
            transition: opacity 0.3s ease;
        }

        .card img {
            transition: transform 0.3s ease;
        }

        .card:hover img {
            transform: scale(1.02);
        }
    </style>
    </style>

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
                <li class="nav-item {{ Request::is('admin/milestone') ? 'active' : '' }}">
                    <a href="{{ url('admin/milestone') }}">Milestone</a>
                </li>

            </ul>

        </div>

        <div class="row">

            <div class="col-12">


                <div class="d-flex justify-content-end align-items-center">
                    <div>
                        <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
                            data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-upload"></i>
                            Upload Images
                        </button>
                    </div>

                    <div>
                        <button id="deleteSelected" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>

                    <div>
                        <select id="userFilter" class="form-select">
                        </select>

                    </div>


                </div>




                <div id='tableContainer' style="min-height: 900px;" class="mt-5">
                </div>



            </div>

        </div>

    </div>



    <!-- Button to Trigger Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload Images</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf


                        <div class="form-group">
                            <label for="user_id">Users</label>
                            <select v-model="selectedUser" style="cursor: pointer" class="form-control" id="user_id"
                                name="user_id">

                                <option value="" disabled selected>Select User</option>

                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach

                            </select>
                        </div>

                        <div class="form-group">
                            <label for="images">Select Images</label>
                            <input type="file" name="images[]" multiple id="images" accept="image/*"
                                class="form-control">
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    {{-- <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">

        <div class="modal-dialog" role="document">

            <div class="modal-content">

                <form id="deleteForm" method="POST" action="/delete-images">

                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p>Are you sure you want to delete the selected images?</p>
                        <input type="hidden" name="images" id="deleteImagesInput">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>

                </form>

            </div>

        </div>

    </div> --}}

    <!-- Update Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="modalUserId" value=""> <!-- User ID -->
                        <div class="mb-3">
                            <label for="modalImageInput" class="form-label">Select New Image</label>
                            <input type="file" id="modalImageInput" accept="image/jpeg,image/png,image/jpg"
                                class="form-control">
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="modalUpdateButton">Upload</button>
                    </form>
                </div>
                <div class="modal-footer">

                </div>
            </div>
        </div>
    </div>


    {{-- <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateForm" enctype="multipart/form-data">
                        <input type="hidden" id="modalUserId" value=""> <!-- User ID -->
                        <div class="mb-3">
                            <label for="modalImageInput" class="form-label">Select New Image</label>
                            <input type="file" id="modalImageInput" accept="image/jpeg,image/png,image/jpg"
                                class="form-control">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="modalUpdateButton">Save changes</button>
                </div>
            </div>
        </div>
    </div> --}}





    @push('scripts')
        <script>
            function loadImages(page = 1) {

                $('#loadingButton').show();

                const url = buildUrl(page);

                $.get(url, function(response) {
                    $('#loadingButton').hide();
                    updateUserFilter(response.users);
                    renderTableContent(response.files);
                }).fail(function() {
                    $('#loadingButton').hide();
                    swal("Error!", "Failed to load images.", "error");
                });
            }

            function buildUrl(page) {

                const baseUrl = `{{ url('/admin/flipbook') }}`;
                const selectedUserId = $('#userFilter').val();

                const params = new URLSearchParams();
                if (page > 1) params.append('page', page);
                if (selectedUserId) params.append('user_id', selectedUserId);

                return params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
            }


            function updateUserFilter(users) {
                const filterSelect = $('#userFilter');

                if (filterSelect.children().length === 0) {
                    filterSelect.append($('<option>').val('').text('All Users'));

                    users.forEach(user => {
                        filterSelect.append($('<option>').val(user.id).text(user.name));
                    });

                    filterSelect.on('change', function() {
                        loadImages(1);
                    });
                }
            }


            function renderTableContent(files) {
                const tableContainer = $('#tableContainer').empty();

                renderSelectAllSection(tableContainer);
                renderImageCards(files.data, tableContainer);
                renderPagination(files.links, tableContainer);
            }


            function renderSelectAllSection(container) {
                const selectAllContainer = $('<div>').addClass('mb-3');
                const selectAllWrapper = $('<div>').addClass('d-flex align-items-center justify-content-between');

                const checkboxGroup = $('<div>').addClass('d-flex align-items-center');
                const selectAllCheckbox = $('<input>').attr({
                    type: 'checkbox',
                    id: 'selectAll'
                }).addClass('me-2').css({
                    width: '20px',
                    height: '20px'
                });
                const selectAllLabel = $('<label>').attr('for', 'selectAll').addClass('form-check-label fs-5').text(
                    'Select All');

                checkboxGroup.append(selectAllCheckbox, selectAllLabel);
               
                selectAllWrapper.append(checkboxGroup);
               
                selectAllContainer.append(selectAllWrapper);

                container.append(selectAllContainer);

                selectAllCheckbox.change(function() {
                    const isChecked = $(this).prop('checked');
                    $('.image-checkbox').prop('checked', isChecked);
                    updateSelectionInfo();
                });
            }


            function renderImageCards(files, container) {
                const cardContainer = $('<div>').addClass('row');

                if (files.length === 0) {
                    cardContainer.append($('<div>').addClass('col-12 text-center py-5').text('No images found'));
                } else {
                    files.forEach(file => {
                        const cardWrapper = $('<div>').addClass('col-4 mb-4');
                        const card = $('<div>').addClass('h-100 position-relative');

                        const preview = $('<img>').attr('src', `https://lh3.google.com/u/0/d/${file.drive_id}`)
                            .addClass('card-img-top').css({
                                'object-fit': 'cover',
                                'height': '300px',
                                'border-radius': '0.5rem',
                                'box-shadow': '0px 0px 2px black'
                            });

                        // Dynamically creating the Edit button
                        const updateButton = $('<button>')
                            .addClass('btn btn-warning position-absolute top-0 end-0 m-2')
                            .addClass('editButton')
                            .css({
                                'z-index': '2'
                            })
                            .html('<i class="fas fa-edit"></i>')
                            .attr('data-bs-toggle', 'modal')
                            .attr('data-bs-target', '#updateModal')
                            .attr('data-file-id', file.id)
                            .attr('data-user-id', file.user_id) // Add userId if required
                            .on('click', function() {
                                const fileId = $(this).data('file-id');
                                const userId = $(this).data('user-id');

                                const id = $('#modalUserId').val(userId); // Set the user ID in the modal
                                $('#modalUpdateButton').data('file-id', fileId); // Set the file ID on the button
                                $('#updateModal').modal('show'); // Show the modal


                            });

                        if (file.user) {
                            const userLabel = $('<div>').addClass(
                                    'position-absolute bottom-0 start-0 m-2 bg-dark text-white p-1 rounded')
                                .text(file.user.name)
                                .css('z-index', '2');
                            card.append(userLabel);
                        }

                        const checkbox = $('<input>').attr({
                            type: 'checkbox',
                            'data-id': file.id,
                            'data-drive-id': file.drive_id
                        }).addClass('image-checkbox form-check-input position-absolute top-0 start-0 m-2').css({
                            'z-index': '2',
                            'width': '20px',
                            'height': '20px'
                        });

                        card.append(checkbox, preview, updateButton);
                        cardWrapper.append(card);
                        cardContainer.append(cardWrapper);
                    });
                }

                container.append(cardContainer);

                $('.image-checkbox').change(function() {
                    updateSelectionInfo();
                });
            }



            function renderPagination(links, container) {
                if (!links) return;

                const pagination = $('<div>').addClass('pagination-container mt-3');

                links.forEach(link => {
                    const pageLink = $('<button>').html(link.label).addClass(link.active ? 'btn btn-primary mx-1' :
                        'btn btn-outline-primary mx-1').prop('disabled', link.active).click(function() {
                        if (!link.active) {
                            loadImages(new URLSearchParams(link.url.split('?')[1]).get('page'));
                        }
                    });

                    pagination.append(pageLink);
                });

                container.append(pagination);
            }



            function updateSelectionInfo() {
                const selectedCount = $('.image-checkbox:checked').length;
                const deleteButton = $('#deleteSelected');

                if (selectedCount > 0) {
                    deleteButton.removeClass('disabled').text(`Delete Selected (${selectedCount})`);
                } else {
                    deleteButton.addClass('disabled').html('<i class="fa fa-trash"></i>');
                }
            }


            $('#editButton').on('click', function() {
                const fileId = $(this).data('file-id');
                const userId = $(this).data('user-id'); // Get the userId from the button

                $('#modalUserId').val(userId); // Set the user ID in the modal input
                $('#modalUpdateButton').data('file-id', fileId); // Set the file ID on the update button
                $('#updateModal').modal('show'); // Show the modal
            });


            $('#updateForm').on('submit', function(e) {
                e.preventDefault(); // Prevent the default form submission

                // Get fileId from the button where it is stored
                const fileId = $('#modalUpdateButton').data('file-id');
                const formData = new FormData();

                const userId = $('#modalUserId').val(); // Get the user ID from the modal input

                console.log(userId);
                

                const imageInput = $('#modalImageInput')[0].files[0];

                if (imageInput) {
                    formData.append('user_id', userId);
                    formData.append('image', imageInput);

                    // Add _method field for method spoofing (PUT)
                    formData.append('_method', 'PUT');
                } else {
                    alert('Please select an image to upload.');
                    return;
                }

                console.log(formData); // Check the formData content

                // Send the form data via AJAX
                $.ajax({
                    url: `/admin/flipbook/${fileId}`,
                    method: 'POST', // Use POST to simulate PUT request via method spoofing
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.message) {
                            alert(data.message);
                            location.reload(); // Reload page to reflect updates
                        } else if (data.errors) {
                            console.error('Validation Errors:', data.errors);
                            alert('Failed to update file. Please check the console for details.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('An error occurred while updating the file.');
                    }
                });
            });

            $(document).ready(function() {

                // Initial load
                loadImages();

                $('#uploadForm').on('submit', function(e) {
                    e.preventDefault(); // Prevent the default form submission

                    // Prepare form data
                    let formData = new FormData(this);
                    console.log(formData);


                    $.ajax({
                        url: `/admin/flipbook`, // Use the proper endpoint
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                        beforeSend: function() {
                            // Optional: Show a loading spinner
                            $('#uploadForm button[type="submit"]').prop('disabled', true).text(
                                'Uploading...');
                        },
                        success: function(response) {

                            // Handle success response
                            swal("Success!", response.message || 'Files uploaded successfully!',
                                "success");

                            // Optional: Reset the form
                            $('#uploadForm')[0].reset();

                            // Hide the modal
                            $('#uploadModal').modal('hide');

                            window.reload();
                        },
                        error: function(xhr) {
                            // Handle errors
                            if (xhr.status === 422) {
                                // Validation errors
                                const errors = xhr.responseJSON.errors;
                                let errorMessages = '';

                                for (const [key, messages] of Object.entries(errors)) {
                                    errorMessages += `\n${key}: ${messages.join(', ')}`;
                                }

                                swal("Error!", errorMessages, "error");


                                alert(`Validation Errors:${errorMessages}`);
                            } else {

                                swal("Error!", xhr.responseJSON.message, "error");

                            }
                        },
                        complete: function() {
                            // Re-enable the submit button
                            $('#uploadForm button[type="submit"]').prop('disabled', false).text(
                                'Upload');
                        },
                    });
                });

                // Add this to your script
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $('#deleteSelected').click(function() {
                    const selectedImages = $('.image-checkbox:checked').map(function() {
                        return {
                            id: $(this).data('id'),
                            drive_id: $(this).data('drive-id')
                        };
                    }).get();

                    if (selectedImages.length === 0) {
                        swal("Warning!", "Please select at least one image to delete.", "warning");
                        return;
                    }

                    swal({
                            title: "Are you sure?",
                            text: `You are about to delete ${selectedImages.length} image(s). This action cannot be undone.`,
                            icon: "warning",
                            buttons: ["Cancel", "Yes, delete"],
                            dangerMode: true,
                        })
                        .then((willDelete) => {
                            if (willDelete) {
                                $('#deleteSelected').prop('disabled', true).html(
                                    '<i class="fas fa-spinner fa-spin"></i>');

                                const formData = new FormData();
                                formData.append('_method', 'DELETE');
                                formData.append('images', JSON.stringify(selectedImages));

                                $.ajax({
                                    url: `{{ url('/admin/flipbook') }}`,
                                    type: 'POST', // Changed to POST
                                    data: formData,
                                    contentType: false,
                                    processData: false,
                                    success: function(response) {
                                        if (response.success) {
                                            swal("Success!", response.message, "success");
                                            loadImages(); // Reload the image grid
                                        } else {
                                            swal("Error!", response.message, "error");
                                        }
                                    },
                                    error: function(xhr) {
                                        swal("Error!", xhr.responseJSON?.message ||
                                            "Failed to delete images.", "error");
                                    },
                                    complete: function() {
                                        $('#deleteSelected').prop('disabled', false)
                                            .html('<i class="fas fa-trash-alt"></i>');
                                    }
                                });
                            }
                        });
                });


            });
        </script>
    @endpush


</x-app-layout>
