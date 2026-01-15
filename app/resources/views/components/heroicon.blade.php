@php
    $attrs = $attributes->merge(['class' => $class])->toHtml();
    $svgTag = $svg;
    if ($svgTag !== '') {
        $svgTag = preg_replace('/^<svg\b([^>]*)>/', '<svg$1 ' . $attrs . '>', $svgTag, 1);
    }
@endphp
{!! $svgTag !!}
