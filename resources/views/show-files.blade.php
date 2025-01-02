<x-app-layout>

    <div class="page-inner">

        <form id="image-selection-form">
            <table>

                <thead>
                    <tr>
                        <th>Select</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Image</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($files as $file)
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_images[]" value="{{ $file->id }}"
                                    class="image-checkbox">
                            </td>
                            <td>{{ $file->id }}</td>
                            <td>{{ $file->file_name }}</td>
                            <td>
                                <img crossorigin="anonymous"
                                    src="https://lh3.googleusercontent.com/d/{{ $file->file_id }}"
                                    alt="{{ $file->file_name }}" style="max-width: 100px; height: auto;">
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

            <button type="button" id="preview-button" class="btn btn-secondary">Preview Selected Images</button>
            <button type="submit" id="download-button" class="btn btn-primary" disabled>Download Selected
                Images</button>

        </form>

        <div id="image-preview-modal" style="display: none;">
            <div id="image-preview-content"></div>
            <button type="button" id="close-preview">Close Preview</button>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Preview Button Click
                $('#preview-button').click(function() {
                    const selectedImages = $('.image-checkbox:checked').map(function() {
                        return this.value;
                    }).get(); // Array of selected image IDs

                    if (selectedImages.length > 0 && selectedImages.length <= 5) {
                        console.log(selectedImages);

                        $.ajax({
                            url: '{{ url('admin/fetch-images') }}', 
                            method: 'GET',
                            data: {
                                ids: selectedImages.join(',')
                            },
                            success: function(data) {
                                const previewContent = $('#image-preview-content');
                                previewContent.empty(); // Clear previous content

                                // Append images for preview
                                data.forEach(function(image) {
                                    const imgElement = $('<img>').attr('src', image.url)
                                        .attr('alt', image.name)
                                        .css({
                                            'max-width': '200px',
                                            'margin': '10px'
                                        });
                                    previewContent.append(imgElement);
                                });

                                $('#image-preview-modal').show();
                                $('#download-button').prop('disabled',
                                false); // Enable download button
                            },
                            error: function(error) {
                                console.error('Error fetching images:', error);
                                alert('An error occurred while fetching images.');
                            }
                        });
                    } else {
                        alert('Please select up to 5 images to preview.');
                    }
                });

                // Form Submit (Download Images)
                $('#image-selection-form').submit(function(e) {
                    e.preventDefault();

                    const selectedImages = $('.image-checkbox:checked').map(function() {
                        return this.value;
                    }).get(); // Array of selected image IDs

                    if (selectedImages.length > 0 && selectedImages.length <= 5) {
                        const downloadUrl = `admin/download-images?ids=${selectedImages.join(',')}`;
                        window.location.href = downloadUrl; // Redirect for downloading
                    } else {
                        alert('Please select up to 5 images to download.');
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
