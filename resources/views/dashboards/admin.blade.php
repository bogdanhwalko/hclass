<x-app-shell title="Панель адміністратора">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Вітаємо, {{ auth()->user()->name }} 👋</h2>
        <p class="mt-1 text-slate-500">Загальний огляд платформи HClass.</p>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Учнів" :value="$stats['students']" accent="emerald" />
        <x-stat-card label="Вчителів" :value="$stats['teachers']" accent="indigo" />
        <x-stat-card label="Батьків" :value="$stats['parents']" accent="amber" />
        <x-stat-card label="Груп" :value="$stats['classes']" accent="rose" />
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Останні користувачі</h3>
                <a href="{{ route('admin.users') }}" class="text-sm font-medium text-indigo-600 hover:underline">Усі →</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @foreach ($recentUsers as $u)
                    <li class="flex items-center gap-3 py-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-600">{{ $u->initials() }}</div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800">{{ $u->name }}</p>
                            <p class="truncate text-xs text-slate-400">{{ $u->email }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $u->role->color() }}">{{ $u->role->label() }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Групи</h3>
                <a href="{{ route('admin.classes') }}" class="text-sm font-medium text-indigo-600 hover:underline">Керувати →</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($classes as $class)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $class->name }}</p>
                            <p class="text-xs text-slate-400">Куратор: {{ $class->homeroomTeacher?->name ?? '—' }}</p>
                        </div>
                        <span class="text-sm text-slate-500">{{ $class->students_count }} учасників</span>
                    </li>
                @empty
                    <li class="py-3 text-sm text-slate-400">Груп ще немає.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-shell>
