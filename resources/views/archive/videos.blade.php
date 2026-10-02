@foreach($observation->interviews as $video)
@if($private || $video->status==='approved')
<article class="form-panel divider"><h2>{{ $video->title() }}</h2><p>{{ $video->credit }}</p>
@if($video->web_path)
<video controls playsinline preload="metadata" style="width:100%;max-height:650px" aria-label="{{ $video->title() }}" src="{{ route($private?'videos.preview':'videos.public',$video) }}">
@foreach(['en'=>'English','zh'=>'简体中文'] as $language=>$label)<track kind="captions" src="{{ route($private?'videos.private-captions':'videos.captions',[$video,$language]) }}" srclang="{{ $language==='zh'?'zh-Hans':'en' }}" label="{{ $label }}" @if(($locale==='zh-Hans')===($language==='zh')) default @endif>@endforeach
</video>
@else<p>{{ $t('video.waiting') }}</p>@endif
@foreach(['en','zh'] as $language)<details><summary>{{ $t('video.transcript_'.$language) }}</summary><p lang="{{ $language==='zh'?'zh-Hans':'en' }}" style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $video->{'transcript_'.$language} }}</p></details>@endforeach
@if($private)<p class="tag">{{ $t('archive.'.$video->status) }}</p>
@can('moderate')
<p><a href="{{ route('videos.original',$video) }}">{{ $t('video.original') }}</a></p><p style="overflow-wrap:anywhere">{{ $t('video.hash-original') }}: {{ $video->original_sha256 }}<br>{{ $t('video.hash-web') }}: {{ $video->web_sha256 }}</p>
@if($video->status==='pending' && $observation->status==='pending')
<details><summary>{{ $t('video.prepare') }}</summary><p>{{ $t('video.manual') }}</p>
<form action="{{ route('videos.prepare',$video) }}" method="post" enctype="multipart/form-data">@csrf
<label>{{ $t('video.web') }}<input type="file" name="web_file" accept="video/mp4,video/webm"></label>
<label>{{ $t('video.duration') }}<input type="number" name="duration_seconds" required min="1" max="600" value="{{ old('duration_seconds',$video->duration_seconds) }}"></label>
<div class="grid2">@foreach(['en','zh'] as $language)<label>{{ $t('video.transcript_'.$language) }}<textarea name="transcript_{{ $language }}" rows="5" maxlength="100000" lang="{{ $language==='zh'?'zh-Hans':'en' }}" required>{{ old('transcript_'.$language,$video->{'transcript_'.$language}) }}</textarea></label>@endforeach</div>
<p class="help">{{ $t('video.vtt-help') }}</p><div class="grid2">@foreach(['en','zh'] as $language)<label>{{ $t('video.captions_'.$language) }}<textarea name="captions_{{ $language }}" rows="6" maxlength="100000" lang="{{ $language==='zh'?'zh-Hans':'en' }}" required>{{ old('captions_'.$language,$video->{'captions_'.$language}) }}</textarea></label>@endforeach</div>
<button class="primary">{{ $t('video.save') }}</button></form></details>
<form class="divider" action="{{ route('videos.review',$video) }}" method="post">@csrf
<label>{{ $t('video.evidence') }}<textarea name="scan_evidence" required maxlength="5000">{{ old('scan_evidence') }}</textarea></label>
<label class="check"><input type="checkbox" name="checks" value="1"><span>{{ $t('video.checks') }}</span></label><div class="actions"><button name="status" value="approved">{{ $t('archive.approve') }}</button><button name="status" value="rejected">{{ $t('archive.reject') }}</button></div></form>
@else<p>{{ $video->scan_evidence }}</p>@endif
@endcan
@endif
</article>
@endif
@endforeach
