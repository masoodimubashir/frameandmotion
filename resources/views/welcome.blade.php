<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ env('APP_NAME') }}</title>

    {{-- Bootstrap Css --}}
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />


    <style>
        /* Add these styles to center the form */
        html,
        body {
            height: 100%;
            margin: 0;
        }

        .form-bg {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        .container {
            width: 100%;
        }

        /* Optional: Responsive adjustments */
        @media (max-width: 768px) {
            .form-container {
                width: 90%;
                max-width: 350px;
            }
        }

        .form-container {
            font-family: 'Poppins', sans-serif;
            position: relative;
            z-index: 1;
            margin: 0 auto;
            max-width: 500px;

        }

        .form-container .form-horizontal {
            background-color: #fff;
            padding: 30px 30px 10px;
            border: 1px solid #e7e7e7;

        }

        .form-container .form-horizontal:before,
        .form-container .form-horizontal:after {
            content: '';
            background-color: #fff;
            height: 100%;
            width: 100%;
            border: 1px solid #e7e7e7;
            transform: rotate(3deg);
            position: absolute;
            left: 0;
            top: 0;
            z-index: -1;
        }

        .form-container .form-horizontal:after {
            transform: rotate(-3deg);
        }

        .form-container .title {
            color: #777;
            background: linear-gradient(to right, #f5f5f5, transparent, transparent, transparent, #f5f5f5);
            font-size: 23px;
            font-weight: 600;
            text-align: center;
            text-transform: capitalize;
            padding: 2px;
            margin: 0 0 20px 0;
        }

        .form-horizontal .form-group {
            background-color: #eaeaea;
            font-size: 0;
            margin: 0 0 15px;
            border: 1px solid #d1d1d1;
            border-radius: 3px;
        }

        .form-horizontal .input-icon {
            color: #888;
            font-size: 20px;
            text-align: center;
            line-height: 40px;
            height: 40px;
            width: 40px;
            vertical-align: top;
            display: inline-block;
        }

        .form-horizontal .form-control {
            color: #555;
            background-color: transparent;
            font-size: 14px;
            letter-spacing: 1px;
            width: calc(100% - 55px);
            height: 50px;
            padding: 2px 10px 2px 0;
            box-shadow: none;
            border: none;
            border-radius: 0;
            display: inline-block;
            transition: all 0.3s;
        }

        .form-horizontal .form-control:focus {
            box-shadow: none;
            border: none;
        }

        .form-horizontal .form-control::placeholder {
            color: rgba(0, 0, 0, 0.7);
            font-size: 14px;
            text-transform: capitalize;
        }

        .form-horizontal .btn {
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            text-transform: capitalize;
            width: 100px;
            padding: 5px 18px;
            margin: 0 0 15px 0;
            border: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .form-horizontal .btn:hover,
        .form-horizontal .btn:focus {
            color: #fff;
            letter-spacing: 2px;
        }

        .form-horizontal .forgot-pass {
            font-size: 12px;
            text-align: right;
            width: calc(100% - 105px);
            display: inline-block;
            vertical-align: top;
        }

        .form-horizontal .forgot-pass a,
        .form-horizontal .register a {
            color: #999;
            transition: all 0.3s ease;
        }

        .form-horizontal .forgot-pass a:hover,
        .form-horizontal .register a:hover {
            color: #555;
            text-decoration: underline;
        }

        .form-horizontal .register {
            font-size: 12px;
            text-align: center;
            padding-top: 8px;
            border-top: 1px solid #e7e7e7;
            display: block;
        }
    </style>
</head>

<body>

    <div class="form-bg">

        <div class="container">

    

            <div class="form-container">

                <form class="form-horizontal" action="{{ url('/login') }}" method="POST">

                    @csrf

                    <h3 class="title">Sign in To Start Your Session?</h3>

                    @error('username')
                        <span class="text-danger">
                            {{ $message }}
                        </span>
                    @enderror
                    <div class="form-group">
                        <span class="input-icon"><i class="fa fa-user"></i></span>
                        <input class="form-control" type="text" placeholder="Username" name="username"
                            value="{{ old('username') }}">
                    </div>

                    @error('password')
                        <span class="text-danger">
                            {{ $message }}
                        </span>
                    @enderror
                    <div class="form-group">
                        <span class="input-icon"><i class="fa fa-lock"></i></span>
                        <input class="form-control" type="password" placeholder="Password" value="{{ old('password') }}"
                            name="password">
                    </div>


                    <br>

                    <button type="submit" class="btn btn-dark signin">Log in</button>

                </form>

            </div>

        </div>

    </div>


</body>

</html>
