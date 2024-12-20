<?php

namespace App\Http\Controllers;

use App\Models\Admin\clients;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ClientsController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = CLient::latest()->paginate(10);
        return view('admin.clients.show-clients', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:5',
            'address' => 'required|string',
            'phone' => 'required|integer|digits:10',
            'email' => 'required|email|unique:clients,email',
            'venue' => 'required|string'
        ]);

        // Check validation
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create new user
            $client = Client::create([
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'venue' => $request->venue,
                'created_by' => Auth::user()->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Client created successfully',
                'client' => $client
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create Client',
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
            $client = Client::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'client' => $client
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'client not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $client = Client::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:5',
            'address' => 'required|string',
            'phone' => 'required|integer|digits:10',
            'venue' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('clients', 'email')->ignore($id),
            ],
        ]);

        // Check validation
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $client->update([
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'venue' => $request->venue,
                'updated_by' => Auth::user()->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Client updated successfully',
                'client' => $client
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update client',
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
            $client = Client::findOrFail($id);
            $client->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Client deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete Client',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
