<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class DashboardController extends Controller
{
    public function dashboard()
    {

        return view('admin.dashboard.index');


    }

    public function show()
    {

        $user = Auth::user();

        return view('admin.dashboard.edit-profile', compact('user'));
    }

    public function editNameUsername(Request $request, $id)
    {

        $request->validate([
            'name' => 'sometimes|required|string|min:5',
            'username' => 'sometimes|required|string|min:5|unique:users,username,' . $id,
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'name' => $request->name,
            'username' => $request->username
        ]);

        return redirect()->back()->with('success', 'Data Updated Successfully');
    }

    public function editPassword(Request $request, $id)
    {
        $request->validate([
            'password' => ['required', Password::default(), 'confirmed']
        ]);

        $user = User::find($id);

        $user->update([
            'password' => $request->password,
        ]);

        return redirect()->back()->with('success', 'Password Updated Successfully');

    }
}
