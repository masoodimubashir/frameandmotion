<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MilestoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $users = User::where('role_name', 'client')->latest()->get();

        $milestones = Milestone::latest()->paginate(10);

        return response()->json([
            'milestones' => $milestones,
            'users' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {

            Debugbar::error($request->has('is_in_progress') ? $request->is_in_progress : 0);


            $validation = Validator::make($request->all(), [
                'description' => 'required|string',
                'name' => 'required|string',
                'user_id' => 'required|exists:users,id',
                // 'is_in_progress' => 'required|boolean|required_without:is_completed',
                // 'is_completed' => 'required|boolean|required_without:is_in_progress',
            ])->after(function ($validator) use ($request) {
                // Ensure that at least one of the two fields is present
                if (!$request->has('is_in_progress') && !$request->has('is_completed')) {
                    $validator->errors()->add('status', 'Either is_in_progress or is_completed must be provided.');
                }
            });

            if ($validation->fails()) {
                Debugbar::error($validation->errors());
                return response()->json(['status' => 'error', 'errors' => $validation->errors()], 422);
            }

            $is_in_progress = ($request->is_in_progress === 'true') ? 1 : 0;
            $is_completed = ($request->is_completed === 'true') ? 1 : 0;

            $start_date = ($is_in_progress === 1) ? now() : null;
            $completion_date = ($is_completed === 1) ? now() : null;

            $milestone = Milestone::create([
                'description' => $request->description,
                'name' => $request->name,
                'user_id' => $request->user_id,
                'is_in_progress' => $is_in_progress,
                'is_completed' => $is_completed,
                'start_date' => $start_date,
                'completion_date' => $completion_date
            ]);

            return response()->json(['status' => 'success', 'milestone' => $milestone], 201);

            return response()->json([
                'message' => 'Milestone created successfully.',
                'milestone' => $milestone,
            ]);
        } catch (\Throwable $th) {
            Log::error('Error creating milestone: ' . $th->getMessage());
            return response()->json(['message' => 'Failed to create milestone.'], 500);
        }
    }


    public function show($id)
    {
        try {


            $user = User::with(['milestones' => function ($query) {
                $query->latest()->paginate(10);
            }])->findOrFail($id);

            return response()->json([
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve milestones.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $milestone = Milestone::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'drive_id' => 'required|string|max:255',
            ]);

            $milestone->update($request->all());

            return response()->json([
                'message' => 'Milestone updated successfully.',
                'milestone' => $milestone,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Failed to update milestone.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $milestone = Milestone::findOrFail($id);
            $milestone->delete();

            return response()->json(['message' => 'Milestone deleted successfully.']);
        } catch (\Throwable $th) {
            Log::error('Error deleting milestone: ' . $th->getMessage());
            return response()->json(['message' => 'Failed to delete milestone.'], 500);
        }
    }
}
