@extends('layouts.account')
@section('content')
<p><a href="{{ route('admin') }}">{{ $t('admin') }}</a></p>
<p>{{ $t('users.help') }}</p>
<details class="form-panel"><summary>{{ $t('invite.add') }}</summary><p>{{ $t('invite.help') }}</p>
<form method="post" action="{{ route('invitations.store') }}">@csrf
<label>{{ $t('name') }}<input name="name" value="{{ old('name') }}" required maxlength="80"></label>
<label>{{ $t('email') }}<input type="email" name="email" value="{{ old('email') }}" required maxlength="254"></label>
<label>{{ $t('role') }}<select name="role">@foreach(['contributor','moderator','admin'] as $role)<option value="{{ $role }}" @selected(old('role','contributor')===$role)>{{ $t('role.'.$role) }}</option>@endforeach</select></label>
<label>{{ $t('profile.language') }}<select name="locale"><option value="en">English</option><option value="zh-Hans" @selected(old('locale')==='zh-Hans')>简体中文</option></select></label>
<button class="primary">{{ $t('invite.send') }}</button></form></details>
@if($invitations->count())<section class="divider"><h2>{{ $t('invite.pending') }}</h2>
@foreach($invitations as $invite)<article class="form-panel"><h3>{{ $invite->name }}</h3><p style="overflow-wrap:anywhere">{{ $invite->email }}</p><p>{{ $t('role.'.$invite->role) }}</p>
<span class="tag">{{ $t($invite->revoked_at?'invite.revoked':($invite->expires_at->isPast()?'invite.expired':'invite.waiting')) }}</span>
@unless($invite->revoked_at)<div class="actions"><form action="{{ route('invitations.resend',$invite) }}" method="post">@csrf<button>{{ $t('invite.resend') }}</button></form><form action="{{ route('invitations.revoke',$invite) }}" method="post">@csrf @method('DELETE')<button>{{ $t('invite.revoke') }}</button></form></div>@endunless
</article>@endforeach
<nav class="actions">@if($invitations->previousPageUrl())<a href="{{ $invitations->previousPageUrl() }}">{{ $t('users.previous') }}</a>@endif @if($invitations->nextPageUrl())<a href="{{ $invitations->nextPageUrl() }}">{{ $t('users.next') }}</a>@endif</nav></section>@endif
@foreach($users as $user)
<form class="divider" action="{{ route('admin.users.update', $user) }}" method="post">
@csrf @method('PATCH')
<h2>{{ $user->name }}</h2><p style="overflow-wrap:anywhere">{{ $user->email }}</p>
<p class="tag">{{ $t($user->hasVerifiedEmail()?'users.verified':'users.unverified') }}</p>
<label>{{ $t('role') }}<select name="role">@foreach(['contributor','moderator','admin'] as $role)<option value="{{ $role }}" @selected($user->role===$role)>{{ $t('role.'.$role) }}</option>@endforeach</select></label>
<label>{{ $t('users.active') }}<select name="active"><option value="1" @selected($user->active)>{{ $t('users.active') }}</option><option value="0" @selected(!$user->active)>{{ $t('users.suspended') }}</option></select></label>
<button class="primary">{{ $t('users.save') }}</button>
</form>
@endforeach
<nav class="actions">@if($users->previousPageUrl())<a class="button" href="{{ $users->previousPageUrl() }}">{{ $t('users.previous') }}</a>@endif @if($users->nextPageUrl())<a class="button" href="{{ $users->nextPageUrl() }}">{{ $t('users.next') }}</a>@endif</nav>
@endsection
