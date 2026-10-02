<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use App\Notifications\AccountInvitation;
use App\Support\Words;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InvitationController extends Controller
{
    public function store(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => 'required|string|max:80', 'email' => 'required|email|max:254|unique:users|unique:invitations',
            'role' => ['required', Rule::in(['contributor', 'moderator', 'admin'])],
            'locale' => ['required', Rule::in(['en', 'zh-Hans'])],
        ], ['required' => Words::get('error.required'), 'email' => Words::get('error.email'), 'unique' => Words::get('invite.duplicate'), 'max' => Words::get('error.long'), 'in' => Words::get('error.role')]);
        $token = Str::random(64);
        $invite = Invitation::create([...$data, 'token_hash' => hash('sha256', $token), 'expires_at' => now()->addDays(7), 'invited_by' => $request->user()->id]);
        Notification::route('mail', $invite->email)->notify(new AccountInvitation($token, $invite->locale));

        return back()->with('status', Words::get('invite.sent'));
    }

    public function resend(Invitation $invitation)
    {
        $token = Str::random(64);
        DB::transaction(function () use ($invitation, $token) {
            $invite = Invitation::whereKey($invitation->id)->lockForUpdate()->firstOrFail();
            abort_if($invite->accepted_at || $invite->revoked_at || User::where('email', $invite->email)->exists(), 409);
            $invite->update(['token_hash' => hash('sha256', $token), 'expires_at' => now()->addDays(7)]);
        });
        Notification::route('mail', $invitation->email)->notify(new AccountInvitation($token, $invitation->locale));

        return back()->with('status', Words::get('invite.sent'));
    }

    public function revoke(Invitation $invitation)
    {
        DB::transaction(function () use ($invitation) {
            $invite = Invitation::whereKey($invitation->id)->lockForUpdate()->firstOrFail();
            abort_if($invite->accepted_at, 409);
            $invite->update(['revoked_at' => now()]);
        });

        return back()->with('status', Words::get('invite.revoked'));
    }

    public function show(string $token)
    {
        $invitation = Invitation::where('token_hash', hash('sha256', $token))->first();

        return response()->view('invitation', ['screen' => 'invite.accept', 'invitation' => $invitation?->usable() ? $invitation : null, 'token' => $token], $invitation?->usable() ? 200 : 410)
            ->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function accept(Request $request, string $token)
    {
        $data = $request->validate(['password' => 'required|string|min:12|max:128|confirmed'], ['required' => Words::get('error.required'), 'min' => Words::get('error.password'), 'max' => Words::get('error.long'), 'confirmed' => Words::get('error.confirm')]);
        $user = DB::transaction(function () use ($token, $data) {
            $invite = Invitation::where('token_hash', hash('sha256', $token))->lockForUpdate()->first();
            abort_unless($invite?->usable(), 410);
            if (User::where('email', $invite->email)->exists()) {
                throw ValidationException::withMessages(['email' => Words::get('invite.duplicate')]);
            }
            $user = User::create(['name' => $invite->name, 'email' => $invite->email, 'password' => $data['password']]);
            // Possession of the single-use emailed secret proves control of this email address.
            $user->forceFill(['role' => $invite->role, 'locale' => $invite->locale, 'email_verified_at' => now()])->save();
            $invite->update(['accepted_at' => now()]);

            return $user;
        });
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('profile');
    }
}
