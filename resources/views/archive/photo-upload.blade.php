<section class="divider"><h2>{{ $t('photo.title') }}</h2><p>{{ $t('photo.help') }}</p><p class="help">{{ $t('photo.reselect') }}</p>@if($revision)<p>{{ $t('photo.resubmit') }}</p>@endif
@for($i=0;$i<4;$i++)<details class="form-panel"><summary>{{ $t('photo.file') }} {{ $i+1 }}</summary>
<label>{{ $t('photo.file') }}<input type="file" name="photos[{{ $i }}][file]" accept="image/jpeg,image/png,image/webp"></label>
<div class="grid2">@foreach(['en','zh'] as $language)<label>{{ $t('photo.caption_'.$language) }}<textarea name="photos[{{ $i }}][caption_{{ $language }}]" lang="{{ $language==='zh'?'zh-Hans':'en' }}" maxlength="2000">{{ old('photos.'.$i.'.caption_'.$language) }}</textarea></label>@endforeach</div>
<label>{{ $t('photo.credit') }}<input name="photos[{{ $i }}][credit]" value="{{ old('photos.'.$i.'.credit') }}" maxlength="200"></label>
<label class="check"><input type="checkbox" name="photos[{{ $i }}][permission]" value="1" @checked(old('photos.'.$i.'.permission'))><span>{{ $t('photo.permission') }}</span></label></details>@endfor</section>
