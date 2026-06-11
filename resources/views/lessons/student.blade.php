<x-app-shell title="Мої уроки">
    <div class="space-y-4">
        @forelse ($lessons as $lesson)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-slate-900">{{ $lesson->title }}</h3>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $lesson->statusColor() }}">{{ $lesson->statusLabel() }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            Вчитель: {{ $lesson->teacher?->name ?? '—' }}
                            @if ($lesson->subject) · {{ $lesson->subject->name }} @endif
                            @if ($lesson->scheduled_at) · {{ $lesson->scheduled_at->format('d.m.Y H:i') }} @endif
                        </p>
                        @if ($lesson->description)
                            <p class="mt-2 text-sm text-slate-600">{{ $lesson->description }}</p>
                        @endif
                    </div>

                    @if ($lesson->board && $lesson->status !== 'cancelled')
                        <a href="{{ route('board.show', $lesson->board) }}" target="_blank"
                           class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            🖊 Приєднатися до дошки
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-400">
                Вам ще не призначено уроків.
            </div>
        @endforelse
    </div>
</x-app-shell>
