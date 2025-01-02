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


                <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal"
                    data-bs-target="#uploadModal">
                    <i class="fas fa-upload"></i>
                    Upload Images
                </button>w

                <div id='tableContainer' style="min-height: 900px;">
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
                    <form id="editImageForm">


                        <input type="hidden" id="editImageId" name="id">


                        <label for="user">Users</label>
                        <select v-model="selectedUser" class="form-control" id="user" name="user_id">
                            <option value="" disabled selected>Select User</option>
                            <!-- Options will be appended dynamically -->
                        </select>

                        {{-- <div class="form-group">
                            <label for="editImageName">Image Name</label>
                            <input type="text" class="form-control" id="editImageName" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="editDriveId">Drive ID</label>
                            <input type="text" class="form-control" id="editDriveId" name="drive_id" disabled>
                        </div> --}}

                        <div class="mt-2">
                            <label for="editImageFile">Upload New Image</label>
                            <input type="file" class="form-control" id="editImageFile" name="image"
                                accept="image/*">
                        </div>



                        <div class="mt-2 text-right">
                            <button type="submit" class="btn btn-primary editButton">Save Changes</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>




    @push('scripts')
        <script>
            $(document).ready(function() {

                function loadUsers() {
                    $.ajax({
                        url: '/admin/flipbook',
                        type: 'GET',
                        success: function(response) {

                            const users = response.users;

                            const userDropdown = $('#user_id');

                            userDropdown.empty();

                            userDropdown.append(
                                '<option value="" disabled selected>Select User</option>'
                            );

                            users.forEach(function(user) {
                                userDropdown.append(
                                    `<option value="${user.id}">${user.name}</option>`);
                            });

                        },
                        error: function(xhr, status, error) {
                            swal("Error!", "Failed To Load Users.", "error");

                        },
                    });
                }

                function fetchUsers() {
                    $.ajax({
                        url: '/admin/flipbook', // Your endpoint for fetching users
                        type: 'GET',
                        success: function(response) {

                            const users = response.users;

                            const userDropdown = $('#user'); // Target the select element

                            userDropdown.empty(); // Clear existing options

                            userDropdown.append(
                                '<option value="" disabled selected>Select User</option>'
                            ); // Default option

                            // Populate the dropdown with user data
                            users.forEach(function(user) {
                                userDropdown.append(
                                    `<option value="${user.id}">${user.name}</option>`);
                            });
                        },
                        error: function(xhr, status, error) {
                            swal("Error!", "Failed To Load Users.", "error");

                        },
                    });
                }



                function loadImages(page = 1) {
                    $('#loadingButton').show();

                    $.get(`{{ url('admin/flipbook') }}?page=${page}`, function(response) {
                        $('#tableContainer').empty();
                        const files = response.files;
                        $('#loadingButton').hide();

                        // Select All Section
                        const selectAllContainer = $('<div>').addClass('mb-3 d-flex align-items-center');

                        const selectAllCheckbox = $('<input>')
                            .attr('type', 'checkbox')
                            .attr('id', 'selectAll')
                            .addClass('me-2 form-check-input')
                            .css({
                                'width': '25px',
                                'height': '25px'
                            });

                        const selectAllLabel = $('<label>')
                            .attr('for', 'selectAll')
                            .addClass('form-check-label fs-5')
                            .text('Select All');

                        selectAllContainer.append(selectAllCheckbox, selectAllLabel);
                        $('#tableContainer').append(selectAllContainer);

                        // Create grid container
                        let cardContainer = $('<div>').addClass('row g-4').css({
                            'height': '600px',
                            'overflow-y': 'scroll',
                            'scrollbar-width': 'none', // For Firefox
                            '-ms-overflow-style': 'none', // For Internet Explorer and Edge
                            '&::-webkit-scrollbar': { // For Chrome, Safari, and Opera
                                'display': 'none'
                            }
                        });

                        files.data.forEach(function(file) {
                            // Card wrapper with bootstrap grid
                            let cardWrapper = $('<div>').addClass('col-sm-6 col-md-4 col-lg-3');

                            // Main card container
                            let card = $('<div>')
                                .addClass('card')
                                .css({
                                    'position': 'relative',
                                    'overflow': 'hidden',
                                    'border': 'none',
                                    'border-radius': '10px',
                                    'box-shadow': '0 0 5px black'
                                });

                            // Controls overlay container
                            let controlsOverlay = $('<div>')
                                .addClass('position-absolute w-100 p-2 d-flex justify-content-between')
                                .css({
                                    'top': '0',
                                    'left': '0',
                                    'z-index': '2',
                                    'background': 'linear-gradient(180deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0) 100%)'
                                });

                            // Edit button (top left)
                            let editButton = $('<button>')
                                .addClass('edit-button')
                                .attr({
                                    'data-id': file.id,
                                    'data-name': file.name,
                                    'data-drive-id': file.drive_id,
                                    'data-user-id': file.user_id
                                })
                                .html('<i class="fas fa-edit fs-5"></i>')
                                .css({
                                    'background': '#4CAF50',
                                    'border': 'none',
                                    'border-radius': '50%',
                                    'width': '40px',
                                    'height': '40px',
                                    'color': 'white',
                                    'cursor': 'pointer',
                                    'box-shadow': '0 2px 5px rgba(0,0,0,0.2)',
                                    'opacity': '0.9',
                                    'transition': 'all 0.3s ease',
                                    'display': 'flex',
                                    'align-items': 'center',
                                    'justify-content': 'center',
                                    'position': 'relative',
                                    'overflow': 'hidden'
                                })
                                .hover(
                                    function() { // Mouse enter
                                        $(this).css({
                                            'opacity': '1',
                                            'transform': 'scale(1.05)',
                                            'box-shadow': '0 4px 8px rgba(0,0,0,0.3)',
                                            'background': '#45a049'
                                        });
                                    },
                                    function() { // Mouse leave
                                        $(this).css({
                                            'opacity': '0.9',
                                            'transform': 'scale(1)',
                                            'box-shadow': '0 2px 5px rgba(0,0,0,0.2)',
                                            'background': '#4CAF50'
                                        });
                                    }
                                );

                            // Checkbox container (top right)
                            let checkbox = $('<input>')
                                .attr({
                                    'type': 'checkbox',
                                    'data-id': file.id,
                                    'data-drive-id': file.drive_id
                                })
                                .addClass('image-checkbox form-check-input fs-5')
                                .css({
                                    'transform': 'scale(1.2)',
                                    'opacity': '0.9'
                                });

                            // Add controls to overlay
                            controlsOverlay.append(editButton, checkbox);

                            // Image container with aspect ratio
                            let imageContainer = $('<div>')
                                .addClass('position-relative')
                                .css({
                                    'height': '100%',
                                    'min-height': '200px'
                                });

                            // Image element
                            let preview = $('<img>')
                                .attr('src', `https://lh3.google.com/u/0/d/${file.drive_id}`)
                                .css({
                                    'object-fit': 'cover',
                                    'border-radius': '10px',
                                    'width': '100%',
                                    'height': '300px',
                                    'cursor': 'pointer'
                                });

                            // Filename overlay at bottom
                            let fileNameOverlay = $('<div>')
                                .addClass('position-absolute bottom-0 w-100 p-2')
                                .css({
                                    'background': 'linear-gradient(0deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%)',
                                    'border-radius': '0 0 10px 10px'
                                });

                            // Assemble the card
                            imageContainer.append(preview);
                            card.append(controlsOverlay, imageContainer, fileNameOverlay);
                            cardWrapper.append(card);
                            cardContainer.append(cardWrapper);
                        });

                        $('#tableContainer').append(cardContainer);

                        // Pagination
                        if (response.files.links) {
                            let pagination = $('<div>').addClass(
                                'pagination-container mt-3 d-flex justify-content-center');
                            response.files.links.forEach(function(link) {
                                let pageLink = $('<button>')
                                    .html(link.label)
                                    .addClass(link.active ? 'btn btn-primary mx-1' :
                                        'btn btn-outline-primary mx-1')
                                    .prop('disabled', link.active)
                                    .click(function() {
                                        if (!link.active) {
                                            loadImages(link.url.split('page=')[1]);
                                        }
                                    });
                                pagination.append(pageLink);
                            });
                            $('#tableContainer').append(pagination);
                        }

                        // Hover effects
                        $('.card').hover(
                            function() {
                                $(this).find('.edit-button, .image-checkbox').css('opacity', '1');
                                $(this).css('box-shadow', '0 4px 8px rgba(0,0,0,0.2)');
                            },
                            function() {
                                $(this).find('.edit-button, .image-checkbox').css('opacity', '0.9');
                                $(this).css('box-shadow', '0 2px 4px rgba(0,0,0,0.1)');
                            }
                        );

                    }).fail(function() {
                        $('#loadingButton').hide();
                        swal("Error!", "Failed To Load Images.", "error");
                    });
                }


                // Load Images Here
                // function loadImages(page = 1) {
                //     // Show the loading button
                //     $('#loadingButton').show();

                //     $.get(`{{ url('admin/flipbook') }}?page=${page}`, function(response) {
                //         $('#tableContainer').empty();

                //         const files = response.files;

                //         // Hide the loading button when the data is fetched
                //         $('#loadingButton').hide();

                //         // Add select all checkbox
                //         const selectAllContainer = $('<div>').addClass('mb-3 d-flex align-items-center');
                //         const selectAllCheckbox = $('<input>')
                //             .attr('type', 'checkbox')
                //             .attr('id', 'selectAll')
                //             .addClass('me-2 form-check-input');
                //         const selectAllLabel = $('<label>')
                //             .attr('for', 'selectAll')
                //             .addClass('form-check-label')
                //             .text('Select All');

                //         selectAllContainer.append(selectAllCheckbox, selectAllLabel);
                //         $('#tableContainer').append(selectAllContainer);

                //         // Create a responsive grid container for cards
                //         let cardContainer = $('<div>').addClass('row g-4');

                //         files.data.forEach(function(file) {



                //             // Card wrapper with bootstrap grid
                //             let cardWrapper = $('<div>').addClass('col-sm-6 col-md-4 col-lg-3');

                //             let card = $('<div>')
                //                 .addClass('card h-100 shadow-sm rounded')
                //                 .css({
                //                     'overflow': 'hidden',
                //                     'border': 'none',
                //                     'border-radius': '10px',
                //                 });


                //             // Card preview area
                //             let preview = $('<img>')
                //                 .attr('src', `https://lh3.google.com/u/0/d/${file.drive_id}`) 
                //                 .attr('width', '100%')
                //                 .attr('height', '200')
                //                 .attr('frameborder', '0')
                //                 .addClass('card-img-top')
                //                 .css({
                //                     'border-radius': '10px 10px 0 0',
                //                 });

                //             // Card body with file details
                //             let cardBody = $('<div>').addClass('card-body text-center p-1');

                //             let fileName = $('<h6>')
                //                 .addClass('card-title text-truncate')
                //                 .css('max-width', '100%'); // Ensure long names truncate properly

                //             // Checkbox with image data
                //             let checkbox = $('<input>')
                //                 .attr('type', 'checkbox')
                //                 .addClass('image-checkbox form-check-input mb-3')
                //                 .attr('data-id', file.id)
                //                 .attr('data-drive-id', file.drive_id);

                //             // Action buttons
                //             let actionButtons = $('<div>').addClass(
                //                 'd-flex justify-content-around mt-2');

                //             let editButton = $('<button>')
                //                 .addClass('btn btn-sm btn-warning edit-button')
                //                 .attr('data-id', file.id)
                //                 .attr('data-name', file.name)
                //                 .attr('data-drive-id', file.drive_id)
                //                 .html('<i class="fas fa-edit"></i>'); // Font Awesome Edit Icon

                //             // Uncomment if you want a delete button
                //             // let deleteButton = $('<button>')
                //             //     .addClass('btn btn-sm btn-danger delete-button')
                //             //     .attr('data-id', file.id)
                //             //     .attr('data-drive-id', file.drive_id)
                //             //     .html('<i class="fas fa-trash-alt"></i>'); // Font Awesome Delete Icon

                //             actionButtons.append(editButton); // Add buttons to container
                //             cardBody.append(checkbox, fileName, actionButtons); // Add to card body

                //             card.append(preview, cardBody); // Combine card components
                //             cardWrapper.append(card); // Place card in grid column
                //             cardContainer.append(cardWrapper); // Add column to grid
                //         });

                //         $('#tableContainer').append(cardContainer);


                //         // Add pagination if needed
                //         if (response.files.links) {
                //             console.log(response.files.links);

                //             let pagination = $('<div>').addClass(
                //                 'pagination-container mt-3 d-flex justify-content-center');
                //             response.files.links.forEach(function(link) {
                //                 let pageLink = $('<button>')
                //                     .html(link.label)
                //                     .addClass(link.active ? 'btn btn-primary mx-1' :
                //                         'btn btn-outline-primary mx-1')
                //                     .prop('disabled', link.active) // Disable the active button
                //                     .click(function() {
                //                         if (!link.active) {
                //                             loadImages(link.url.split('page=')[1]);
                //                         }
                //                     });
                //                 pagination.append(pageLink);
                //             });
                //             $('#tableContainer').append(pagination);
                //         }
                //     }).fail(function() {
                //         // Hide the loading button in case of error
                //         $('#loadingButton').hide();
                //         swal("Error!", "Failed To Load Users.", "error");
                //     });
                // }


                // Reset the form and configure modal behavior
                $('#uploadModalTrigger').click(function() {
                    $('#uploadForm')[0].reset(); // Reset the upload form
                    $('#uploadModalLabel').text('Upload Images'); // Set modal title
                    $('#uploadModal').modal('show'); // Show the modal
                });
                // Upload Form
                $('#uploadForm').on('submit', function(e) {
                    e.preventDefault();
                    let formData = new FormData(this);

                    $.ajax({
                        url: '/admin/flipbook',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                            $('.is-invalid').removeClass('is-invalid');
                            $('.invalid-feedback').remove();
                            $('#uploadForm button').prop('disabled', true).text('Uploading...');
                        },
                        success: function(response) {
                            if (response.success) {
                                swal("Success!", "Files uploaded successfully!", "success").then(
                                    () => {
                                        $('#uploadModal').modal('hide');
                                        $('#uploadForm')[0].reset();
                                        loadImages();
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
                                    field = field.replace(/\./g, '[').split('[')[0];
                                    const $input = $(
                                        `[name="${field}"], [name="${field}[]"]`);
                                    $input.addClass('is-invalid');
                                    $input.after(
                                        `<div class="invalid-feedback">${messages[0]}</div>`
                                    );
                                });
                            } else {
                                swal("Error!", "An unexpected error occurred.", "error");
                            }
                            $('#uploadForm button').prop('disabled', false).text('Upload');
                        }
                    });
                });

                // Fetch users for dropdown
                function fetchUsers() {
                    $.ajax({
                        url: '/admin/flipbook',
                        method: 'GET',
                        success: function(response) {
                            const $dropdown = $('#user_id, #user'); // Target both dropdowns
                            $dropdown.empty(); // Clear existing options
                            $dropdown.append(new Option('Select User', '', true, true));

                            const users = response.users;

                            console.log(users);

                            users.forEach(function(user) {
                                $dropdown.append(new Option(user.name, user.id));
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching users:', error);
                        }
                    });
                }

                // When edit button is clicked
                $(document).on('click', '.edit-button', function() {
                    const userId = $(this).data('user-id'); // Add this data attribute to your edit button
                    const imageId = $(this).data('id');
                    const imageName = $(this).data('name');
                    const driveId = $(this).data('drive-id');

                    // Set form values
                    $('#editImageId').val(imageId);
                    $('#editImageName').val(imageName);
                    $('#editDriveId').val(driveId);

                    // Wait for users to be loaded then set selected user
                    $('#user').val(userId);

                    // Show modal
                    $('#editImageModal').modal('show');
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




                $('#editImageForm').submit(function(e) {
                    fetchUsers();
                    e.preventDefault();

                    let formData = new FormData(this);
                    formData.append('_method', 'PUT');
                    const imageId = $('#editImageId').val();
                    const url = `/admin/flipbook/${imageId}`;

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                            // Clear previous errors
                            $('.is-invalid').removeClass('is-invalid');
                            $('.invalid-feedback').remove();
                            $('.editButton').prop('disabled', true).text('Uploading...');
                        },
                        success: function(response) {
                            swal("Success!", "Files uploaded successfully!", "success").then(() => {
                                $('#editImageModal').modal('hide');
                                $('#editImageForm')[0].reset();
                                loadImages();
                                $('.editButton').prop('disabled', false).text(
                                    'Save Changes');
                            });
                        },
                        error: function(xhr, status, error) {
                            if (xhr.status === 422) {

                                const errors = xhr.responseJSON.errors;
                                console.log(errors);


                                $.each(errors, function(field, messages) {

                                    const $input = $(`[name="${field}"]`);
                                    $input.addClass('is-invalid');
                                    $input.after(
                                        `<div class="invalid-feedback">${messages[0]}</div>`
                                    );
                                });
                                $('.editButton').prop('disabled', false).text('Save Changes');
                            } else {
                                swal("Error!", "An unexpected error occurred.", "error");
                                $('.editButton').prop('disabled', false).text('Save Changes');
                            }
                        }
                    });
                });








                // $('#deleteSelected').click(function() {
                //     const selectedImages = $('.image-checkbox:checked').map(function() {
                //         return {
                //             id: $(this).data('id'),
                //             drive_id: $(this).data('drive-id')
                //         };
                //     }).get();

                //     if (selectedImages.length === 0) {
                //         swal("Warning!", "Please select at least one image to delete.", "error");
                //         return;
                //     }

                //     const formData = new FormData();
                //     formData.append('_method', 'DELETE');
                //     formData.append('images', JSON.stringify(selectedImages));

                //     $.ajax({
                //         url: '/admin/flipbook',
                //         type: 'POST',
                //         data: formData,
                //         processData: false,
                //         contentType: false,
                //         headers: {
                //             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                //         },
                //         beforeSend: function() {
                //             swal({
                //                 title: 'Processing...',
                //                 text: 'Please wait while we delete the selected images.',
                //                 icon: 'info',
                //                 buttons: false,
                //                 closeOnClickOutside: false
                //             }).then(() => {
                //                 loadImages();
                //             });
                //         },
                //         success: function(response) {
                //             swal.close();
                //             if (response.success) {
                //                 swal('Success!',
                //                     `Successfully deleted ${selectedImages.length} images.`,
                //                     'success');
                //                 loadImages(); // Reload the images
                //             } else {
                //                 swal("Error!", response.message || "Error deleting the images.",
                //                     "error");
                //             }
                //         },
                //         error: function(xhr, status, error) {
                //             swal.close();
                //             let errorMessage = xhr.responseJSON?.message || xhr.responseText ||
                //                 'An unexpected error occurred.';
                //             swal("Error!", errorMessage, "error");
                //             console.error('Detailed error:', {
                //                 xhr,
                //                 status,
                //                 error
                //             });
                //         }
                //     });
                // });




                // // Handle select all functionality
                // $(document).on('change', '#selectAll', function() {
                //     $('.image-checkbox').prop('checked', $(this).prop('checked'));
                //     updateDeleteButton();
                // });

                // // Handle individual checkbox changes
                // $(document).on('change', '.image-checkbox', function() {
                //     updateDeleteButton();
                // });

                // Update delete button state
                function updateDeleteButton() {
                    const selectedCount = $('.image-checkbox:checked').length;
                    $('#deleteSelected').text(
                        selectedCount > 0 ? `Delete Selected (${selectedCount})` : 'Delete Selected'
                    ).prop('disabled', selectedCount === 0);
                }


                // Initialize the page
                loadImages();
                fetchUsers();
                loadUsers();

            });
        </script>
    @endpush


</x-app-layout>
