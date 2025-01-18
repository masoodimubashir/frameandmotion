<?php

namespace App\Http\Controllers\Admin;

use App\GoogleCalendarService as AppGoogleCalendarService;
use App\Http\Controllers\Controller;
use App\Mail\BookingMail;
use App\Models\Client;
use App\Models\User;
use App\Service\GoogleAuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class AcceptBookingController extends Controller
{

    protected $googleAuthService;
    protected $googleCalendarService;

    /**
     * Constructor for the class.
     *
     * @param \App\Services\GoogleAuthService $googleAuthService  The Google authentication service
     * @param \App\Services\AppGoogleCalendarService $googleCalendarService  The Google Calendar service
     */
    public function __construct(GoogleAuthService $googleAuthService, AppGoogleCalendarService $googleCalendarService)
    {
        $this->googleAuthService = $googleAuthService;
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Getting Client Id And Active Id field Form The Request.
     *
     * @param Illuminate\Http\Request;
     */
    public function confirmBooking(Request $request)
    {

        $validatedData = $request->validate([
            'id' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        $client = Client::findOrFail($validatedData['id']);


        try {

            $client->is_active = $validatedData['is_active'];
            $client->save();

            if ($client->is_active) {

                $booking = $this->createBookingForClient($client);

                $password = Str::random(8);

                $username = strtolower(str_replace(' ', '.', $client->name)) . '.' . $booking->id;

                $user = $this->createUserForBooking($client, $booking, $username, $password);

                $this->sendBookingEmail($client->email, $this->prepareBookingEmailData($client, $user, $password, $username));

                $this->createGoogleCalendarEvent($client);

                return response()->json([
                    'success' => true,
                    'message' => 'Booking confirmed and calendar event created',
                    'booking' => $booking,
                    'user' => $user,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Client status updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Booking confirmation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process booking'
            ], 500);
        }
    }


    /**
     * Accepting The Client Model 
     *
     * @param App\Models\Client;;
     */
    private function createGoogleCalendarEvent(Client $client): bool
    {
        $startDateTime = Carbon::parse($client->date)->addHours(4)->setTimezone('Asia/Kolkata');
        $endDateTime = $startDateTime->copy()->endOfDay();

        return $this->googleCalendarService->createEvent([
            'title' => 'Booking Confirmation',
            'description' => 'Your booking has been confirmed',
            'start_datetime' => $startDateTime->format('Y-m-d\TH:i:s'),
            'end_datetime' => $endDateTime->format('Y-m-d\TH:i:s'),
            'timezone' => 'Asia/Kolkata',
            'attendees' => ['masudimubashir@gmail.com', 'masudimubashir@gmail.com']
        ]);
    }

    /**
     * Accepting The Client Model 
     *
     * @param App\Models\Client;;
     */

    private function createBookingForClient(Client $client)
    {
        return $client->booking()->create([
            'booking_date' => $client->date,
            'ceremony_date' => $client->date,
        ]);
    }


    /**
     * Accepting The Client Model , Username, Password
     *
     * @param App\Models\Client, Password, Username
     */

    private function createUserForBooking(Client $client, $booking, $username, $password)
    {
        return User::create([
            'name' => $client->name,
            'booking_id' => $booking->id,
            'role_name' => 'client',
            'username' => $username,
            'password' => bcrypt($password),
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Accepting The Client Model , Username, Password
     *
     * @param App\Models\Client, Password, Username
     */
    private function prepareBookingEmailData(Client $client,  $password, $username): array
    {
        return [
            'name' => $client->name,
            'email' => $client->email,
            'number' => $client->phone_number,
            'venue' => $client->venue,
            'date' => $client->date,
            'message' => 'Booking created successfully',
            'username' => $username,
            'password' => $password,
        ];
    }

    /**
     * Sending Mail To The User
     *
     * @param Password, Username
     */
    private function sendBookingEmail(string $email, array $data): void
    {
        try {
            Mail::to($email)->send(new BookingMail($data));
            Log::info('Booking email sent to ' . $email);
        } catch (\Exception $e) {
            Log::error('Error sending booking email: ' . $e->getMessage());
        }
    }
}
