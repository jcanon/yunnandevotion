@extends('layouts.account')
@section('content')
<div class="actions"><a class="button" href="{{ route('observations.create') }}">{{ $t('archive.new') }}</a></div>
@forelse($items as $item)<article class="form-panel"><span class="tag">{{ $t('archive.'.$item->status) }}</span><h2><a href="{{ route('observations.private',$item) }}">{{ $item->text('name') }}</a></h2><p>{{ $item->created_at->format('Y-m-d') }}</p></article>@empty<p>{{ $t('archive.empty') }}</p>@endforelse
@include('archive.pagination')
@endsection
