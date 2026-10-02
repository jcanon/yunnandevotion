@extends('layouts.account')
@section('content')
<span class="tag">{{ $t('archive.'.$observation->status) }}</span>
@include('archive.observation',['private'=>true])
@include('archive.photos',['private'=>true]) @include('archive.videos',['private'=>true])
@if($observation->revises_id)<p>{{ $t('archive.revision') }} <a href="{{ route('observations.private',$observation->revises_id) }}">{{ $t('archive.submission') }}</a></p>@endif
@can('moderate')<p>{{ $t('archive.author') }}: {{ $observation->author->name }} · {{ $observation->author->email }}</p>@endcan
@foreach($observation->decisions as $decision)<div class="selected-date"><strong>{{ $t('archive.'.$decision->decision) }}</strong><p>{{ $decision->reason }}</p><small>{{ $decision->created_at->format('Y-m-d H:i') }}</small></div>@endforeach
@if($observation->status==='approved')<a class="button" href="{{ route('sites.show',$observation->site_id) }}">{{ $t('archive.history') }}</a>@endif
@if($observation->author_id===auth()->id() && in_array($observation->status,['changes_requested','rejected']))<a class="button" href="{{ route('observations.create',['revise'=>$observation->id]) }}">{{ $t('archive.revise') }}</a>@endif
@can('moderate')@if($observation->status==='pending')<form class="divider" action="{{ route('observations.decide',$observation) }}" method="post">@csrf<p>{{ $t('archive.review-help') }}</p><label>{{ $t('archive.reason') }}<textarea name="reason" rows="4" maxlength="5000">{{ old('reason') }}</textarea></label><div class="actions"><button name="decision" value="approved" class="primary">{{ $t('archive.approve') }}</button><button name="decision" value="changes_requested">{{ $t('archive.return') }}</button><button name="decision" value="rejected">{{ $t('archive.reject') }}</button></div></form>@endif
@endcan
@endsection
