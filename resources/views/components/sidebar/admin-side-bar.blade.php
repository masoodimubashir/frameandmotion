<div class="sidebar" data-background-color="dark">

    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="{{ url('admin/dashboard') }}" class="logo">
                <img src="{{ asset('img/Logo/frameandmotion_logo.webp') }}" alt="navbar brand" class="navbar-brand"
                    height="40" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>

    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">

                <li class="nav-item  {{ Request::is('admin/dashboard') ? 'active' : '' }}">
                    <a href="{{ url('admin/dashboard') }}" class="collapsed" aria-expanded="false">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Links</h4>
                </li>

                <li class="nav-item {{ Request::is('admin/clients') ? 'active' : '' }}">
                    <a href="{{ route('clients.index') }}">
                        <i class="fas fa-layer-group"></i>
                        <p>Clients</p>
                    </a>
                </li>

                <li class="nav-item {{ Request::is('admin/bookings') ? 'active' : '' }}">

                    <a href="{{ route('bookings.index') }}">
                        <i class="fas fa-th-list"></i>
                        <p>Bookings</p>
                    </a>
                </li>

                <li class="nav-item {{ Request::is('admin/users') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}">
                        <i class="fas fa-users"></i>
                        <p>Users</p>
                    </a>

                </li>

                <li class="nav-item {{ Request::is('admin/view-flipbook') ? 'active' : '' }}">
                    <a href="{{ url('admin/view-flipbook') }}">
                        <i class="fas fa-users"></i>
                        <p>Flipbook</p>
                    </a>

                </li>

                <li class="nav-item {{ Request::is('admin/view-milestone') ? 'active' : '' }}">
                    <a href="{{ url('admin/view-milestone') }}">
                        <i class="fas fa-users"></i>
                        <p>Milestone</p>
                    </a>
                </li>
               

            </ul>
        </div>
    </div>
</div>
