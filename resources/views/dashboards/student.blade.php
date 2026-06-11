<x-app-shell title="Панель учня">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Привіт, {{ auth()->user()->name }} 👋</h2>
        <p class="mt-1 text-slate-500">Твоя група, напрями та курси.</p>
    </div>

    <a href="{{ route('student.courses') }}" class="mb-6 flex items-center justify-between rounded-2xl border border-slate-200 bg-gradient-to-r from-violet-600 to-indigo-600 p-6 text-white shadow-sm transition hover:shadow-lg">
        <div>
            <p class="text-xl font-bold">📚 Переглянути курси</p>
            <p class="text-sm text-indigo-100">Проходь матеріали та складай тести</p>
        </div>
        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
    </a>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 font-semibold text-slate-800">Моя група</h3>
            @forelse ($classes as $class)
                <div class="rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 p-5 text-white">
                    <p class="text-2xl font-bold">{{ $class->name }}</p>
                    <p class="mt-1 text-sm text-indigo-100">Куратор: {{ $class->homeroomTeacher?->name ?? '—' }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-400">Тебе ще не додано до групи.</p>
            @endforelse
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h3 class="mb-4 font-semibold text-slate-800">Мої напрями</h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @forelse ($subjects as $subject)
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $subject->name }}</p>
                            <p class="text-xs text-slate-400">{{ $subject->teacher_name ?? 'Вчитель не призначений' }}</p>
                        </div>
                        <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-medium text-slate-500">{{ $subject->code }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Напрямів ще немає.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-shell>
