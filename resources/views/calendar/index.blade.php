<x-app-shell title="Календар">
    @php
        $weekdays = ['Пн','Вт','Ср','Чт','Пт','Сб','Нд'];
        $months = ['','Січень','Лютий','Березень','Квітень','Травень','Червень','Липень','Серпень','Вересень','Жовтень','Листопад','Грудень'];
    @endphp

    <div x-data="{
            createOpen: false,
            form: { date: '', time: '14:00' },
            openCreate(date) { this.form.date = date; this.form.time = '14:00'; this.createOpen = true; },

            dayOpen: false, dayLabel: '', dayEvents: [],
            openDay(label, events) { this.dayLabel = label; this.dayEvents = events; this.dayOpen = true; },

            eventOpen: false, event: {},
            openEvent(ev) { this.event = ev; this.eventOpen = true; this.dayOpen = false; },
         }">

    {{-- Header: title, person switcher, month nav --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">
                @if ($isSelf) Мій календар
                @else Календар: {{ $target->name }}
                @endif
            </h2>
            <p class="mt-1 text-sm text-slate-500">Заняття та події у форматі місяця.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($canCreate)
                <button @click="openCreate('{{ now()->format('Y-m-d') }}')"
                        class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">+ Урок</button>
            @endif
            {{-- Person switcher (teacher / parent / admin) --}}
            @if ($people->isNotEmpty())
                <form method="GET" class="w-56">
                    <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                    <x-search-select
                        name="user"
                        :options="$people->map(fn($p) => ['value' => $p->id, 'label' => $p->name])"
                        :selected="$isSelf ? '' : $target->id"
                        empty-label="Мій календар"
                        placeholder="Мій календар"
                        :submit-on-change="true" />
                </form>
            @endif

            {{-- Month navigation --}}
            <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1">
                <a href="{{ route('calendar', array_filter(['month' => $prev, 'user' => $isSelf ? null : $target->id])) }}"
                   class="rounded-lg px-2 py-1.5 text-slate-500 hover:bg-slate-100" title="Попередній місяць">‹</a>
                <span class="px-2 text-sm font-semibold text-slate-700">{{ $months[$month->month] }} {{ $month->year }}</span>
                <a href="{{ route('calendar', array_filter(['month' => $next, 'user' => $isSelf ? null : $target->id])) }}"
                   class="rounded-lg px-2 py-1.5 text-slate-500 hover:bg-slate-100" title="Наступний місяць">›</a>
            </div>
            <a href="{{ route('calendar', array_filter(['user' => $isSelf ? null : $target->id])) }}"
               class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Сьогодні</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        {{-- ===== Month grid ===== --}}
        <div class="lg:col-span-3">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                {{-- Weekday header --}}
                <div class="grid grid-cols-7 border-b border-slate-100 bg-slate-50 text-center text-xs font-semibold uppercase tracking-wide text-slate-400">
                    @foreach ($weekdays as $wd)
                        <div class="py-2">{{ $wd }}</div>
                    @endforeach
                </div>
                {{-- Days --}}
                <div class="grid grid-cols-7">
                    @foreach ($days as $day)
                        @php
                            // Build the JS payload for this day's events (used by modals).
                            $dayLabel = $day['date']->format('d.m.Y');
                            $events = $day['lessons']->map(fn ($l) => [
                                'id'       => $l->id,
                                'time'     => $l->scheduled_at->format('H:i'),
                                'datetime' => $l->scheduled_at->format('d.m.Y, H:i'),
                                'title'    => $l->title,
                                'desc'     => $l->description,
                                'duration' => $l->duration_min,
                                'status'   => $l->status,
                                'statusLabel' => $l->statusLabel(),
                                'statusColor' => $l->statusColor(),
                                'subject'  => $l->subject?->name,
                                'teacher'  => $l->teacher?->name,
                                'student'  => $l->student?->name,
                                'boardUrl' => $l->board ? route('board.show', $l->board) : null,
                            ])->values();
                        @endphp
                        <div class="group/day relative min-h-[92px] border-b border-r border-slate-100 p-1.5
                                    {{ $day['inMonth'] ? 'bg-white' : 'bg-slate-50/60' }}">
                            <div class="mb-1 flex items-center justify-between">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium
                                    {{ $day['isToday'] ? 'bg-indigo-600 text-white' : ($day['inMonth'] ? 'text-slate-600' : 'text-slate-300') }}">
                                    {{ $day['date']->day }}
                                </span>
                                @if ($day['lessons']->count())
                                    <button @click="openDay('{{ $dayLabel }}', {{ $events->toJson() }})"
                                            class="rounded px-1 text-[10px] font-semibold text-indigo-500 hover:bg-indigo-50" title="Усі події дня">
                                        {{ $day['lessons']->count() }}
                                    </button>
                                @endif
                            </div>
                            @if ($canCreate)
                                <button @click="openCreate('{{ $day['date']->format('Y-m-d') }}')"
                                        class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-md bg-indigo-50 text-xs font-bold text-indigo-500 opacity-0 transition hover:bg-indigo-100 group-hover/day:opacity-100"
                                        title="Додати урок">+</button>
                            @endif
                            <div class="space-y-1">
                                @foreach ($day['lessons']->take(3) as $lesson)
                                    @php
                                        $dot = $lesson->status === 'done' ? 'bg-emerald-500'
                                            : ($lesson->status === 'cancelled' ? 'bg-rose-400' : 'bg-indigo-500');
                                    @endphp
                                    <button @click='openEvent(@json($events[$loop->index]))'
                                         class="flex w-full items-center gap-1 truncate rounded-md bg-slate-50 px-1.5 py-0.5 text-left text-[11px] text-slate-600 hover:bg-indigo-50"
                                         title="Деталі: {{ $lesson->title }}">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $dot }}"></span>
                                        <span class="shrink-0 font-medium text-slate-500">{{ $lesson->scheduled_at->format('H:i') }}</span>
                                        <span class="truncate">{{ $lesson->title }}</span>
                                    </button>
                                @endforeach
                                @if ($day['lessons']->count() > 3)
                                    <button @click="openDay('{{ $dayLabel }}', {{ $events->toJson() }})"
                                            class="px-1 text-[10px] font-medium text-indigo-500 hover:underline">+ ще {{ $day['lessons']->count() - 3 }}</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Legend --}}
            <div class="mt-3 flex flex-wrap gap-4 text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-indigo-500"></span> Заплановано</span>
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Проведено</span>
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-rose-400"></span> Скасовано</span>
            </div>
        </div>

        {{-- ===== Upcoming list ===== --}}
        <aside class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-4 font-semibold text-slate-800">Найближчі заняття</h3>
                <div class="space-y-3">
                    @forelse ($upcoming as $lesson)
                        @php
                            $ev = [
                                'id' => $lesson->id,
                                'time' => $lesson->scheduled_at->format('H:i'),
                                'datetime' => $lesson->scheduled_at->format('d.m.Y, H:i'),
                                'title' => $lesson->title,
                                'desc' => $lesson->description,
                                'duration' => $lesson->duration_min,
                                'status' => $lesson->status,
                                'statusLabel' => $lesson->statusLabel(),
                                'statusColor' => $lesson->statusColor(),
                                'subject' => $lesson->subject?->name,
                                'teacher' => $lesson->teacher?->name,
                                'student' => $lesson->student?->name,
                                'boardUrl' => $lesson->board ? route('board.show', $lesson->board) : null,
                            ];
                        @endphp
                        <button @click='openEvent(@json($ev))' class="block w-full rounded-xl border border-slate-100 p-3 text-left transition hover:border-indigo-200 hover:bg-indigo-50/40">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-indigo-600">{{ $lesson->scheduled_at->format('d.m · H:i') }}</span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium {{ $lesson->statusColor() }}">{{ $lesson->statusLabel() }}</span>
                            </div>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $lesson->title }}</p>
                            <p class="text-xs text-slate-400">
                                @if ($lesson->subject){{ $lesson->subject->name }} · @endif
                                {{ auth()->user()->isStudent() || (!$isSelf && $target->isStudent()) ? $lesson->teacher?->name : $lesson->student?->name }}
                            </p>
                        </button>
                    @empty
                        <p class="text-sm text-slate-400">Найближчих занять немає.</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>

    {{-- ===== Day events list modal ===== --}}
    <div x-show="dayOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="dayOpen=false" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative flex max-h-[80vh] w-full max-w-md flex-col rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h3 class="font-bold text-slate-900">Події <span x-text="dayLabel"></span></h3>
                <button @click="dayOpen=false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100">✕</button>
            </div>
            <div class="space-y-2 overflow-y-auto p-4">
                <template x-for="ev in dayEvents" :key="ev.id">
                    <button @click="openEvent(ev)" class="block w-full rounded-xl border border-slate-100 p-3 text-left transition hover:border-indigo-200 hover:bg-indigo-50/40">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-indigo-600" x-text="ev.time"></span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-medium" :class="ev.statusColor" x-text="ev.statusLabel"></span>
                        </div>
                        <p class="mt-1 text-sm font-medium text-slate-800" x-text="ev.title"></p>
                        <p class="text-xs text-slate-400">
                            <span x-show="ev.subject" x-text="ev.subject ? ev.subject + ' · ' : ''"></span>
                            <span x-text="ev.student || ev.teacher || ''"></span>
                        </p>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- ===== Event details modal ===== --}}
    <div x-show="eventOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="eventOpen=false" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl">
            <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900" x-text="event.title"></h3>
                    <p class="mt-0.5 text-sm text-slate-500" x-text="event.datetime"></p>
                </div>
                <button @click="eventOpen=false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100">✕</button>
            </div>
            <div class="space-y-3 px-5 py-4 text-sm">
                <div class="flex items-center gap-2">
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="event.statusColor" x-text="event.statusLabel"></span>
                    <span class="text-slate-400" x-text="event.duration ? (event.duration + ' хв') : ''"></span>
                </div>
                <template x-if="event.desc">
                    <p class="whitespace-pre-line text-slate-600" x-text="event.desc"></p>
                </template>
                <dl class="space-y-2 border-t border-slate-100 pt-3">
                    <template x-if="event.subject">
                        <div class="flex gap-2"><dt class="w-24 shrink-0 text-slate-400">Напрям</dt><dd class="font-medium text-slate-700" x-text="event.subject"></dd></div>
                    </template>
                    <template x-if="event.teacher">
                        <div class="flex gap-2"><dt class="w-24 shrink-0 text-slate-400">Вчитель</dt><dd class="font-medium text-slate-700" x-text="event.teacher"></dd></div>
                    </template>
                    <template x-if="event.student">
                        <div class="flex gap-2"><dt class="w-24 shrink-0 text-slate-400">Учень</dt><dd class="font-medium text-slate-700" x-text="event.student"></dd></div>
                    </template>
                </dl>
                {{-- Board: open + copy link, or create if missing --}}
                <div class="flex flex-wrap items-center gap-2">
                    <template x-if="event.boardUrl">
                        <a :href="event.boardUrl" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">🖊 Відкрити дошку</a>
                    </template>
                    <template x-if="event.boardUrl">
                        <button type="button"
                                @click="navigator.clipboard.writeText(event.boardUrl); $el.querySelector('span').textContent='✓ Скопійовано'; setTimeout(() => $el.querySelector('span').textContent='🔗 Копіювати посилання', 1500)"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">
                            <span>🔗 Копіювати посилання</span>
                        </button>
                    </template>
                    @if ($canCreate)
                        <template x-if="!event.boardUrl">
                            <form method="POST" :action="`{{ url('teacher/lessons') }}/${event.id}/board-back`">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">🖊 Створити дошку</button>
                            </form>
                        </template>
                    @endif
                </div>

                @if ($canCreate)
                    {{-- Teacher: change status inline (full class names so Tailwind keeps them) --}}
                    @php
                        $statuses = [
                            'planned'   => ['Заплановано', 'border-indigo-300 bg-indigo-50 text-indigo-700'],
                            'done'      => ['Проведено',   'border-emerald-300 bg-emerald-50 text-emerald-700'],
                            'cancelled' => ['Скасовано',   'border-rose-300 bg-rose-50 text-rose-700'],
                        ];
                    @endphp
                    <div class="border-t border-slate-100 pt-3">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">Змінити статус</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($statuses as $val => $meta)
                                <form method="POST" :action="`{{ url('teacher/lessons') }}/${event.id}`">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $val }}">
                                    <button type="submit"
                                            class="rounded-lg border px-3 py-1.5 text-xs font-medium transition"
                                            :class="event.status === '{{ $val }}'
                                                ? '{{ $meta[1] }}'
                                                : 'border-slate-200 text-slate-500 hover:bg-slate-50'">
                                        {{ $meta[0] }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($canCreate)
    {{-- ===== Create lesson modal ===== --}}
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="createOpen=false" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-900">Новий урок</h3>
            <form method="POST" action="{{ route('teacher.lessons.store') }}" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                {{-- Combine chosen date + time into scheduled_at on submit --}}
                <input type="hidden" name="scheduled_at" :value="form.date + ' ' + form.time">

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-600">Тема уроку</label>
                    <input name="title" value="{{ old('title') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                    @error('title') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Дата</label>
                    <input type="date" x-model="form.date" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Час</label>
                    <input type="time" x-model="form.time" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Учень</label>
                    <x-search-select
                        name="student_id"
                        :options="$students->map(fn($s) => ['value' => $s->id, 'label' => $s->name])"
                        :selected="!$isSelf && $target->isStudent() ? $target->id : ''"
                        empty-label="— без призначення —"
                        placeholder="— без призначення —" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Напрям</label>
                    <select name="subject_id" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="">—</option>
                        @foreach ($subjects as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Тривалість (хв)</label>
                    <input type="number" name="duration_min" value="45" min="5" max="240" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600 sm:col-span-2">
                    <input type="checkbox" name="with_board" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                    Створити інтерактивну дошку для уроку
                </label>
                <div class="sm:col-span-2 flex justify-end gap-2">
                    <button type="button" @click="createOpen=false" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">Скасувати</button>
                    <button class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Створити урок</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    </div>
</x-app-shell>
