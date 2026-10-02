@extends('layouts.account')
@section('content')
@if($invitation)
<h2>{{ $invitation->name }}</h2><p style="overflow-wrap:anywhere">{{ $invitation->email }}</p><p>{{ $t('role') }}: {{ $t('role.'.$invitation->role) }}</p>
<form action="{{ route('invitation.accept',['token'=>$token]) }}" method="post">@csrf
<label>{{ $t('password') }}<input type="password" name="password" required minlength="12" maxlength="128" autocomplete="new-password"><span class="help">{{ $t('password.help') }}</span></label>
<label>{{ $t('confirmation') }}<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
<button class="primary">{{ $t('invite.accept') }}</button></form>
@else <p>{{ $t('invite.invalid') }}</p>@endif
@endsection
