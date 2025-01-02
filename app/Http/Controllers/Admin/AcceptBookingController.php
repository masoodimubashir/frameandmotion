<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingMail;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AcceptBookingController extends Controller
{


    public function confirmBooking(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'id' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        // Find the client by ID
        $client = Client::find($request->id);

        if (!$client) {
            return response()->json(['success' => false, 'message' => 'Client not found'], 404);
        }

        if ($client->is_active == $request->is_active) {
            return response()->json(['success' => true, 'message' => 'No changes made to client status'], 200);
        }

        $client->is_active = $request->is_active;

        if (!$client->save()) {
            return response()->json(['success' => false, 'message' => 'Failed to update client status'], 500);
        }

        if ($client->is_active) {

            // Create the booking for the client
            $booking = $client->booking()->create([
                'booking_date' => $client->date,
                'ceremony_date' => $client->date,
            ]);

            if ($booking) {
                // Generate the username and password
                $username = strtolower(str_replace(' ', '.', $client->name)) . '.' . $booking->id;

                $password = Str::random(8);

                // Create the user
                $user = User::create([
                    'name' => $client->name,
                    'booking_id' => $booking->id,
                    'role_name' => 'client',
                    'username' => $username,
                    'password' => bcrypt($password),
                    'created_by' => auth()->id() ?? null,
                ]);

                // Prepare the data for the email
                $data = [
                    'name' => $client->name,
                    'email' => $client->email,
                    'number' => $client->phone_number,
                    'venue' => $client->venue,
                    'date' => $client->date,
                    'message' => 'Booking created successfully',
                    'username' => $username,
                    'password' => $password,
                ];

                // Send the email to the client
                Mail::to($client->email)->send(new BookingMail($data));

                return response()->json([
                    'success' => true,
                    'message' => 'Booking and user created successfully!',
                    'booking' => $booking,
                    'user' => $user,
                ]);
                
            } else {

                return response()->json(['success' => false, 'message' => 'Failed to create the booking'], 500);
            
            }
        }

        // If the client is not active, return a failure response
        return response()->json(['success' => false, 'message' => 'Client is not active'], 400);
    }
}
