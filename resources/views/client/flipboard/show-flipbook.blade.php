<x-app-layout>

    <style>
        /* General page container styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
        }

        /* Action buttons styling */
        .d-flex {
            margin-top: 20px;
        }

        /* Upload and View Flipbook buttons */
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            font-weight: 600;
            padding: 8px 20px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            font-weight: 600;
            padding: 8px 20px;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* Container for the card and selection section */
        #tableContainer {
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Select All checkbox styling */
        .mb-3 {
            margin-bottom: 20px;
        }

        .d-flex.align-items-center {
            display: flex;
            align-items: center;
        }

        .form-check-label {
            font-size: 16px;
            font-weight: 600;
        }

        /* Card Container Grid */
        .row.g-4 {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        /* Card styles */
        .card-img-top {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            object-fit: cover;
            height: 220px;
            width: 100%;
        }

        .image-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 2;
            width: 20px;
            height: 20px;
        }

        /* Loading overlay styling */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 20px;
            color: #007bff;
        }

        /* Pagination styling */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination-container button {
            font-size: 16px;
            padding: 8px 15px;
            margin: 0 5px;
            border-radius: 5px;
        }

        .pagination-container button.btn-primary {
            background-color: #007bff;
            color: white;
        }

        .pagination-container button.btn-outline-primary {
            background-color: transparent;
            border: 1px solid #007bff;
            color: #007bff;
        }

        .pagination-container button:disabled {
            opacity: 0.6;
        }
    </style>

    <div class="page-inner">

        <div class="page-header">
            <h3 class="fw-bold mb-3">Flipbook</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ url('client/dashboard') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item {{ Request::is('client/flipbook') ? 'active' : '' }}">
                    <a href="{{ url('client/flipbook') }}">Flipbook</a>
                </li>
            </ul>
        </div>

        <div class="row">
            <div class="col-12">
                <!-- Action Buttons Container -->
                <div class="d-flex align-items-center justify-content-end mb-4">
                    <button class="btn btn-primary me-2" id="uploadModalTrigger" data-toggle="modal"
                        data-target="#uploadModal">
                        <i class="fas fa-file-pdf"></i> Upload
                    </button>
                    <button id="viewSelectedImages" class="btn btn-secondary me-2" disabled>
                        <i class="fas fa-eye"></i> View Flipbook
                    </button>
                </div>

                <!-- Table Container (Image Cards & Select All) -->
                <div id="tableContainer" style="min-height: 900px;">

                </div>

                <!-- Loading Button (Overlay) -->
                <div id="loadingButton" class="loading-overlay" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>


    </div>

    <!-- Add this before closing body tag -->
    <div class="modal fade" id="carouselModal" tabindex="-1" aria-labelledby="carouselModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0 bg-dark">
                    <h5 class="modal-title text-light" id="carouselModalLabel">Digital Flipbook</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center p-0">
                    <div id="selectedImagesCarousel" class="carousel slide" data-bs-interval="false">
                        <div class="carousel-inner">
                            <!-- Images will be inserted here -->
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#selectedImagesCarousel"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#selectedImagesCarousel"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            $(document).ready(function() {

                function addDateFilters() {

                    const filterContainer = $('<div>').addClass('d-flex flex-wrap align-items-center mb-4');

                    // Start Date Input
                    const startDateInput = $('<input>')
                        .attr('type', 'date')
                        .attr('id', 'startDate')
                        .addClass('form-control')
                        .css('width', 'auto');

                    // End Date Input
                    const endDateInput = $('<input>')
                        .attr('type', 'date')
                        .attr('id', 'endDate')
                        .addClass('form-control')
                        .css('width', 'auto');

                    // Filter Button
                    const filterButton = $('<button>')
                        .addClass('btn btn-primary')
                        .text('Filter Images')
                        .click(function() {
                            loadImages(1); // Reset to first page with filters
                        });

                    // Clear Filter Button
                    const clearButton = $('<button>')
                        .addClass('btn btn-outline-secondary')
                        .text('Clear Filter')
                        .click(function() {
                            $('#startDate').val('');
                            $('#endDate').val('');
                            loadImages(1); // Reset to first page without filters
                        });

                    filterContainer.append(
                        $('<label>').text('Start Date:').addClass('me-2'),
                        startDateInput,
                        $('<label>').text('End Date:').addClass('mx-2'),
                        endDateInput,
                        filterButton.addClass('ms-3'),
                        clearButton.addClass('ms-2')
                    );

                    // Add the filter container before the table container
                    $('#tableContainer').before(filterContainer);
                }

                function loadImages(page = 1) {
                    $('#loadingButton').show();

                    const startDate = $('#startDate').val();
                    const endDate = $('#endDate').val();

                    let url = `{{ url('client/flipbook') }}?page=${page}`;

                    if (startDate && endDate) {
                        url += `&start_date=${startDate}&end_date=${endDate}`;
                    }

                    $.get(url, function(response) {
                        console.log(response);

                        $('#tableContainer').empty();

                        const files = response;

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

                        let cardContainer = $('<div>').addClass('row');

                        files.data.forEach(function(file) {
                            let cardWrapper = $('<div>').addClass('col-4');
                            let card = $('<div>').addClass('h-100 position-relative');

                            console.log(file);

                            // In the loadImages function, update the preview image URL:
                            let preview = $('<img>')
                                .attr('src', `${file.thumbnail}`)
                                .addClass('card-img-top')
                                .css({
                                    'object-fit': 'cover',
                                    'height': '300px',
                                    'border-radius': '0.5rem',
                                    'box-shadow': '0px 0px 2px black'
                                });

                            // In the viewSelectedImages click handler, update the carousel image URL:
                            const imageUrl = `${file.thumbnail}`;

                            let checkbox = $('<input>')
                                .attr('type', 'checkbox')
                                .addClass(
                                    'image-checkbox form-check-input position-absolute top-0 start-0 m-2'
                                )
                                .attr('data-id', file.id)
                                .attr('data-drive-id', file.drive_id)
                                .css({
                                    'z-index': '2',
                                    'width': '20px',
                                    'height': '20px'
                                });

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

                        updateSelectionInfo();
                    }).fail(function() {
                        $('#loadingButton').hide();
                        swal("Error!", "Failed to load images.", "error");
                    });
                }


                function updateViewButton() {
                    const selectedCount = $('.image-checkbox:checked').length;
                    $('#viewSelectedImages').prop('disabled', selectedCount === 0);
                }

                $('#viewSelectedImages').click(function() {

                    const selectedCheckboxes = $('.image-checkbox:checked');
                    const carouselInner = $('#selectedImagesCarousel .carousel-inner');
                    carouselInner.empty();

                    selectedCheckboxes.each(function(index) {
                        const driveId = $(this).data('drive-id');
                        const imageUrl = `https://lh3.google.com/u/0/d/${driveId}`;

                        const carouselItem = $('<div>')
                            .addClass('carousel-item')
                            .addClass(index === 0 ? 'active' : '');

                        const image = $('<img>')
                            .attr('src', imageUrl)
                            .addClass('d-block w-100')
                            .css({
                                'height': '90vh',
                                'object-fit': 'contain'
                            });

                        carouselItem.append(image);
                        carouselInner.append(carouselItem);
                    });

                    // Show the modal
                    const carouselModal = new bootstrap.Modal(document.getElementById('carouselModal'));
                    carouselModal.show();
                });

                $(document).on('change', '#selectAll', function() {
                    $('.image-checkbox').prop('checked', $(this).prop('checked'));
                    updateSelectionInfo();
                });

                $(document).on('change', '.image-checkbox', function() {
                    updateSelectionInfo();
                });

                // Initialize on page load
                $(document).ready(function() {
                    updateSelectionInfo();
                });

                $('#uploadModalTrigger').click(function(e) {
                    e.preventDefault();

                    // Get all checked checkboxes
                    const selectedCheckboxes = $('.image-checkbox:checked');

                    if (selectedCheckboxes.length === 0) {
                        swal("Warning!", "Please select at least one image.", "warning");
                        return;
                    }

                    // Create array of selected drive IDs
                    const selectedFiles = [];
                    selectedCheckboxes.each(function() {
                        selectedFiles.push({
                            id: $(this).data('id'),
                            drive_id: $(this).data('drive-id')
                        });
                    });



                    $.ajax({
                        url: '/client/flipbook',
                        method: 'POST',
                        data: JSON.stringify({
                            files: selectedFiles,
                        }),
                        contentType: 'application/json',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-CSRF-TOKEN',
                                '{{ csrf_token() }}'); // CSRF Token
                            $('#uploadModalTrigger').prop('disabled', true).text(
                                'Generating PDF...');
                        },
                        success: function(response) {

                            if (response.success) {
                                swal("Success!", "Your PDF has been generated successfully!",
                                        "success")
                                    .then(() => {

                                        window.open(response.download_url, '_blank');

                                        $('#uploadModalTrigger').prop('disabled', false).html(
                                            '<i class="fas fa-regular fa-file-pdf"></i>');

                                    });
                            } else {
                                let errorMessage = response.message || "Failed to download files.";
                                if (response.errors && response.errors.length > 0) {
                                    errorMessage += "\n\n" + response.errors.join("\n");
                                }
                                swal("Error!", errorMessage, "error");
                            }
                        },
                        error: function(xhr) {
                            $('#uploadModalTrigger').prop('disabled', false).text(
                                'Upload'); // Re-enable button on error
                            let errorMessage = "Failed to process request.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            swal("Error!", errorMessage, "error");
                        }
                    });

                });

                $(document).on('change', '#selectAll', function() {
                    $('.image-checkbox').prop('checked', $(this).prop('checked'));
                    updateDeleteButton();
                });

                $(document).on('change', '.image-checkbox', function() {
                    updateDeleteButton();
                });

                function updateDeleteButton() {
                    const selectedCount = $('.image-checkbox:checked').length;
                    $('#deleteSelected').text(
                        selectedCount > 0 ? `Delete Selected (${selectedCount})` : 'Delete Selected'
                    ).prop('disabled', selectedCount === 0);
                }

                function updateSelectionInfo() {
                    updateDeleteButton();
                    updateViewButton();
                }

                addDateFilters();
                loadImages(1);
            });
        </script>
    @endpush

</x-app-layout>
