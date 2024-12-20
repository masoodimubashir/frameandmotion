<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('role_name', 'client')->latest()->paginate(10);
        $bookings = Booking::all();
        return view('admin.user.show-user', compact('users', 'bookings'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username' ,
            'password' =>  [
                Password::default()->required()
            ],
            'is_active' => 'nullable|boolean',
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $validatedData = $validator->validated();

        try {

            User::create([
                'name' => $validatedData['name'],
                'username' => $validatedData['username'],
                'password' => bcrypt($validatedData['password']),
                'is_active' => $validatedData['is_active'] ?? 0,
                'booking_id' => $validatedData['booking_id'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
            ], 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create user', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return $this->findUser($id, function ($user) {
            return response()->json([
                'status' => 'success',
                'user' => $user,
            ]);
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $this->validateUser($request, $id);

        return $this->findUser($id, function ($user) use ($validatedData) {
            try {
                $user->update([
                    'name' => $validatedData['name'],
                    'username' => $validatedData['username'],
                    'password' => $validatedData['password'] ? bcrypt($validatedData['password']) : $user->password,
                    'is_active' => $validatedData['is_active'] ?? 0,
                    'booking_id' => $validatedData['booking_id'],
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'User updated successfully',
                ]);
            } catch (\Exception $e) {
                return $this->errorResponse('Failed to update user', $e);
            }
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->findUser($id, function ($user) {
            try {
                $user->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'User deleted successfully',
                ]);
            } catch (\Exception $e) {
                return $this->errorResponse('Failed to delete user', $e);
            }
        });
    }

    /**
     * Validate user input.
     */
    private function validateUser(Request $request, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username' . ($id ? ",$id" : ''),
            'password' => $id ? 'nullable|string|min:8' : 'required|string|min:8',
            'is_active' => 'nullable|boolean',
            'booking_id' => 'required|exists:bookings,id',
        ];

        return $request->validate($rules);
    }

    /**
     * Handle user retrieval and apply callback.
     */
    private function findUser($id, callable $callback)
    {
        try {
            $user = User::findOrFail($id);
            return $callback($user);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }
    }

    /**
     * Return a standardized error response.
     */
    private function errorResponse($message, \Exception $e)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'error' => $e->getMessage(),
        ], 500);
    }
}
