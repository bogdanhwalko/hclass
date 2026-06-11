<x-app-shell title="Панель вчителя">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Вітаємо, {{ auth()->user()->name }} 👋</h2>
        <p class="mt-1 text-slate-500">Ваші групи та напрями навчання.</p>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <x-stat-card label="Куруєте груп" :value="$stats['homeroom']" accent="indigo" />
        <x-stat-card label="Напрямів" :value="$stats['subjects']" accent="emerald" />
        <x-stat-card label="Учнів" :value="$stats['students']" accent="amber" />
    </div>

    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="{{ route('teacher.courses') }}" class="rounded-2xl border border-slate-200 bg-gradient-to-br from-violet-500 to-violet-600 p-5 text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
            <span class="text-2xl">📚</span>
            <p class="mt-2 text-lg font-bold">Мої курси</p>
            <p class="text-sm text-violet-100">Створюйте та наповнюйте курси</p>
        </a>
        <a href="{{ route('teacher.lessons') }}" class="rounded-2xl border border-slate-200 bg-gradient-to-br from-indigo-500 to-indigo-600 p-5 text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
            <span class="text-2xl">🎥</span>
            <p class="mt-2 text-lg font-bold">Уроки</p>
            <p class="text-sm text-indigo-100">Плануйте уроки та дошку</p>
        </a>
        <a href="{{ route('teacher.classes') }}" class="rounded-2xl border border-slate-200 bg-gradient-to-br from-emerald-500 to-emerald-600 p-5 text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
            <span class="text-2xl">👥</span>
            <p class="mt-2 text-lg font-bold">Мої групи</p>
            <p class="text-sm text-emerald-100">Списки учнів за напрямами</p>
        </a>
    </div>

    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 font-semibold text-slate-800">Мої групи</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($classes as $class)
                <div class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-bold text-slate-900">{{ $class->name }}</span>
                        <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-600">{{ $class->students_count }} учнів</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">
                        Напрями: {{ $class->subjects->pluck('name')->join(', ') ?: '—' }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-slate-400">Вам ще не призначено груп.</p>
            @endforelse
        </div>
    </div>
</x-app-shell>
