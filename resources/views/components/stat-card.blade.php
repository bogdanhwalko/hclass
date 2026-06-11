@props(['label', 'value', 'accent' => 'indigo'])

@php
    $accents = [
        'indigo' => 'from-indigo-500 to-indigo-600',
        'emerald' => 'from-emerald-500 to-emerald-600',
        'amber' => 'from-amber-500 to-amber-600',
        'rose' => 'from-rose-500 to-rose-600',
    ];
    $grad = $accents[$accent] ?? $accents['indigo'];
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
        <div class="h-9 w-9 rounded-xl bg-gradient-to-br {{ $grad }} opacity-90"></div>
    </div>
    <p class="mt-3 text-3xl font-bold text-slate-900">{{ $value }}</p>
</div>
