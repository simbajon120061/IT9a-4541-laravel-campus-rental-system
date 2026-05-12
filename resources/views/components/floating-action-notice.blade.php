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
    {{ $attributes->merge(['class' => 'fixed left-1/2 top-1/2 z-50 w-[calc(100vw-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl border px-5 py-4 text-center text-sm font-semibold shadow-2xl '.$toneClasses]) }}
>
    {{ $message }}
</div>
