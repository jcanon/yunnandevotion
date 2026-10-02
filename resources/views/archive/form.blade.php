@extends('layouts.account')
@section('content')
<p>{{ $t('photo.archive-help') }}</p><p class="help">{{ $t('archive.fallback') }}</p>
@if($site)<p class="eyebrow">{{ $site->reference }}</p>@endif
<form action="{{ route('observations.store') }}" method="post" enctype="multipart/form-data">@csrf
<input type="hidden" name="site_id" value="{{ $site?->id }}"><input type="hidden" name="revises_id" value="{{ $revision?->id }}">
<div class="grid2">@foreach(['name_en','name_zh','description_en','description_zh','source_en','source_zh'] as $field)<label>{{ $t('archive.'.$field) }}
@if(str_starts_with($field,'name'))<input name="{{ $field }}" lang="{{ str_ends_with($field,'zh')?'zh-Hans':'en' }}" maxlength="200" value="{{ old($field,$revision?->content[$field]??(str_starts_with($field,'name')?($latest?->content[$field]??''):'')) }}">
@else<textarea name="{{ $field }}" lang="{{ str_ends_with($field,'zh')?'zh-Hans':'en' }}" rows="4">{{ old($field,$revision?->content[$field]??(str_starts_with($field,'name')?($latest?->content[$field]??''):'')) }}</textarea>@endif</label>@endforeach</div>
<div class="grid2">@foreach(['category'=>$categories,'condition'=>$conditions,'date_precision'=>['exact','range','unknown'],'location_visibility'=>['approximate','exact']] as $field=>$choices)<label>{{ $t('archive.'.$field) }}<select name="{{ $field }}">@foreach($choices as $choice)<option value="{{ $choice }}" @selected(old($field,$field==='category'?($revision?->content['category']??'shrine'):($revision?->$field??($field==='date_precision'?'unknown':$choices[0])))===$choice)>{{ $field==='category' ? \App\Models\Category::label($choice) : $t('archive.'.$choice) }}</option>@endforeach</select></label>@endforeach
@foreach(['observed_from','observed_to'] as $field)<label>{{ $t('archive.'.$field) }}<input type="date" name="{{ $field }}" max="{{ now()->format('Y-m-d') }}" value="{{ old($field,$revision?->$field?->format('Y-m-d')) }}"></label>@endforeach
@foreach(['latitude','longitude'] as $field)<label>{{ $t('archive.'.$field) }}<input type="number" step="any" name="{{ $field }}" value="{{ old($field,$revision?->$field??($picked[$field==='latitude'?'lat':'lng']??null)) }}" min="{{ $field==='latitude'?-90:-180 }}" max="{{ $field==='latitude'?90:180 }}"></label>@endforeach</div>
@include('archive.map',['mode'=>'pick','markers'=>[]])
<label>{{ $t('archive.location_notes') }}<textarea name="location_notes" maxlength="2000">{{ old('location_notes',$revision?->content['location_notes']??'') }}</textarea><span class="help">{{ $t('archive.privacy') }}</span></label>
<label>{{ $t('profile.credit') }}<select name="anonymous_credit"><option value="0" @selected(!old('anonymous_credit',$revision?->content['anonymous_credit']??auth()->user()->anonymous_credit))>{{ $t('name') }}</option><option value="1" @selected(old('anonymous_credit',$revision?->content['anonymous_credit']??auth()->user()->anonymous_credit))>{{ $t('profile.anonymous') }}</option></select></label>
@include('archive.photo-upload')
@include('archive.video-upload')
<button class="primary">{{ $t(auth()->user()->can('moderate')?'archive.publish':'archive.submit') }}</button></form>
@endsection
