<x-app-shell title="Мої групи">
    <div class="space-y-6">
        @forelse ($classes as $class)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-slate-900">{{ $class->name }}</h3>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-600">{{ $class->students_count }} учнів</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">Напрями: {{ $class->subjects->pluck('name')->join(', ') ?: '—' }}</p>

                <div class="mt-4 overflow-hidden rounded-xl border border-slate-100">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-400">
                            <tr><th class="px-4 py-2">Учень</th><th class="px-4 py-2">Email</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($class->students as $student)
                                <tr>
                                    <td class="px-4 py-2 font-medium text-slate-700">{{ $student->name }}</td>
                                    <td class="px-4 py-2 text-slate-400">{{ $student->email }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-4 py-3 text-slate-400">Учнів немає.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400">Вам ще не призначено груп.</p>
        @endforelse
    </div>
</x-app-shell>
