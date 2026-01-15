@props([
    'elementPrefix' => 'ai',
    'showMediaSelector' => true,
    'mediaId' => null,
    'mediaName' => null,
    'containerClass' => '',
    'previewContainerId' => null,
])

@php
    $aiIntegration = App\Models\AiIntegration::active('gemini');
    $aiEnabled = (bool) $aiIntegration;

    if (!$aiEnabled) {
        return;
    }

    // Generate unique IDs based on prefix
    $ids = [
        'mediaIdInput' => $elementPrefix . 'MediaId',
        'selectedMediaName' => $elementPrefix . 'SelectedMediaName',
        'clearSelection' => $elementPrefix . 'ClearSelection',
        'backgroundColor' => $elementPrefix . 'BackgroundColor',
        'useSolid' => $elementPrefix . 'UseSolid',
        'featuresContainer' => $elementPrefix . 'FeaturesContainer',
        'quickPresetsContainer' => $elementPrefix . 'QuickPresets',
        'enhanceTrigger' => $elementPrefix . 'EnhanceTrigger',
        'status' => $elementPrefix . 'Status',
    ];

    $defaults = [
        'background_color' => $aiIntegration->default_bg_color ?? '#FFFFFF',
        'use_solid_background' => $aiIntegration->use_solid_background ?? true,
    ];
@endphp

<div class="ai-studio-container {{ $containerClass }}">
    {{-- Hidden Input for Media ID --}}
    <input type="hidden" id="{{ $ids['mediaIdInput'] }}" value="{{ $mediaId ?? '' }}">

    @if($showMediaSelector)
    {{-- Selected Media Display --}}
    <div id="{{ $ids['selectedMediaName'] }}" class="hidden mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
        <span class="text-sm text-gray-700"></span>
        <button type="button" id="{{ $ids['clearSelection'] }}" class="text-sm text-red-600 hover:text-red-800 font-medium">
            Batalkan Pilihan
        </button>
    </div>
    @endif

    {{-- Background Settings --}}
    <div class="space-y-4 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Background Color</label>
            <div class="flex items-center gap-3">
                <input
                    type="color"
                    id="{{ $ids['backgroundColor'] }}"
                    value="{{ $defaults['background_color'] }}"
                    class="w-16 h-10 rounded border border-gray-300 cursor-pointer"
                >
                <input
                    type="text"
                    value="{{ $defaults['background_color'] }}"
                    readonly
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm"
                    id="{{ $ids['backgroundColor'] }}Display"
                >
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input
                type="checkbox"
                id="{{ $ids['useSolid'] }}"
                {{ $defaults['use_solid_background'] ? 'checked' : '' }}
                class="rounded border-gray-300 text-primary focus:ring-primary"
            >
            <label for="{{ $ids['useSolid'] }}" class="text-sm text-gray-700">
                Use Solid Background (uncheck for transparent/smart background)
            </label>
        </div>
    </div>

    {{-- Quick Presets --}}
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-3">Quick Presets</label>
        <div id="{{ $ids['quickPresetsContainer'] }}" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            <!-- Presets will be loaded by JS -->
        </div>
    </div>

    {{-- Individual Features --}}
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-3">Individual Features</label>
        <div id="{{ $ids['featuresContainer'] }}" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <!-- Features will be loaded by JS -->
        </div>
    </div>

    {{-- Status Message --}}
    <div id="{{ $ids['status'] }}" class="text-sm text-gray-600 mb-4 min-h-[20px]"></div>

    {{-- Enhance Button --}}
    <button
        type="button"
        id="{{ $ids['enhanceTrigger'] }}"
        class="w-full bg-primary hover:bg-primary-hover text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M12 1v6m0 6v6M16.24 7.76l-4.24 4.24m0 0L7.76 16.24M1 12h6m6 0h6"></path>
        </svg>
        Jalankan Ambu Magic
    </button>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const {{ $elementPrefix }}Config = {
            routes: {
                features: '{{ route('ai.features') }}',
                enhance: '{{ route('ai.enhance') }}',
                job: '{{ url('ai/jobs') }}',
            },
            csrfToken: '{{ csrf_token() }}',
            maxPollAttempts: 40,
            pollIntervalMs: 2000,
            elementIds: {
                mediaIdInput: '{{ $ids['mediaIdInput'] }}',
                @if($showMediaSelector)
                selectedMediaName: '{{ $ids['selectedMediaName'] }}',
                clearSelection: '{{ $ids['clearSelection'] }}',
                @endif
                backgroundColor: '{{ $ids['backgroundColor'] }}',
                useSolid: '{{ $ids['useSolid'] }}',
                featuresContainer: '{{ $ids['featuresContainer'] }}',
                quickPresetsContainer: '{{ $ids['quickPresetsContainer'] }}',
                enhanceTrigger: '{{ $ids['enhanceTrigger'] }}',
                status: '{{ $ids['status'] }}',
            }
        };

        // Sync color input with display
        const colorInput = document.getElementById('{{ $ids['backgroundColor'] }}');
        const colorDisplay = document.getElementById('{{ $ids['backgroundColor'] }}Display');
        if (colorInput && colorDisplay) {
            colorInput.addEventListener('input', (e) => {
                colorDisplay.value = e.target.value;
            });
        }

        // Initialize AI Studio
        let {{ $elementPrefix }}Retries = 0;
        const max{{ ucfirst($elementPrefix) }}Retries = 5;

        function tryInit{{ ucfirst($elementPrefix) }}Studio() {
            if (typeof window.AiStudioManager !== 'undefined') {
                window.{{ $elementPrefix }}StudioInstance = new window.AiStudioManager({{ $elementPrefix }}Config);

                @if($mediaId)
                // Auto-select media if provided
                window.{{ $elementPrefix }}StudioInstance.selectMedia({{ $mediaId }}, '{{ $mediaName ?? '' }}');
                @endif
            } else if ({{ $elementPrefix }}Retries < max{{ ucfirst($elementPrefix) }}Retries) {
                {{ $elementPrefix }}Retries++;
                setTimeout(tryInit{{ ucfirst($elementPrefix) }}Studio, 200);
            }
        }

        tryInit{{ ucfirst($elementPrefix) }}Studio();
    });
</script>
@endpush
