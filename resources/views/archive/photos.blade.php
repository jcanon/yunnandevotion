@foreach($observation->photographs as $photo)
@if($private || $photo->status==='approved')
<figure class="form-panel" style="margin:24px 0"><img src="{{ route($private?'photos.preview':'photos.public',$photo) }}" alt="{{ $photo->caption() }}" style="max-width:100%;max-height:650px;object-fit:contain" decoding="async"><figcaption><p>{{ $photo->caption() }}</p><p>{{ $photo->credit }}</p></figcaption>
@if($private)<span class="tag">{{ $t('archive.'.$photo->status) }}</span>
@can('moderate')
<p><a href="{{ route('photos.original',$photo) }}">{{ $t('photo.original') }}</a></p><p style="overflow-wrap:anywhere">SHA-256: {{ $photo->sha256 }}</p>
@if($photo->status==='pending' && $observation->status==='pending')
<form action="{{ route('photos.review',$photo) }}" method="post">@csrf<p class="help">{{ $t('photo.scan-help') }}</p><label>{{ $t('photo.evidence') }}<textarea name="scan_evidence" required maxlength="5000"></textarea></label><label class="check"><input type="checkbox" name="checks" value="1"><span>{{ $t('photo.checks') }}</span></label><div class="actions"><button name="status" value="approved">{{ $t('archive.approve') }}</button><button name="status" value="rejected">{{ $t('archive.reject') }}</button></div></form>
@else <p>{{ $photo->scan_evidence }}</p>@endif
@endcan
@endif
</figure>
@endif
@endforeach
