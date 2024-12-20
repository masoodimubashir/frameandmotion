<x-app-layout>
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Edit Profile</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ url('admin/dashboard') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="{{ url('admin/edit-admin') }}">Edit Profile</a>
                </li>

            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form class="card" action="{{ url('admin/edit-admin-name', [$user->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">

                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" id="name" placeholder="name"
                                        name="name" value="{{ $user->name }}" />
                                    @error('name')
                                        <span class="text-danger fw-bold">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        placeholder="Enter Username" value="{{ $user->username }}" />
                                    @error('username')
                                        <span class="text-danger fw-bold">
                                            {{ $message }}
                                        </span>
                                    @enderror

                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-action">
                        <button class="btn btn-success">Submit</button>
                    </div>
                </form>

                <form class="card" action="{{ url('admin/edit-admin-password', [$user->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password"
                                        placeholder="Enter password" name="password" />
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        placeholder="Confirm Password" name="password_confirmation" />
                                    @error('password')
                                        <span class="text-danger fw-bold">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-action">
                        <button class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        @if (session('success'))
            <script>
                swal({
                    title: "Success!",
                    text: "{{ session('success') }}",
                    icon: "success",
                    buttons: {
                        confirm: {
                            text: "OK",
                            className: "btn btn-success"
                        }
                    }
                });
            </script>
        @endif
    @endpush


</x-app-layout>
