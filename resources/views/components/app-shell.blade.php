@props(['title' => 'Панель'])

@php
    $user = auth()->user();
    $role = $user->role;

    $nav = [['route' => 'dashboard', 'label' => 'Головна', 'icon' => 'home']];
    $nav[] = ['route' => 'calendar', 'label' => 'Календар', 'icon' => 'calendar'];

    if ($user->isAdmin()) {
        $nav[] = ['route' => 'admin.users', 'label' => 'Користувачі', 'icon' => 'users'];
        $nav[] = ['route' => 'admin.classes', 'label' => 'Групи', 'icon' => 'building'];
        $nav[] = ['route' => 'admin.subjects', 'label' => 'Напрями', 'icon' => 'book'];
    }
    if ($user->isTeacher()) {
        $nav[] = ['route' => 'teacher.courses', 'label' => 'Мої курси', 'icon' => 'book'];
        $nav[] = ['route' => 'teacher.boards', 'label' => 'Мої дошки', 'icon' => 'board'];
        $nav[] = ['route' => 'teacher.classes', 'label' => 'Мої групи', 'icon' => 'building'];
        $nav[] = ['route' => 'teacher.lessons', 'label' => 'Уроки', 'icon' => 'play'];
    }
    if ($user->isStudent()) {
        $nav[] = ['route' => 'student.courses', 'label' => 'Курси', 'icon' => 'book'];
        $nav[] = ['route' => 'student.subjects', 'label' => 'Мої напрями', 'icon' => 'book'];
        $nav[] = ['route' => 'student.lessons', 'label' => 'Мої уроки', 'icon' => 'play'];
    }
    if ($user->isParent()) {
        $nav[] = ['route' => 'parent.children', 'label' => 'Мої діти', 'icon' => 'users'];
    }
@endphp

<!DOCTYPE html>
<html lang="uk" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · HClass</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 font-sans text-slate-800 antialiased">
<div x-data="{ open: false }" class="min-h-full">

    {{-- ===================== Sidebar ===================== --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 text-slate-300 transition-transform duration-200 lg:translate-x-0"
           :class="open ? 'translate-x-0' : '-translate-x-full'">
        <div class="flex h-16 items-center gap-2 px-6">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500 font-bold text-white">H</div>
            <span class="text-lg font-bold text-white">HClass</span>
        </div>

        <nav class="mt-4 space-y-1 px-3">
            @foreach ($nav as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                          {{ $active ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30' : 'hover:bg-slate-800 hover:text-white' }}">
                    <x-nav-icon :name="$item['icon']" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="absolute bottom-0 w-full border-t border-slate-800 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-700 text-sm font-semibold text-white">
                    {{ $user->initials() }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                    <span class="inline-block rounded-full px-2 py-0.5 text-[11px] font-medium {{ $role->color() }}">
                        {{ $role->label() }}
                    </span>
                </div>
            </div>
        </div>
    </aside>

    {{-- Mobile overlay --}}
    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

    {{-- ===================== Main ===================== --}}
    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/80 px-4 backdrop-blur lg:px-8">
            <button @click="open = !open" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-lg font-semibold text-slate-800">{{ $title }}</h1>
            <div class="ml-auto flex items-center gap-3">
                <a href="{{ route('profile') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Профіль</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Вийти</button>
                </form>
            </div>
        </header>

        <main class="p-4 lg:p-8">
            @if (session('status'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>
