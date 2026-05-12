@props([
    'message',
    'tone' => 'success',
])

@php
    $toneClasses = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800 shadow-emerald-100',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-800 shadow-rose-100',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800 shadow-amber-100',
    ][$tone] ?? 'border-emerald-200 bg-emerald-50 text-emerald-800 shadow-emerald-100';
@endphp

<div
    x-data="{ show: true }"
    x-init="setTimeout(() => show = false, 3500)"
    x-show="show"
    x-transition.opacity.duration.200ms
    {{ $attributes->merge(['class' => 'relative z-10 mt-2 rounded-lg border px-4 py-2.5 text-center text-xs font-semibold shadow-lg '.$toneClasses]) }}
>
    {{ $message }}
</div>
