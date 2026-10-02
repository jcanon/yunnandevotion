<?php

namespace App\Http\Controllers;

use App\Support\Words;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80', 'bio_en' => 'nullable|string|max:2000', 'bio_zh' => 'nullable|string|max:2000',
            'locale' => ['required', Rule::in(['en', 'zh-Hans'])], 'anonymous_credit' => 'required|boolean',
        ], ['required' => Words::get('error.required'), 'max' => Words::get('error.long'), 'in' => Words::get('error.role'), 'boolean' => Words::get('error.role')]);
        $request->user()->forceFill($data)->save();
        $request->session()->put('locale', $data['locale']);

        return redirect()->route('profile')->with('status', Words::get('profile.saved', $data['locale']));
    }
}
