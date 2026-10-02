<?php

namespace App\Http\Middleware;

use App\Models\InterfaceTranslation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AccountContext
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->query('lang', $request->session()->get('locale', $request->user()?->locale ?? 'en'));
        abort_unless(in_array($locale, ['en', 'zh-Hans'], true), 404);
        app()->setLocale($locale);
        $request->session()->put('locale', $locale);
        if ($request->user() && ! $request->user()->fresh()?->active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }
        $words = InterfaceTranslation::where('locale', $locale)->pluck('value', 'key');
        View::share(['locale' => $locale, 't' => fn ($key) => $words[$key] ?? $key]);

        return $next($request);
    }
}
