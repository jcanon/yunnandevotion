@extends('layouts.account')
@section('content')
<p>{{ $t('assets.help') }}</p>
@foreach($categories->push(null) as $category)
<form method="post" enctype="multipart/form-data" action="{{ $category ? route('assets.category.update',$category) : route('assets.category.create') }}" class="divider">
@php($formKey = $category?->key ?? '__new')
@php($retry = old('_asset_key') === $formKey)
<input type="hidden" name="_asset_key" value="{{ $formKey }}">
@csrf @if($category) @method('patch') @endif
<h2>{{ $category?->key ?? $t('assets.new') }}</h2>
@if($category)<img src="{{ route('assets.marker',$category) }}" width="40" height="40" alt="{{ $category->name_en }}">@else<label>{{ $t('assets.key') }}<input name="key" value="{{ $retry ? old('key') : '' }}" required pattern="[a-z][a-z0-9-]{0,59}" maxlength="60"></label>@endif
<div class="grid2"><label>English<input name="name_en" value="{{ $retry ? old('name_en') : $category?->name_en }}" required maxlength="200"></label><label>简体中文<input name="name_zh" value="{{ $retry ? old('name_zh') : $category?->name_zh }}" required maxlength="200"></label></div>
<label>{{ $t('assets.color') }}<input type="color" name="color" value="{{ $retry ? old('color') : ($category?->color ?? '#24594c') }}"></label>
<label>{{ $t('assets.svg') }}<input type="file" name="svg" accept=".svg,image/svg+xml"></label>
@if($category)<label><input type="checkbox" name="reset" value="1"> {{ $t('assets.reset') }}</label>@endif
<button class="primary">{{ $t('content.save') }}</button></form>
@endforeach
<h2>{{ $t('assets.photos') }}</h2><p>{{ $t('assets.photo-help') }}</p>
@foreach($photos as $photo)<figure><img src="{{ route('assets.photo',$photo->id) }}" alt="{{ $locale==='en' ? $photo->alt_en : $photo->alt_zh }}" style="max-width:100%;max-height:220px"><figcaption>{{ $photo->credit }}</figcaption></figure><form method="post" action="{{ route('assets.photo.remove',$photo->id) }}">@csrf @method('delete')<button>{{ $t('assets.remove') }}</button></form>@endforeach
<form method="post" enctype="multipart/form-data" action="{{ route('assets.photo.upload') }}" class="divider">@csrf
<label>{{ $t('assets.photos') }}<input type="file" name="image" accept="image/jpeg,image/png,image/webp" required></label>
<div class="grid2"><label>{{ $t('assets.alt') }} — English<textarea name="alt_en" maxlength="500" required>{{ old('alt_en') }}</textarea></label><label>{{ $t('assets.alt') }} — 简体中文<textarea name="alt_zh" maxlength="500" required>{{ old('alt_zh') }}</textarea></label></div>
<label>{{ $t('profile.credit') }}<input name="credit" maxlength="200" value="{{ old('credit') }}" required></label>
<label><input type="checkbox" name="permission" value="1" required> {{ $t('assets.permission') }}</label><button class="primary">{{ $t('assets.upload') }}</button></form>
@endsection
