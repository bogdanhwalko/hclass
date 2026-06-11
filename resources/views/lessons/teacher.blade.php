<x-app-shell title="Уроки">
    <div x-data="{ showForm: false }" class="space-y-6">

        <div class="flex items-center justify-between">
            <p class="text-sm text-slate-500">Створюйте уроки для учнів та керуйте ними. До уроку можна під'єднати інтерактивну дошку.</p>
            <button @click="showForm = !showForm" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">+ Новий урок</button>
        </div>

        {{-- Create form --}}
        <div x-show="showForm" x-cloak class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('teacher.lessons.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-600">Тема уроку</label>
                    <input name="title" value="{{ old('title') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                    @error('title') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Учень</label>
                    <select name="student_id" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="">— без призначення —</option>
                        @foreach ($students as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Предмет</label>
                    <select name="subject_id" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="">—</option>
                        @foreach ($subjects as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Дата та час</label>
                    <input type="datetime-local" name="scheduled_at" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Тривалість (хв)</label>
                    <input type="number" name="duration_min" value="45" min="5" max="240" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-600">Опис / план</label>
                    <textarea name="description" rows="2" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">{{ old('description') }}</textarea>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600 sm:col-span-2">
                    <input type="checkbox" name="with_board" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                    Створити інтерактивну дошку для цього уроку
                </label>
                <div class="sm:col-span-2">
                    <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Зберегти</button>
                </div>
            </form>
        </div>

        {{-- Lessons list --}}
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
                                {{ $lesson->student?->name ?? 'Без учня' }}
                                @if ($lesson->subject) · {{ $lesson->subject->name }} @endif
                                @if ($lesson->scheduled_at) · {{ $lesson->scheduled_at->format('d.m.Y H:i') }} @endif
                                · {{ $lesson->duration_min }} хв
                            </p>
                            @if ($lesson->description)
                                <p class="mt-2 text-sm text-slate-600">{{ $lesson->description }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if ($lesson->board)
                                <a href="{{ route('board.show', $lesson->board) }}" target="_blank"
                                   class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">🖊 Відкрити дошку</a>
                            @else
                                <form method="POST" action="{{ route('teacher.lessons.board', $lesson) }}">
                                    @csrf
                                    <button class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">+ Дошка</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('teacher.lessons.update', $lesson) }}" class="flex items-center gap-1">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="rounded-xl border-slate-200 py-1.5 text-xs focus:border-indigo-400 focus:ring-indigo-400">
                                    <option value="planned" @selected($lesson->status==='planned')>Заплановано</option>
                                    <option value="done" @selected($lesson->status==='done')>Проведено</option>
                                    <option value="cancelled" @selected($lesson->status==='cancelled')>Скасовано</option>
                                </select>
                            </form>

                            <form method="POST" action="{{ route('teacher.lessons.destroy', $lesson) }}" onsubmit="return confirm('Видалити урок?')">
                                @csrf @method('DELETE')
                                <button class="rounded-xl px-3 py-2 text-xs font-medium text-rose-500 hover:bg-rose-50">Видалити</button>
                            </form>
                        </div>
                    </div>

                    @if ($lesson->board)
                        <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3 text-xs text-slate-400">
                            <span>Посилання для учня:</span>
                            <input readonly value="{{ route('board.show', $lesson->board) }}"
                                   onclick="this.select()"
                                   class="flex-1 rounded-lg border-slate-200 bg-slate-50 px-2 py-1 text-xs text-slate-600">
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-400">
                    Уроків ще немає. Створіть перший урок кнопкою «Новий урок».
                </div>
            @endforelse
        </div>
    </div>
</x-app-shell>
