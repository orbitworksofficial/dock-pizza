{{--
    Resolved SEO head. Every value arrives already resolved (database, then
    config/seo.php), so nothing here can render empty.
--}}
<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
@if($seo['keywords'])
    <meta name="keywords" content="{{ $seo['keywords'] }}">
@endif
<meta name="robots" content="{{ $seo['robots'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ config('seo.organization.name') }}">
<meta property="og:type" content="{{ $seo['og_type'] }}">
<meta property="og:title" content="{{ $seo['og_title'] }}">
<meta property="og:description" content="{{ $seo['og_description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
@if($seo['og_image'])
    <meta property="og:image" content="{{ $seo['og_image'] }}">
@endif

{{-- Twitter --}}
<meta name="twitter:card" content="{{ $seo['twitter_card'] }}">
<meta name="twitter:title" content="{{ $seo['twitter_title'] }}">
<meta name="twitter:description" content="{{ $seo['twitter_description'] }}">
@if($seo['twitter_image'])
    <meta name="twitter:image" content="{{ $seo['twitter_image'] }}">
@endif

{{--
    One @graph per page. Re-encoded from decoded structures, so malformed
    JSON can never reach the page as raw text.
--}}
@isset($seoGraph)
    <script type="application/ld+json">{!! json_encode($seoGraph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endisset
