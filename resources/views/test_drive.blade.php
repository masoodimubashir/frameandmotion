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
        
    </style>

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


                    {{-- <div id="folderSelection">
                        <select id="folderDropdown">
                            <!-- Folders will be dynamically added here -->
                        </select>
                        <button id="moveToFolderBtn">Move to Folder</button>
                    </div> --}}

                    <button class="btn btn-primary" id="uploadModalTrigger" data-toggle="modal"
                        data-target="#uploadModal">
                        Upload Images
                    </button>

                    <button id="downloadSelected" class="btn btn-success me-2">
                        <i class="fas fa-download me-1"></i>Download Selected
                    </button>
                    <button id="deleteSelected" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete Selected
                    </button>

                </div>

                <div id='tableContainer'>
                </div>

                <!-- Loading Button -->
                <div id="loadingButton" class="loading-overlay" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
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
                            <select v-model="selectedUser" class="form-control" id="user_id" name="user_id">
                                <option value="" disabled selected>Select User</option>

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
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
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
                        <!-- Hidden input to hold image data -->
                        <input type="hidden" name="images" id="deleteImagesInput">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Edit Image Modal -->
    <div class="modal fade" id="editImageModal" tabindex="-1" role="dialog" aria-labelledby="editImageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editImageModalLabel">Edit Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editImageForm" enctype="multipart/form-data">
                        <!-- Ensure you use enctype for file upload -->
                        @csrf
                        <input type="hidden" id="editImageId" name="id">
                        <div class="form-group">
                            <label for="editImageName">Image Name</label>
                            <input type="text" class="form-control" id="editImageName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="editDriveId">Drive ID</label>
                            <input type="text" class="form-control" id="editDriveId" name="drive_id" disabled>
                        </div>
                        <div class="form-group">
                            <label for="editImageFile">Upload New Image</label>
                            <input type="file" class="form-control" id="editImageFile" name="image_file"
                                accept="image/*">
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>




    @push('scripts')
        <script>
            $(document).ready(function() {

                $.ajax({
                    url: '/admin/files',
                    method: 'GET',
                    success: function(response) {
                        const $dropdown = $('#user_id');

                        const users = response.users

                        users.forEach(function(user) {
                            $dropdown.append(new Option(user.name, user.id));
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching users:', error);
                    }
                });

                // Load Images Here
                function loadImages(page = 1) {
                    // Show the loading button
                    $('#loadingButton').show();

                    $.get(`{{ url('admin/files') }}?page=${page}`, function(response) {
                        $('#tableContainer').empty();

                        const files = response.files

                        // Hide the loading button when the data is fetched
                        $('#loadingButton').hide();

                        // Add select all checkbox
                        const selectAllContainer = $('<div>').addClass('mb-3');
                        const selectAllCheckbox = $('<input>')
                            .attr('type', 'checkbox')
                            .attr('id', 'selectAll')
                            .addClass('me-2');
                        const selectAllLabel = $('<label>')
                            .attr('for', 'selectAll')
                            .text('Select All');

                        selectAllContainer.append(selectAllCheckbox, selectAllLabel);
                        $('#tableContainer').append(selectAllContainer);

                        // Create cards container
                        let cardContainer = $('<div>').addClass('card-container d-flex flex-wrap');

                        files.data.forEach(function(file) {

                            let card = $('<div>').addClass('card m-2').css('width', '200px');

                            let preview = $('<img>')
                                .attr('src',
                                    `https://drive.google.com/file/d/${file.drive_id}/preview`)
                                .attr('width', '100%')
                                .attr('height', '300')
                                .attr('frameborder', '0')
                                .css('border-radius', '10px');

                            let cardBody = $('<div>').addClass(
                                'card-body p-2 d-flex flex-column align-items-center');

                            // Checkbox with image data
                            let checkbox = $('<input>')
                                .attr('type', 'checkbox')
                                .addClass('image-checkbox mb-2')
                                .attr('data-id', file.id)
                                .attr('data-drive-id', file.drive_id);

                            // Edit button
                            let editButton = $('<button>')
                                .addClass('btn btn-sm btn-  mb-1 edit-button')
                                .attr('data-id', file.id)
                                .attr('data-name', file.name)
                                .attr('data-drive-id', file.drive_id)
                                .html('<i class="fas fa-edit"></i>'); // Font Awesome Edit Icon

                            // // Delete button
                            // let deleteButton = $('<button>')
                            //     .addClass('btn btn-sm btn-danger delete-button')
                            //     .attr('data-id', file.id)
                            //     .attr('data-drive-id', file.drive_id)
                            //     .html('<i class="fas fa-trash-alt"></i>'); // Font Awesome Delete Icon

                            cardBody.append(checkbox, preview, editButton);
                            card.append(cardBody);
                            cardContainer.append(card);
                        });

                        $('#tableContainer').append(cardContainer);

                        // Add pagination if needed
                        if (response.links) {
                            let pagination = $('<div>').addClass('pagination-container mt-3');
                            response.links.forEach(function(link) {
                                let pageLink = $('<button>')
                                    .html(link.label)
                                    .addClass(link.active ? 'active' : '')
                                    .click(function() {
                                        if (!link.active) {
                                            loadImages(link.url.split('page=')[1]);
                                        }
                                    });
                                pagination.append(pageLink);
                            });
                            $('#tableContainer').append(pagination);
                        }
                    }).fail(function() {
                        // Hide the loading button in case of error
                        $('#loadingButton').hide();
                        alert('Error fetching images.');
                    });
                }



                // Reset the form and configure modal behavior
                $('#uploadModalTrigger').click(function() {
                    $('#uploadForm')[0].reset(); // Reset the upload form
                    $('#uploadModalLabel').text('Upload Images'); // Set modal title
                    $('#uploadModal').modal('show'); // Show the modal
                });


                //  Upoad Form
                $('#uploadForm').on('submit', function(e) {

                    e.preventDefault();

                    let formData = new FormData(this);

                    $.ajax({
                        url: '/admin/files', // Your server endpoint
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-CSRF-TOKEN',
                                '{{ csrf_token() }}'); // CSRF Token
                            $('#uploadForm button').prop('disabled', true).text('Uploading...');
                        },
                        success: function(response) {
                            if (response.success) {
                                swal("Success!", "Files uploaded successfully!", "success").then(
                                    () => {
                                        $('#uploadModal').modal('hide'); // Close the modal
                                        $('#uploadForm')[0].reset(); // Reset the form

                                    });
                            } else {
                                swal("Error!", response.message || "Upload failed.", "error");
                            }
                            $('#uploadForm button').prop('disabled', false).text('Upload');
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                $.each(errors, function(field, messages) {
                                    const $input = $(`[name="${field}"]`);
                                    $input.addClass('is-invalid');
                                    $input.after(
                                        `<div class="invalid-feedback">${messages[0]}</div>`
                                    );
                                });
                                swal("Validation Error!", "Please check the highlighted fields.",
                                    "error");
                            } else {
                                swal("Error!", "An unexpected error occurred.", "error");
                            }
                            $('#uploadForm button').prop('disabled', false).text('Upload');
                        }
                    });
                });




                // Handle delete button click
                // $(document).on('click', '.delete-button', function() {
                //     const imageId = $(this).data('id');
                //     const driveId = $(this).data('drive-id');

                //     // Confirm the deletion
                //     if (confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
                //         $.ajax({
                //             url: '{{ url('admin/files') }}', // Adjust URL to match delete endpoint
                //             type: 'POST',
                //             data: {
                //                 _token: '{{ csrf_token() }}',
                //                 _method: 'DELETE', // Spoof DELETE method for Laravel
                //                 images: [{ // Sending the image as an array with one object
                //                     id: imageId,
                //                     method: 'DELETE',
                //                     drive_id: driveId
                //                 }]
                //             },
                //             success: function(response) {
                //                 if (response.success) {
                //                     alert('Image deleted successfully.');
                //                     loadImages(); // Reload the images after deletion
                //                 } else {
                //                     alert('Error deleting image: ' + response.message);
                //                 }
                //             },
                //             error: function(xhr, status, error) {
                //                 alert('Error deleting image: ' + error);
                //             }
                //         });
                //     }
                // });

                // Delete Selected Images

                $('#deleteSelected').click(function() {

                    const selectedImages = $('.image-checkbox:checked').map(function() {
                        return {
                            id: $(this).data('id'),
                            drive_id: $(this).data('drive-id')
                        };
                    }).get();

                    if (selectedImages.length === 0) {
                        swal("Warning!", "Please Select Your Images", "error");
                        return;
                    }


                    $.ajax({
                        url: '{{ url('admin/files') }}', // Changed to match your route
                        type: 'POST', // Changed to POST
                        data: {
                            _token: '{{ csrf_token() }}', // Laravel CSRF token
                            _method: 'DELETE', // Spoof the DELETE method
                            images: selectedImages // Selected images to delete
                        },
                        success: function(response) {
                            if (response.success) {
                                swal('Success!',
                                    `Successfully deleted ${selectedImages.length} images`,
                                    'success');
                                loadImages(); // Reload the images
                            } else {
                                swal("Error!", "Error Deleting The Images.", "error");

                            }
                        },
                        error: function(xhr, status, error) {
                            swal("Error!", "An unexpected error occurred.", "error");

                        }
                    });
                });


                // Edit The Image
                $(document).on('click', '.edit-button', function() {
                    const imageId = $(this).data('id');
                    const imageName = $(this).data('name');
                    const driveId = $(this).data('drive-id');

                    // Show edit form in modal
                    $('#editImageModal').modal('show');
                    $('#editImageId').val(imageId);
                    $('#editImageName').val(imageName);
                    $('#editDriveId').val(driveId); // Populate Drive ID, but make it disabled
                });


                //  Edit Image Form
                $('#editImageForm').submit(function(e) {
                    e.preventDefault();

                    const imageId = $('#editImageId').val();
                    const imageName = $('#editImageName').val();
                    const imageFile = $('#editImageFile')[0].files[0]; // Get the image file if selected

                    // FormData is used for file uploads
                    let formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('id', imageId);
                    formData.append('name', imageName);
                    if (imageFile) {
                        formData.append('image_file', imageFile); // Add the file to the request
                    }

                    $.ajax({
                        url: '{{ url('admin/files') }}/' + imageId, // Adjust URL to match your route
                        type: 'PUT', // Use PUT method for updating
                        data: formData,
                        processData: false, // Don't process data (required for file uploads)
                        contentType: false, // Don't set content type (required for file uploads)
                        success: function(response) {
                            if (response.success) {
                                alert('Image updated successfully.');
                                $('#editImageModal').modal('hide');
                                loadImages(); // Reload the images
                            } else {
                                alert('Error updating image: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Error updating image: ' + error);
                        }
                    });
                });

                // Handle select all functionality
                $(document).on('change', '#selectAll', function() {
                    $('.image-checkbox').prop('checked', $(this).prop('checked'));
                    updateDeleteButton();
                });

                // Handle individual checkbox changes
                $(document).on('change', '.image-checkbox', function() {
                    updateDeleteButton();
                });

                // Update delete button state
                function updateDeleteButton() {
                    const selectedCount = $('.image-checkbox:checked').length;
                    $('#deleteSelected').text(
                        selectedCount > 0 ? `Delete Selected (${selectedCount})` : 'Delete Selected'
                    ).prop('disabled', selectedCount === 0);
                }


                // Initialize the page
                loadImages();
            });
        </script>
    @endpush


</x-app-layout>
