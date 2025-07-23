<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'level' => 'required|integer|in:1,2',
            'password' => 'required|string|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'level' => $request->level,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'level' => 'required|integer|in:1,2',
        ]);

        $user->update([
            'name' => $request->name,
            'level' => $request->level,
            'username' => $request->username,
        ]);

        return redirect()->back()->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'password' => 'required|string|confirmed',
        ]);

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return redirect()->back()->with('success', 'Kata sandi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
