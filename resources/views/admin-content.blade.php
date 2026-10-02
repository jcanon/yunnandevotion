@extends('layouts.account')
@section('content')
<p>{{ $t('content.help') }}</p>
@if($home)<a href="{{ route('home',['lang'=>$locale]) }}">{{ $t('content.view') }}</a>
@else<form method="get" class="filters"><label>{{ $t('content.search') }}<input name="search" value="{{ $search }}" maxlength="200"></label><button class="primary">{{ $t('map.apply') }}</button></form>@endif
@forelse($items as $item)
@php($pair = $rows[$item->key])
@php($retry = old('key') === $item->key)
<form method="post" action="{{ route('admin.content.update') }}" class="divider">
@csrf @method('patch')
<h2 style="overflow-wrap:anywhere">{{ $home ? $t('content.'.$item->key) : $item->key }}</h2>
<input type="hidden" name="key" value="{{ $item->key }}">
<input type="hidden" name="version" value="{{ $retry ? old('version') : \App\Http\Controllers\ContentAdminController::version($pair) }}">
<div class="grid2">@foreach(['en'=>'English','zh'=>'简体中文'] as $field=>$label)
<label>{{ $label }}<textarea name="{{ $field }}" lang="{{ $field==='en' ? 'en' : 'zh-Hans' }}" rows="5" maxlength="20000" required>{{ $retry ? old($field) : $pair->firstWhere('locale',$field==='en' ? 'en' : 'zh-Hans')?->value }}</textarea></label>
@endforeach</div>
<button class="primary">{{ $t('content.save') }}</button>
</form>
@empty<p>{{ $t('content.empty') }}</p>@endforelse
@include('archive.pagination')
@endsection
