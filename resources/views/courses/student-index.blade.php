<x-app-shell title="Курси">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Каталог курсів</h2>
        <p class="mt-1 text-slate-500">Обирай курс і починай навчання у власному темпі.</p>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($courses as $course)
            <a href="{{ route('student.courses.show', $course) }}"
               class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                <div class="relative flex h-32 items-center justify-center bg-gradient-to-br {{ $course->gradient() }} text-6xl">
                    {{ $course->emoji }}
                    @if ($enrolledIds->contains($course->id))
                        <span class="absolute right-3 top-3 rounded-full bg-white/90 px-2.5 py-0.5 text-xs font-semibold text-emerald-600">Розпочато</span>
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-slate-900 group-hover:text-indigo-600">{{ $course->title }}</h3>
                    <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $course->summary ?: 'Без опису' }}</p>
                    <div class="mt-4 flex items-center justify-between text-xs text-slate-400">
                        <span>👤 {{ $course->teacher->name }}</span>
                        <span>{{ $course->lessons_count }} модулів</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <p class="text-4xl">🔍</p>
                <p class="mt-3 font-semibold text-slate-700">Курсів поки немає</p>
                <p class="text-sm text-slate-400">Зачекай, доки вчителі опублікують курси.</p>
            </div>
        @endforelse
    </div>
</x-app-shell>
