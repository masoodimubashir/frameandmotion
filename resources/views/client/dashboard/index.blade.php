<x-app-layout>

    <style>
        .timeline-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .timeline-header h1 {
            color: #333;
            margin-bottom: 5px;
        }

        .timeline-header p {
            color: #666;
            margin: 0;
        }

        .timeline {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
        }

        .timeline::after {
            content: '';
            position: absolute;
            width: 2px;
            background-color: #e0e6ff;
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -1px;
        }

        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
        }

        .left {
            left: 0;
            text-align: right;
        }

        .right {
            left: 50%;
            text-align: left;
        }

        .content {
            padding: 20px;
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            right: -8px;
            background-color: #4a90e2;
            border: 4px solid #c8deff;
            top: 15px;
            border-radius: 50%;
            z-index: 1;
        }

        .right::after {
            left: -8px;
        }

        .content h2 {
            margin-top: 0;
            color: #333;
            font-size: 18px;
        }

        .content p {
            margin: 8px 0;
            color: #666;
            font-size: 14px;
        }

        .date {
            position: absolute;
            top: 18px;
            color: #666;
            font-size: 14px;
        }

        .left .date {
            right: -150px;
        }

        .right .date {
            left: -150px;
        }

        .image-gallery {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .image-gallery img {
            width: 80px;
            height: 60px;
            border-radius: 4px;
            object-fit: cover;
        }

        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            margin-top: 15px;
        }

        .timeline {
            position: relative;
        }

        .spinner-container {
            position: absolute;
            bottom: -30px;
            /* Adjust this to control the distance below the timeline */
            left: 50%;
            transform: translateX(-50%);
        }
    </style>

    <div class="page-inner">

        <div class="page-header">

            <h3 class="fw-bold mb-3">Project Milestone</h3>

            <ul class="breadcrumbs mb-3">

                <li class="nav-home">
                    <a href="{{ url('admin/dashboard') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>

                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>

                <li class="nav-item {{ Request::is('client/milestone') ? 'active' : '' }}">
                    <a href="{{ url('client/milestone') }}">Timeline</a>
                </li>

            </ul>

        </div>

        <div class="row">

            <div class="col-12">

                <div class="timeline-header">
                    <h1>Our Progress Milestone</h1>
                </div>

                <div class="timeline">
                    <div id="timelineLoader" style="display: none;" class="text-center my-4">
                        <p class="mt-2">Loading timeline...</p>
                        <div class="spinner-container">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>



            </div>

        </div>

    </div>

    @push('scripts')
        <script>
            function loadMilestoneTimeline() {
                // Show loader before making the request
                $('#timelineLoader').show();

                $.ajax({
                    url: '/client/get-milestone',
                    method: 'GET',
                    success: function(response) {
                        // Clear existing content
                        $('.timeline').empty();

                        response.forEach(function(milestone) {
                            // Format date
                            const date = new Date(milestone.created_at).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: '2-digit'
                            });

                            // Determine if item should be on left or right
                            const side = response.indexOf(milestone) % 2 === 0 ? 'left' : 'right';

                            // Create timeline item HTML
                            const timelineItem = `
                    <div class="timeline-item ${side}">
                        <div class="content">
                            <h2>${milestone.name}</h2>
                            <p>${milestone.description}</p>
                            <div class="status">
                                ${milestone.is_in_progress ? '<span class="badge bg-info">In Progress</span>' : ''}
                                ${milestone.is_completed ? `
                                                            <span class="badge bg-success">Completed</span>
                                                            <a href="{{ url('/client/view-flipbook') }}" class="btn btn-link">View Your Flipbook</a>
                                                        ` : ''}
                            </div>
                            ${milestone.start_date ? `<p class="small">Started: ${new Date(milestone.start_date).toLocaleDateString()}</p>` : ''}
                            ${milestone.completion_date ? `<p class="small">Completed: ${new Date(milestone.completion_date).toLocaleDateString()}</p>` : ''}
                        </div>
                        <span class="date">${date}</span>
                    </div>
                `;

                            $('.timeline').append(timelineItem);
                        });
                    },
                    error: function(xhr) {
                        console.error('Error loading milestones:', xhr);
                        $('.timeline').html('<div class="alert alert-danger">Error loading milestones</div>');
                    },
                    complete: function() {
                        // Hide loader after request is complete (whether success or error)
                        $('#timelineLoader').hide();
                    }
                });
            }

            // Call the function when document is ready
            $(document).ready(function() {
                loadMilestoneTimeline();
            });
        </script>
    @endpush

</x-app-layout>
