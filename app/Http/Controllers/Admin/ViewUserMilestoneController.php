<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

class ViewUserMilestoneController extends Controller
{
    public function index($id)
    {

        return view('admin.milestone.view-milestone');
    }

    public function getUser($id)
    {

        try {

            $user = User::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'user' => $user
            ]);
            
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch user details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
