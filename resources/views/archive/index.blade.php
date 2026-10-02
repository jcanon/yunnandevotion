@extends('layouts.account')
@section('content')
<p>{{ $t('map.current') }}</p>
<form method="get" action="{{ route('atlas') }}" class="filters">
<label>{{ $t('map.search') }}<input name="search" type="search" value="{{ request('search') }}" maxlength="200"></label>
<label>{{ $t('map.as-of') }}<input name="as_of" type="date" max="{{ now()->format('Y-m-d') }}" value="{{ $asOf }}"></label>
@foreach(['category'=>$categories,'condition'=>$conditions] as $field=>$choices)<label>{{ $t('archive.'.$field) }}<select name="{{ $field }}"><option value="">{{ $t('map.all') }}</option>@foreach($choices as $choice)<option value="{{ $choice }}" @selected(request($field)===$choice)>{{ $field==='category' ? \App\Models\Category::label($choice) : $t('archive.'.$choice) }}</option>@endforeach</select></label>@endforeach
<div class="actions"><button class="primary">{{ $t('map.apply') }}</button><a href="{{ route('atlas') }}">{{ $t('map.reset') }}</a></div></form>
@if($asOf)<p class="selected-date">{{ $t('map.as-of') }}: {{ $asOf }}</p><p class="help">{{ $t('map.history-help') }}</p>@endif
<div class="actions"><a class="button primary" href="{{ route('observations.create') }}">{{ $t('archive.new') }}</a></div>
@include('archive.map',['mode'=>'browse'])
<div class="grid3">@forelse($items as $latest)<article class="record-card"><p class="eyebrow">{{ $latest->site->reference }}</p><h2><a href="{{ route('sites.show',['site'=>$latest->site_id,'as_of'=>$asOf]) }}">{{ $latest->text('name') }}</a></h2><span class="tag">{{ $t('archive.'.$latest->condition) }}</span><p>{{ \App\Models\Category::label($latest->content['category']) }}</p><p>{{ $latest->observed_from?->format('Y-m-d') ?? $t('archive.unknown') }}@if($latest->observed_to) — {{ $latest->observed_to->format('Y-m-d') }}@endif</p></article>@empty<p>{{ $t('archive.empty') }}</p>@endforelse</div>
@include('archive.pagination')
<details class="divider"><summary>{{ $t('research.title') }}</summary>
<p>{{ $t('research.help') }}</p><p class="help">{{ $t('research.format') }}</p>
<div class="actions">@foreach(['csv','geojson'] as $format)<a class="button" href="{{ route('research.export', ['format'=>$format] + request()->only(['search','category','condition','as_of']) + ['lang'=>$locale]) }}">{{ $t('research.'.$format) }}</a>@endforeach</div>
</details>
@endsection
