@extends('layouts.account')
@section('content')
@if(in_array($screen, ['login','register','forgot','reset']))
<form method="post" action="{{ match($screen) { 'login'=>route('login'), 'register'=>route('register'), 'forgot'=>route('password.email'), 'reset'=>route('password.update') } }}">
@csrf
@if($screen==='register')<label>{{ $t('name') }}<input name="name" value="{{ old('name') }}" required maxlength="80" autocomplete="nickname"></label>@endif
<label>{{ $t('email') }}<input type="email" name="email" value="{{ old('email', request('email')) }}" required maxlength="254" autocomplete="email"></label>
@if($screen!=='forgot')
<label>{{ $t('password') }}<input name="password" type="password" required @if($screen!=='login') minlength="12" maxlength="128" autocomplete="new-password" @else autocomplete="current-password" @endif></label>
@endif
@if(in_array($screen,['register','reset']))
<p class="help">{{ $t('password.help') }}</p><label>{{ $t('confirmation') }}<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
@endif
@if($screen==='reset')<input type="hidden" name="token" value="{{ $token }}">@endif
<button class="primary full-button">{{ $t($screen==='forgot'?'reset.send':$screen) }}</button>
@if($screen==='login')<p class="divider"><a href="{{ route('password.request') }}">{{ $t('forgot') }}</a></p>@endif
</form>
@elseif($screen==='verify')
<p>{{ $t('verify.help') }}</p><form method="post" action="{{ route('verification.send') }}">@csrf<button class="primary">{{ $t('verify.resend') }}</button></form>
@elseif($screen==='profile')
<h2>{{ auth()->user()->name }}</h2><p>{{ auth()->user()->email }}</p><p>{{ $t('role') }}: {{ $t('role.'.auth()->user()->role) }}</p>
<p class="help">{{ $t('profile.email-help') }}</p>
<form action="{{ route('profile.update') }}" method="post">@csrf @method('PATCH')
<label>{{ $t('name') }}<input name="name" value="{{ old('name',auth()->user()->name) }}" required maxlength="80" autocomplete="nickname"></label>
<label>{{ $t('profile.bio-en') }}<textarea name="bio_en" lang="en" rows="4" maxlength="2000">{{ old('bio_en',auth()->user()->bio_en) }}</textarea></label>
<label>{{ $t('profile.bio-zh') }}<textarea name="bio_zh" lang="zh-Hans" rows="4" maxlength="2000">{{ old('bio_zh',auth()->user()->bio_zh) }}</textarea></label>
<label>{{ $t('profile.language') }}<select name="locale"><option value="en" @selected(old('locale',auth()->user()->locale)==='en')>English</option><option value="zh-Hans" @selected(old('locale',auth()->user()->locale)==='zh-Hans')>简体中文</option></select></label>
<label>{{ $t('profile.credit') }}<select name="anonymous_credit"><option value="0" @selected(!old('anonymous_credit',auth()->user()->anonymous_credit))>{{ $t('name') }}</option><option value="1" @selected(old('anonymous_credit',auth()->user()->anonymous_credit))>{{ $t('profile.anonymous') }}</option></select><span class="help">{{ $t('profile.credit-help') }}</span></label>
<button class="primary">{{ $t('profile.save') }}</button></form>
@else <p>{{ $t('workspace.pending') }}</p>
@if($screen==='admin')<a class="button primary" href="{{ route('admin.users') }}">{{ $t('users') }}</a>@endif
@endif
@endsection
