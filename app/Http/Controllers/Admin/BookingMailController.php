<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingMail;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BookingMailController extends Controller
{
    //


    public function sendBookMail(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'number' => 'required',
            'venue' => 'required',
            'date' => 'required',
            'message' => 'required',
        ]);

        $userEmail = $request->email;

      

        // try {
          
        //     Mail::to('masudimubashir@gmail.com')->send(new BookingMail($request->all()));

        //     Mail::to($userEmail)->send(new BookingMail($request->all()));

        //     return response()->json(['success' => 'Booking email sent successfully to both admin and user!']);

        // } catch (\Exception $e) {
        //     return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
        // }
    }


    // $toEmail = $request->email;
    // $message = 'Testing the booking email';

    // Mail::to($toEmail)->send(new BookingMail($message));

    // }

    public function getFormDetails(Request $request){

        
        Client::create($request->all());

        return redirect()->back();

    }

}
