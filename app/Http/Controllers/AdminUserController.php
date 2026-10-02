<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use App\Support\Words;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin-users', ['screen' => 'users', 'users' => User::orderBy('id')->paginate(20), 'invitations' => Invitation::whereNull('accepted_at')->orderByDesc('id')->paginate(10, ['*'], 'invitations_page')]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['required', Rule::in(['contributor', 'moderator', 'admin'])], 'active' => ['required', 'boolean']], ['required' => Words::get('error.required'), 'in' => Words::get('error.role'), 'boolean' => Words::get('error.role')]);
        DB::transaction(function () use ($user, $data) {
            // Serialize changes involving existing admins so concurrent requests cannot remove the last one.
            $admins = User::where('role', 'admin')->orderBy('id')->lockForUpdate()->get();
            $target = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $usableAdmins = $admins->filter(fn ($admin) => $admin->active && $admin->hasVerifiedEmail());
            if ($usableAdmins->contains('id', $target->id) && $usableAdmins->count() === 1 && ($data['role'] !== 'admin' || ! $data['active'])) {
                throw ValidationException::withMessages(['role' => Words::get('error.last-admin')]);
            }
            $target->forceFill($data)->save();
        });

        return back()->with('status', Words::get('users.saved'));
    }
}
