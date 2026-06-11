@props(['name'])

@php
    $paths = [
        'home' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10',
        'users' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-3-1.5',
        'building' => 'M3 21h18M9 8h1m-1 4h1m4-4h1m-1 4h1M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16',
        'book' => 'M12 6.5C10.5 5 7.5 4.5 5 5v13c2.5-.5 5.5 0 7 1.5 1.5-1.5 4.5-2 7-1.5V5c-2.5-.5-5.5 0-7 1.5zM12 6.5V20',
        'play' => 'M15.5 12l-7-4.5v9l7-4.5zM21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'board' => 'M4 5h16v11H4zM2 5h20M9 20h6m-3-4v4',
        'calendar' => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z',
    ];
    $d = $paths[$name] ?? $paths['home'];
@endphp

<svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}" />
</svg>
