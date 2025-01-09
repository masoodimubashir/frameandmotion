Public Routes
These routes are publicly accessible to users who are not logged in.

GET / - Displays the welcome page and login form.

Controller: AuthController@welcome
Route Name: login
POST /login - Handles user login.

Controller: AuthController@login
GET /logout - Logs the user out.

Controller: AuthController@logout
Admin Routes
These routes are prefixed with /admin and are only accessible to authenticated admin users. They also require a valid Google token.

Dashboard & Admin Management
GET /admin/dashboard - Displays the admin dashboard.

Controller: DashboardController@dashboard
GET /admin/edit-admin - Displays the form to edit admin details.

Controller: DashboardController@show
PUT /admin/edit-admin-name/{id} - Updates the admin's name and username.

Controller: DashboardController@editNameUsername
PUT /admin/edit-admin-password/{id} - Updates the admin's password.

Controller: DashboardController@editPassword
User and Client Management
Resource Routes for /admin/clients - CRUD operations for managing clients.

Controller: ClientsController
Resource Routes for /admin/bookings - CRUD operations for managing bookings.

Controller: BookingsController
Resource Routes for /admin/users - CRUD operations for managing users.

Controller: UserController
Flipbook Management
GET /admin/view-flipbook - Displays all clients and their flipbooks.

Controller: FlipBookController@show
Resource Routes for /admin/flipbook - CRUD operations for managing flipbooks.

Controller: FLipBookController
DELETE /admin/flipbook - Deletes a flipbook.

Controller: FLipBookController@destroy
File Management
Resource Routes for /admin/files - CRUD operations for managing files.

Controller: FileController
GET /admin/download-drive-images - Downloads images from Google Drive.

Controller: FileDownloadController@downloadImage
Booking Management
PUT /admin/confirm-bookings - Confirms bookings made by clients.
Controller: AcceptBookingController@confirmBooking
Route Name: confirm-bookings
Google Routes
These routes handle Google OAuth and calendar operations.

GET /create-event - Allows the user to create a new event on Google Calendar.

Controller: GoogleController@createEvent
GET /auth/google/redirect - Redirects to Google for OAuth authentication.

Controller: GoogleController@redirectToGoogle
Route Name: google.redirect
GET /auth/google/callback - Handles the callback after Google OAuth authentication.

Controller: GoogleController@handleGoogleCallback
Route Name: google.callback
Client Routes
These routes are prefixed with /client and are only accessible to authenticated client users. They also require a valid Google token.

GET /client/view-flipbook - Displays the client's flipbook page.

Controller: UserFlipBoardController@show
GET /client/dashboard - Displays the client dashboard.

Controller: ClientDashboardController@dashboard
GET /client/get-milestone - Fetches the client's milestone data.

Controller: ClientDashboardController@getMilestone
Front-End Routes
These routes are for the front-end user booking interface.

GET /front-bookings - Displays the booking form.

View: front-end-bookings
POST /book - Sends booking email after form submission.

Controller: BookingMailController@sendBookMail
POST /FormDetail - Sends form details after submission.

Controller: BookingMailController@getFormDetails