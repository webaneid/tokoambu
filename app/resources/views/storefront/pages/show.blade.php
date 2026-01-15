@extends('storefront.layouts.app-mobile')

@section('title', $page->title . ' - ' . \App\Models\Setting::get('store_name', config('app.name')))

@push('styles')
<style>
/* Hide header and bottom nav for page detail */
.app-header,
.search-bar,
.bottom-nav {
    display: none !important;
}

.storefront-main {
    padding-bottom: 0;
}
</style>
@endpush

@section('content')
<div class="page-detail">
    {{-- Page Header --}}
    <div class="page-detail-header">
        <a href="{{ route('shop.index') }}" class="page-detail-header__btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M7.28 7.72a.75.75 0 0 1 0 1.06l-2.47 2.47H21a.75.75 0 0 1 0 1.5H4.81l2.47 2.47a.75.75 0 1 1-1.06 1.06l-3.75-3.75a.75.75 0 0 1 0-1.06l3.75-3.75a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
        </a>

        <div class="page-detail-header__actions">
            <button type="button" class="page-detail-header__btn" id="shareBtn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M15.75 4.5a3 3 0 1 1 .825 2.066l-8.421 4.679a3.002 3.002 0 0 1 0 1.51l8.421 4.679a3 3 0 1 1-.729 1.31l-8.421-4.678a3 3 0 1 1 0-4.132l8.421-4.679a3 3 0 0 1-.096-.755Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Featured Image --}}
    @if ($page->featuredImage)
    <div class="page-image">
        <div class="page-image__container">
            <img src="{{ $page->featuredImage->url }}" alt="{{ $page->title }}">
        </div>
    </div>
    @endif

    {{-- Page Content --}}
    <div class="page-content">
        <h1 class="page-content__title">{{ $page->title }}</h1>

        @if ($page->content)
        <div class="page-content__body">
            {!! $page->content !!}
        </div>
        @else
        <p class="page-content__empty">Belum ada konten untuk halaman ini.</p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Share button handler
document.getElementById('shareBtn').addEventListener('click', function() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $page->title }}',
            text: '{{ Str::limit(strip_tags($page->content), 100) }}',
            url: window.location.href
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link berhasil disalin!');
        });
    }
});
</script>
@endpush
