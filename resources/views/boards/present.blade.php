<!DOCTYPE html>
<html lang="uk" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $group->name }} · Презентація · HClass</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full overflow-hidden bg-slate-900 font-sans text-white antialiased">

@php
    $slidesJson = $slides->map(fn ($b) => [
        'title' => $b->title,
        'url'   => route('board.show', $b).'?view=1',
    ])->values();
@endphp

<div class="flex h-full flex-col"
     x-data="presenter({ slides: {{ $slidesJson->toJson() }} })"
     @keydown.window="onKey($event)">

    {{-- ===== Top bar ===== --}}
    <header class="z-10 flex items-center gap-3 border-b border-white/10 px-4 py-2.5">
        <a href="{{ route('teacher.boards') }}" class="flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-sm text-white/70 hover:bg-white/10 hover:text-white">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Вийти
        </a>
        <div class="mx-1 h-5 w-px bg-white/15"></div>
        <span class="h-3 w-3 shrink-0 rounded-full bg-gradient-to-br {{ $group->gradient() }}"></span>
        <h1 class="truncate text-sm font-semibold">{{ $group->name }}</h1>
        <span class="text-xs text-white/50" x-text="slides.length ? `Слайд ${index+1} з ${slides.length}` : ''"></span>
        <span class="ml-auto truncate text-sm text-white/70" x-text="slides[index]?.title || ''"></span>
        <button @click="toggleFullscreen()" class="rounded-lg px-2 py-1.5 text-sm text-white/70 hover:bg-white/10 hover:text-white" title="На весь екран">⛶</button>
    </header>

    {{-- ===== Slide stage ===== --}}
    <div class="relative flex-1 bg-slate-800">
        @if ($slides->isEmpty())
            <div class="flex h-full flex-col items-center justify-center text-center">
                <p class="text-5xl">🗂</p>
                <p class="mt-4 text-lg font-semibold">У цій групі ще немає дошок</p>
                <p class="mt-1 text-sm text-white/50">Додайте дошки до групи «{{ $group->name }}», щоб провести презентацію.</p>
            </div>
        @else
            {{-- Each slide is the board opened in view mode --}}
            <template x-for="(s, i) in slides" :key="i">
                <iframe x-show="i === index"
                        :src="i === index || i === loaded ? s.url : ''"
                        class="absolute inset-0 h-full w-full border-0"
                        loading="lazy"></iframe>
            </template>

            {{-- Prev / Next arrows --}}
            <button x-show="index > 0" @click="prev()"
                    class="absolute left-4 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-slate-900/70 text-2xl text-white shadow-lg backdrop-blur hover:bg-slate-900">‹</button>
            <button x-show="index < slides.length - 1" @click="next()"
                    class="absolute right-4 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-slate-900/70 text-2xl text-white shadow-lg backdrop-blur hover:bg-slate-900">›</button>
        @endif
    </div>

    {{-- ===== Bottom thumbnail strip ===== --}}
    @if ($slides->isNotEmpty())
        <footer class="z-10 flex items-center gap-2 overflow-x-auto border-t border-white/10 px-4 py-2">
            <template x-for="(s, i) in slides" :key="i">
                <button @click="go(i)"
                        class="flex h-12 shrink-0 items-center gap-2 rounded-lg border px-3 text-xs transition"
                        :class="i === index ? 'border-indigo-400 bg-indigo-500/20 text-white' : 'border-white/10 text-white/60 hover:bg-white/5'">
                    <span class="flex h-6 w-6 items-center justify-center rounded bg-white/10 text-[10px] font-bold" x-text="i + 1"></span>
                    <span class="max-w-[140px] truncate" x-text="s.title"></span>
                </button>
            </template>
        </footer>
    @endif
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('presenter', (cfg) => ({
        slides: cfg.slides || [],
        index: 0,
        loaded: -1,            // index of the neighbour we've prefetched
        go(i) { this.index = Math.max(0, Math.min(this.slides.length - 1, i)); this.prefetch(); },
        next() { this.go(this.index + 1); },
        prev() { this.go(this.index - 1); },
        prefetch() { this.loaded = this.index + 1; }, // warm the next slide's iframe
        onKey(e) {
            if (e.key === 'ArrowRight' || e.key === 'PageDown' || e.key === ' ') { e.preventDefault(); this.next(); }
            else if (e.key === 'ArrowLeft' || e.key === 'PageUp') { e.preventDefault(); this.prev(); }
            else if (e.key === 'Home') { this.go(0); }
            else if (e.key === 'End') { this.go(this.slides.length - 1); }
            else if (e.key === 'f') { this.toggleFullscreen(); }
        },
        toggleFullscreen() {
            if (document.fullscreenElement) document.exitFullscreen();
            else document.documentElement.requestFullscreen?.();
        },
    }));
});
</script>
</body>
</html>
