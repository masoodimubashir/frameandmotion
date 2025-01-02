<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use Barryvdh\Debugbar\Facades\Debugbar;
use Barryvdh\Debugbar\Twig\Extension\Debug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $bookings = Booking::with('client')->latest()->paginate(10);

        $clients = Client::all();

        return view('admin.booking.show-booking', compact('bookings', 'clients'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'booking_date' => 'required|date|',
            'ceremony_date' => 'required|date|after_or_equal:booking_date',

        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }


        try {
            
            $booking = Booking::create([
                'client_id' => $request->client_id,
                'booking_date' => $request->booking_date,
                'ceremony_date' => $request->ceremony_date,
                'created_by' => Auth::user()->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully',
                'booking' => $booking
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking',
                'error' => $e->getMessage()
            ], 500);
            
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $booking = Booking::with('client')->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'booking' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'booking_date' => 'required|date',
            'ceremony_date' => 'required|date|after_or_equal:booking_date',
        ]);

        // Check validation
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $booking->update([
                'client_id' => $request->client_id,
                'booking_date' => $request->booking_date,
                'ceremony_date' => $request->ceremony_date,
                'updated_by' => Auth::user()->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking updated successfully',
                'booking' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

            Debugbar::error($id);

            $booking = Booking::findOrFail($id);
            $booking->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking deleted successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
