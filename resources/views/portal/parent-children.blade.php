<x-app-shell title="Мої діти">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        @forelse ($children as $child)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-lg font-bold text-emerald-700">{{ $child->initials() }}</div>
                    <div>
                        <p class="text-lg font-semibold text-slate-900">{{ $child->name }}</p>
                        <p class="text-sm text-slate-400">{{ $child->email }}</p>
                    </div>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse ($child->classes as $class)
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm font-medium text-slate-700">Клас: <span class="font-bold">{{ $class->name }}</span></p>
                            <p class="mt-1 text-xs text-slate-500">Класний керівник: {{ $class->homeroomTeacher?->name ?? '—' }}</p>
                            <p class="mt-2 text-xs text-slate-500">Предмети: {{ $class->subjects->pluck('name')->join(', ') ?: '—' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Дитину ще не зараховано до класу.</p>
                    @endforelse
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400">До вашого акаунту ще не прив'язано дітей.</p>
        @endforelse
    </div>
</x-app-shell>
