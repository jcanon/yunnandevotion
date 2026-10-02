<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Words;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    private function credentials(Request $request, array $rules): array
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        return $request->validate($rules, [
            'required' => Words::get('error.required'), 'email' => Words::get('error.email'),
            'min' => Words::get('error.password'), 'max' => Words::get('error.long'),
            'confirmed' => Words::get('error.confirm'), 'unique' => Words::get('error.duplicate'),
            'string' => Words::get('error.required'),
        ]);
    }

    public function register(Request $request)
    {
        $data = $this->credentials($request, ['name' => 'required|string|max:80', 'email' => 'required|email|max:254|unique:users', 'password' => 'required|string|min:12|max:128|confirmed']);
        $user = User::create($data);
        $user->locale = app()->getLocale();
        $user->save();
        event(new Registered($user));
        Auth::login($user->fresh());
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function login(Request $request)
    {
        $data = $this->credentials($request, ['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt([...$data, 'active' => true])) {
            return back()->withErrors(['email' => Words::get('error.login')])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended(route('profile'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function forgot(Request $request)
    {
        $data = $this->credentials($request, ['email' => 'required|email']);
        Password::sendResetLink([...$data, 'active' => true]);

        return back()->with('status', Words::get('reset.sent'));
    }

    public function reset(Request $request)
    {
        $data = $this->credentials($request, ['email' => 'required|email', 'token' => 'required|string', 'password' => 'required|string|min:12|max:128|confirmed']);
        $status = Password::reset([...$data, 'active' => true], function (User $user, string $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', Words::get('reset.done'))
            : back()->withErrors(['email' => Words::get('reset.invalid')])->onlyInput('email');
    }
}
