@php
    $storeName = \App\Models\Setting::get('store_name', config('app.name'));
    $logoMediaId = \App\Models\Setting::get('logo_media_id');
    $logoMedia = $logoMediaId ? \App\Models\Media::find($logoMediaId) : null;
    $faviconMediaId = \App\Models\Setting::get('favicon_media_id');
    $faviconMedia = $faviconMediaId ? \App\Models\Media::find($faviconMediaId) : null;
    $storefrontMetaDescription = \App\Models\Setting::get('storefront_meta_description');

    $defaultTitle = trim($__env->yieldContent('title', $storeName));
    $seoTitle = trim($__env->yieldContent('seo_title', $defaultTitle));
    $seoDescriptionDefault = $storefrontMetaDescription ?: "Belanja produk pilihan di {$storeName}.";
    $seoDescription = trim($__env->yieldContent('seo_description', $seoDescriptionDefault));
    $seoImage = trim($__env->yieldContent('seo_image', $logoMedia?->url ?? ($faviconMedia?->url ?? '')));
    $seoImageAbsolute = $seoImage !== ''
        ? (\Illuminate\Support\Str::startsWith($seoImage, ['http://', 'https://']) ? $seoImage : url($seoImage))
        : '';
    $seoUrl = trim($__env->yieldContent('seo_url', url()->current()));
    $seoType = trim($__env->yieldContent('seo_type', 'website'));
@endphp

<meta name="description" content="{{ $seoDescription }}">
<link rel="canonical" href="{{ $seoUrl }}">

<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:site_name" content="{{ $storeName }}">
@if($seoImageAbsolute)
    <meta property="og:image" content="{{ $seoImageAbsolute }}">
@endif

<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:card" content="{{ $seoImageAbsolute ? 'summary_large_image' : 'summary' }}">
@if($seoImageAbsolute)
    <meta name="twitter:image" content="{{ $seoImageAbsolute }}">
@endif
