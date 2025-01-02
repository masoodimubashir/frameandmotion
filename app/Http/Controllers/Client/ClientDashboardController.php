<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use Illuminate\Http\Request;

class ClientDashboardController extends Controller
{
    public function dashboard()
    {



        return view('client.dashboard.index');
    }


    public function getMilestone()
    {
        $milestones = Milestone::oldest()->get();

        return response()->json($milestones);
    }
}
