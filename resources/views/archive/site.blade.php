@extends('layouts.account')
@section('content')
@if($asOf)<p class="selected-date">{{ $t('map.as-of') }}: {{ $asOf }}</p><p class="help">{{ $t('map.history-help') }}</p>@endif
<p class="eyebrow">{{ $site->reference }}</p><p>{{ $t('photo.archive-help') }}</p><p class="help">{{ $t('archive.fallback') }}</p>
<a class="button primary" href="{{ route('observations.create',['site'=>$site->id]) }}">{{ $t('archive.contribute') }}</a>
<div class="timeline">@foreach($observations as $observation)<article class="divider" id="observation-{{ $observation->id }}">@include('archive.observation') @include('archive.photos',['private'=>false]) @include('archive.videos',['private'=>false])
<details><summary>{{ $t('research.cite') }}</summary><p class="help">{{ $t('research.citation-help') }}</p><p style="overflow-wrap:anywhere">{{ \App\Services\ResearchRecord::citation($observation) }}</p><a class="button" href="{{ route('research.citation',['observation'=>$observation->id,'lang'=>$locale]) }}">{{ $t('research.download-citation') }}</a></details>
</article>@endforeach</div>
@endsection
