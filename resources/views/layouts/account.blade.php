<!doctype html><html lang="{{ $locale }}"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $t($screen) }} — {{ $t('brand') }}</title>
<link rel="stylesheet" href="{{ asset('design/styles.css') }}"><link rel="stylesheet" href="{{ asset('design/visual-design.css') }}">
<link rel="stylesheet" href="/vendor/leaflet/leaflet.css"><link rel="stylesheet" href="/design/atlas-map.css">
</head><body>
<a class="skip" href="#main">{{ $t('skip') }}</a>
<header class="header"><a class="brand" href="{{ route('home') }}">{{ $t('brand') }}</a>
<div class="header-right"><nav aria-label="{{ $t('language.label') }}">
<a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" lang="en" @if($locale==='en') aria-current="page" @endif>English</a>
<a href="{{ request()->fullUrlWithQuery(['lang' => 'zh-Hans']) }}" lang="zh-Hans" @if($locale==='zh-Hans') aria-current="page" @endif>简体中文</a></nav>
<nav aria-label="{{ $t('profile') }}">
<a href="{{ route('atlas') }}">{{ $t('archive') }}</a>
@auth<a href="{{ route('observations.mine') }}">{{ $t('archive.mine') }}</a>@endauth
@guest <a href="{{ route('login') }}">{{ $t('login') }}</a><a href="{{ route('register') }}">{{ $t('register') }}</a>
@else <a href="{{ route('profile') }}">{{ $t('profile') }}</a>
@can('admin') <a href="{{ route('admin') }}">{{ $t('admin') }}</a> @endcan
@can('moderate') <a href="{{ route('moderation') }}">{{ $t('moderation') }}</a> @endcan
<form method="post" action="{{ route('logout') }}">@csrf<button class="nav-signout">{{ $t('logout') }}</button></form>@endguest
</nav></div></header>
@php($workspace = in_array($screen, ['admin','users','moderation','content.title','translations.title','assets.title']))
@php($archive = str_starts_with($screen, 'archive'))
<main id="main" class="wrap {{ ($workspace || $archive) ? 'wrap-wide' : 'auth-layout' }}" tabindex="-1"><section class="{{ ($workspace || $archive) ? 'page-heading' : 'auth-intro' }}"><p class="eyebrow">{{ $t('brand') }}</p><h1>{{ $t($heading ?? $screen) }}</h1>@unless($workspace || $archive)<p class="lead">{{ $t('account.intro') }}</p>@endunless</section>
@if($workspace)<div class="admin-layout"><nav class="admin-nav" aria-label="{{ $t('admin') }}">@can('admin')<a href="{{ route('admin') }}" @if($screen==='admin') aria-current="page" @endif>{{ $t('admin') }}</a><a href="{{ route('admin.users') }}" @if($screen==='users') aria-current="page" @endif>{{ $t('users') }}</a><a href="{{ route('admin.content') }}" @if($screen==='content.title') aria-current="page" @endif>{{ $t('content.title') }}</a><a href="{{ route('admin.translations') }}" @if($screen==='translations.title') aria-current="page" @endif>{{ $t('translations.title') }}</a><a href="{{ route('assets.admin') }}" @if($screen==='assets.title') aria-current="page" @endif>{{ $t('assets.title') }}</a>@endcan<a href="{{ route('moderation') }}" @if($screen==='moderation') aria-current="page" @endif>{{ $t('moderation') }}</a></nav>@endif
<section class="{{ ($workspace || $archive) ? 'admin-content' : 'form-panel auth-panel' }}">
@if(session('status'))<p role="status" class="selected-date">{{ session('status') }}</p>@endif
@if($errors->any())<div role="alert" class="error"><p>{{ $t('error.heading') }}</p><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</section>@if($workspace)</div>@endif</main>
<script data-account-script>
// Preserve unfinished account fields when switching the interface language.
document.querySelectorAll('a[lang]').forEach(link => link.addEventListener('click', async event => {
    event.preventDefault();
    const response = await fetch(link.href);
    if (!response.ok) return;
    const next = new DOMParser().parseFromString(await response.text(), 'text/html');
    const controls = 'input:not([name="_token"]),textarea,select';
    const values = [...document.querySelectorAll(controls)].map(el => ({name:el.name,value:el.value,checked:el.checked,files:el.files}));
    const expanded = [...document.querySelectorAll('details')].map(el=>el.open);
    window.destroyAtlasMap?.();
    document.querySelector('header').replaceWith(next.querySelector('header'));
    document.querySelector('main').replaceWith(next.querySelector('main'));
    document.querySelectorAll(controls).forEach((input,index) => { const saved=values[index]; if(saved && saved.name===input.name){ if(input.type==='file'){if(saved.files) input.files=saved.files;}else input.value=saved.value; if(input.type==='checkbox'||input.type==='radio') input.checked=saved.checked; } });
    document.querySelectorAll('details').forEach((el,index)=>el.open=expanded[index]||false);
    document.documentElement.lang = next.documentElement.lang;
    document.title = next.title;
    history.replaceState(null, '', link.href);
    const script = document.createElement('script'); script.textContent = next.querySelector('script[data-account-script]').textContent; document.body.append(script); script.remove();
    window.dispatchEvent(new Event('atlas-page-replaced'));
}));
</script>
<script src="/vendor/leaflet/leaflet.js"></script><script src="/design/atlas-map.js"></script>
</body></html>
