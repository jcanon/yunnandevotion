<h2>{{ $observation->text('name') }}</h2><p class="tag">{{ $t('archive.'.$observation->condition) }}</p>
<p>{{ $t('archive.date_precision') }}: {{ $t('archive.'.$observation->date_precision) }} · {{ $observation->observed_from?->format('Y-m-d') ?? $t('archive.unknown') }} @if($observation->observed_to) — {{ $observation->observed_to->format('Y-m-d') }} @endif</p>
<p>{{ \App\Models\Category::label($observation->content['category']) }}</p>
<p style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $observation->text('description') }}</p>
@if($observation->text('source'))<p style="white-space:pre-wrap">{{ $observation->text('source') }}</p>@endif
<p>{{ $t('profile.credit') }}: {{ $observation->content['anonymous_credit'] ? $t('profile.anonymous') : $observation->content['credit'] }}</p>
<p>{{ $observation->content['location_notes'] }}</p>
@if($observation->location_visibility==='exact' || ($private??false))
@if($observation->latitude!==null)<p>{{ $t('archive.latitude') }}: {{ $observation->latitude }} · {{ $t('archive.longitude') }}: {{ $observation->longitude }}</p>@endif
@else<p class="help">{{ $t('archive.approximate') }}</p>@endif
