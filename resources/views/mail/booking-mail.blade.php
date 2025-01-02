<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>

    <h1>New Booking Details</h1>

    <p><strong>Name:</strong> {{ $data['name'] }}</p>
    <p><strong>Email:</strong> {{ $data['email'] }}</p>
    <p><strong>Phone Number:</strong> {{ $data['number'] }}</p>
    <p><strong>Venue:</strong> {{ $data['venue'] }}</p>
    <p><strong>Date:</strong> {{ $data['date'] }}</p>
    <p><strong>Message:</strong> {{ $data['message'] }}</p>
    <p><strong>Username:</strong> {{ $data['username'] }}</p>
    <p><strong>Password:</strong> {{ $data['password'] }}</p>


    <p>Thank you for your request!... {{ env('APP_NAME') }}</p>

</body>

</html>
