@extends('layouts.account')
@php($screen = 'archive.home')
@php($heading = 'home.headline')
@section('content')
<p class="lead" style="white-space:pre-wrap">{{ $t('home.intro') }}</p>
<div class="actions"><a class="button primary" href="{{ route('atlas',['lang'=>$locale]) }}">{{ $t('archive') }}</a><a class="button" href="{{ route('observations.create',['lang'=>$locale]) }}">{{ $t('archive.contribute') }}</a></div>
@foreach(['project','funding','contributors'] as $section)
<section class="divider"><h2>{{ $t('content.home.'.$section) }}</h2><p style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $t('home.'.$section) }}</p></section>
@endforeach
<div class="grid2">@foreach(\Illuminate\Support\Facades\DB::table('homepage_images')->orderBy('id')->get() as $photo)<figure><img src="{{ route('assets.photo',$photo->id) }}" alt="{{ $locale==='en' ? $photo->alt_en : $photo->alt_zh }}" style="width:100%;height:auto" loading="lazy"><figcaption>{{ $photo->credit }}</figcaption></figure>@endforeach</div>
@endsection
