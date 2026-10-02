@php($mapWords=collect(['map.failed','map.add-here','map.selected','map.location-error','map.zoom-in','map.zoom-out','map.close'])->mapWithKeys(fn($key)=>[$key=>$t($key)]))
<section class="divider" data-atlas-map data-mode="{{ $mode }}" data-markers="{{ json_encode($markers) }}" data-words="{{ json_encode($mapWords) }}" data-create="{{ route('observations.create') }}" data-tiles="{{ config('atlas.tile_url') }}" data-attribution="{{ config('atlas.tile_attribution') }}">
<h2>{{ $t('map.title') }}</h2><p class="help">{{ $t('map.privacy') }}</p>
@if($mode==='browse')<p>{{ $t('map.scope') }}</p><button type="button" data-map-toggle aria-expanded="false" aria-controls="atlas-map-panel">{{ $t('map.show') }}</button>@endif
<div id="atlas-map-panel" @if($mode==='browse') hidden @endif>
<p>{{ $t('map.pick') }}</p><div data-map-canvas class="live-map" role="region" aria-label="{{ $t('map.title') }}"></div>
<p data-map-status role="status" class="help"></p><p data-map-selected role="status" class="help"></p>
<div class="actions"><button type="button" data-map-center>{{ $t('map.center') }}</button><button type="button" data-map-locate>{{ $t('map.locate') }}</button>@if($mode==='pick')<button type="button" data-map-clear>{{ $t('map.clear') }}</button>@endif</div>
@if($mode==='browse')<p>{{ $t('map.legend') }}: @foreach(\App\Models\Category::orderBy('key')->get() as $category)<span style="display:inline-flex;align-items:center;margin-right:14px"><img src="{{ route('assets.marker',$category) }}" width="24" height="24" alt=""> {{ $locale==='en' ? $category->name_en : $category->name_zh }}</span>@endforeach</p>@endif
</div></section>
