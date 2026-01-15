@php
    $normalizeHex = function (?string $hex, string $fallback) {
        if (!$hex) {
            return $fallback;
        }
        $hex = trim($hex);
        if (preg_match('/^#?[A-Fa-f0-9]{6}$/', $hex) !== 1) {
            return $fallback;
        }
        return strtoupper(str_starts_with($hex, '#') ? $hex : ('#' . $hex));
    };

    $hexToRgb = function (string $hex) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "{$r}, {$g}, {$b}";
    };

    $mixWithWhite = function (string $hex, float $ratio = 0.92) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $r = (int) round($r * (1 - $ratio) + 255 * $ratio);
        $g = (int) round($g * (1 - $ratio) + 255 * $ratio);
        $b = (int) round($b * (1 - $ratio) + 255 * $ratio);
        return sprintf('#%02X%02X%02X', $r, $g, $b);
    };

    $primary = $normalizeHex(\App\Models\Setting::get('color_primary'), '#F17B0D');
    $primaryHover = $normalizeHex(\App\Models\Setting::get('color_primary_hover'), '#DD5700');
    $secondary = $normalizeHex(\App\Models\Setting::get('color_secondary'), '#0D36AA');
    $alternative = $normalizeHex(\App\Models\Setting::get('color_alternative'), '#D00086');
    $dark = $normalizeHex(\App\Models\Setting::get('color_dark'), '#1F2937');
    $lightGray = $normalizeHex(\App\Models\Setting::get('color_light_gray'), '#F9FAFB');
    $primaryLight = $mixWithWhite($primary, 0.92);
@endphp

<style>
    :root {
        --ane-color-primary: {{ $primary }};
        --ane-color-primary-hover: {{ $primaryHover }};
        --ane-color-primary-light: {{ $primaryLight }};
        --ane-color-secondary: {{ $secondary }};
        --ane-color-alternative: {{ $alternative }};
        --ane-color-dark: {{ $dark }};
        --ane-color-light-gray: {{ $lightGray }};
        --ane-color-primary-rgb: {{ $hexToRgb($primary) }};
    }
</style>
