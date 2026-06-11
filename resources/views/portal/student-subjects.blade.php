<x-app-shell title="Мої предмети">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($subjects as $subject)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">{{ $subject->name }}</h3>
                    <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-medium text-slate-500">{{ $subject->code }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-500">{{ $subject->description ?: 'Опис відсутній.' }}</p>
                <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-3 text-sm text-slate-600">
                    <span class="text-slate-400">Вчитель:</span>
                    <span class="font-medium">{{ $subject->teacher_name ?? 'не призначений' }}</span>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400">Предметів ще немає.</p>
        @endforelse
    </div>
</x-app-shell>
