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
                            <option value="">All Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <button id="clearFilter" class="btn btn-sm btn-outline-secondary mt-4" style="display: none;">
                            Clear Filter
                        </button>
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
            function loadImages() {



                $('#loadingButton').show();

                let url = `{{ url('/admin/flipbook') }}`;

                $.get(url, function(response) {

                    $('#tableContainer').empty();

                    const files = response.files;

                    $('#loadingButton').hide();

                    // Select All Container
                    const selectAllContainer = $('<div>').addClass('mb-3');
                    const selectAllWrapper = $('<div>').addClass('d-flex align-items-center');
                    const selectAllCheckbox = $('<input>')
                        .attr('type', 'checkbox')
                        .attr('id', 'selectAll')
                        .addClass('me-2')
                        .css({
                            'width': '20px',
                            'height': '20px'
                        });
                    const selectAllLabel = $('<label>')
                        .attr('for', 'selectAll')
                        .addClass('form-check-label fs-5')
                        .text('Select All');

                    selectAllWrapper.append(selectAllCheckbox, selectAllLabel);
                    selectAllContainer.append(selectAllWrapper);
                    $('#tableContainer').append(selectAllContainer);

                    // Create image cards
                    let cardContainer = $('<div>').addClass('row');

                    files.data.forEach(function(file) {
                        let cardWrapper = $('<div>').addClass('col-4');
                        let card = $('<div>').addClass('h-100 position-relative');

                        // Preview Image
                        let preview = $('<img>')
                            .attr('src', `https://lh3.google.com/u/0/d/${file.drive_id}`)
                            .addClass('card-img-top')
                            .css({
                                'object-fit': 'cover',
                                'height': '300px',
                                'border-radius': '0.5rem',
                                'box-shadow': '0px 0px 2px black'
                            });

                        // Image Checkbox
                        let checkbox = $('<input>')
                            .attr('type', 'checkbox')
                            .addClass('image-checkbox form-check-input position-absolute top-0 start-0 m-2')
                            .attr('data-id', file.id)
                            .attr('data-drive-id', file.drive_id)
                            .css({
                                'z-index': '2',
                                'width': '20px',
                                'height': '20px'
                            });

                        // Append image and checkbox to card
                        card.append(checkbox, preview);
                        cardWrapper.append(card);
                        cardContainer.append(cardWrapper);
                    });

                    $('#tableContainer').append(cardContainer);

                    // Pagination
                    if (response.links) {
                        let pagination = $('<div>').addClass('pagination-container mt-3');
                        response.links.forEach(function(link) {
                            let pageLink = $('<button>')
                                .html(link.label)
                                .addClass(link.active ? 'btn btn-primary mx-1' : 'btn btn-outline-primary mx-1')
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

                    // Handle select all functionality
                    $('#selectAll').change(function() {
                        const isChecked = $(this).prop('checked');
                        $('.image-checkbox').prop('checked', isChecked);
                        updateSelectionInfo();
                    });

                    // Update selection info on each checkbox change
                    $('.image-checkbox').change(function() {
                        updateSelectionInfo();
                    });

                    updateSelectionInfo();
                }).fail(function() {
                    $('#loadingButton').hide();
                    swal("Error!", "Failed to load images.", "error");
                });
            }

            // Function to update selection info
            function updateSelectionInfo() {
                const selectedCount = $('.image-checkbox:checked').length;
                $('#selectedCount').text(selectedCount);
                if (selectedCount === $('.image-checkbox').length) {
                    $('#selectAll').prop('checked', true);
                } else {
                    $('#selectAll').prop('checked', false);
                }
            }

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
                            alert(response.message || 'Files uploaded successfully!');

                            // Optional: Reset the form
                            $('#uploadForm')[0].reset();

                            // Hide the modal
                            $('#uploadModal').modal('hide');
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

                                alert(`Validation Errors:${errorMessages}`);
                            } else {
                                alert(xhr.responseJSON.message ||
                                    'An error occurred while uploading files.');
                            }
                        },
                        complete: function() {
                            // Re-enable the submit button
                            $('#uploadForm button[type="submit"]').prop('disabled', false).text(
                                'Upload');
                        },
                    });
                });

            });
        </script>
    @endpush


</x-app-layout>
