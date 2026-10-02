<section class="divider"><h2>{{ $t('video.title') }}</h2><p>{{ $t('video.help') }}</p><p class="help">{{ $t('photo.reselect') }}</p>
<details class="form-panel"><summary>{{ $t('video.file') }}</summary>
<label>{{ $t('video.file') }}<input type="file" name="interview[file]" accept="video/mp4,video/webm"></label>
<div class="grid2">@foreach(['en','zh'] as $language)<label>{{ $t('video.title_'.$language) }}<input name="interview[title_{{ $language }}]" lang="{{ $language==='zh'?'zh-Hans':'en' }}" maxlength="200" value="{{ old('interview.title_'.$language) }}"></label>@endforeach</div>
<label>{{ $t('photo.credit') }}<input name="interview[credit]" maxlength="200" value="{{ old('interview.credit') }}"></label>
<label class="check"><input type="checkbox" name="interview[permission]" value="1" @checked(old('interview.permission'))><span>{{ $t('video.permission') }}</span></label>
<div class="grid2">@foreach(['en','zh'] as $language)<label>{{ $t('video.transcript_'.$language) }}<textarea name="interview[transcript_{{ $language }}]" lang="{{ $language==='zh'?'zh-Hans':'en' }}" rows="5" maxlength="100000">{{ old('interview.transcript_'.$language) }}</textarea></label>@endforeach</div></details></section>
