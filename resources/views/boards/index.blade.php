<x-app-shell title="Мої дошки">
    <div x-data="{ newBoard: false, newGroup: false, manage: false }" class="space-y-5">

        {{-- ===== Toolbar ===== --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">{{ $boards->total() }} {{ \Illuminate\Support\Str::plural('дошка', $boards->total()) }} · {{ $groups->count() }} {{ \Illuminate\Support\Str::plural('група', $groups->count()) }}</p>
            <div class="flex flex-wrap gap-2">
                <button @click="manage=!manage" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">⚙ Групи</button>
                <button @click="newGroup=!newGroup; newBoard=false" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">+ Група</button>
                <button @click="newBoard=!newBoard; newGroup=false" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">+ Нова дошка</button>
            </div>
        </div>

        {{-- New group form --}}
        <div x-show="newGroup" x-cloak class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="POST" action="{{ route('teacher.board-groups.store') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label class="mb-1 block text-sm font-medium text-slate-600">Назва групи</label>
                    <input name="name" placeholder="Напр. «Алгебра 7 клас»" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Колір</label>
                    <select name="color" class="rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        @foreach (['indigo'=>'Індиго','violet'=>'Фіолетовий','emerald'=>'Зелений','amber'=>'Бурштин','rose'=>'Рожевий','sky'=>'Блакитний'] as $v=>$l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Створити групу</button>
            </form>
        </div>

        {{-- New board form --}}
        <div x-show="newBoard" x-cloak class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="POST" action="{{ route('teacher.boards.store') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label class="mb-1 block text-sm font-medium text-slate-600">Назва дошки</label>
                    <input name="title" placeholder="Напр. «Урок 1: Дроби»" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Група</label>
                    <select name="group_id" class="rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="">Без групи</option>
                        @foreach ($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Створити дошку</button>
            </form>
        </div>

        {{-- ===== Group management (access) — compact accordion ===== --}}
        <div x-show="manage" x-cloak class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-3">
                <h3 class="text-sm font-bold text-slate-800">Групи (презентації) та доступ</h3>
                <p class="text-xs text-slate-400">Група — це презентація: кожна дошка в ній — окремий слайд. Кнопка «▶ Презентація» відкриває показ.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($groups as $group)
                    <div x-data="{ open: false }" class="px-5 py-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="h-3 w-3 shrink-0 rounded-full bg-gradient-to-br {{ $group->gradient() }}"></span>
                            <span class="font-semibold text-slate-800">{{ $group->name }}</span>
                            <span class="text-xs text-slate-400">{{ $group->boards_count }} {{ \Illuminate\Support\Str::plural('слайд', $group->boards_count) }} · {{ $group->members->count() }} учасників</span>
                            <div class="ml-auto flex items-center gap-2">
                                @if ($group->boards_count)
                                    <a href="{{ route('teacher.board-groups.present', $group) }}" target="_blank"
                                       class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">▶ Презентація</a>
                                @endif
                                <button @click="open=!open" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-200">👥 Доступ</button>
                                <form method="POST" action="{{ route('teacher.board-groups.destroy', $group) }}" onsubmit="return confirm('Видалити групу? Дошки залишаться без групи.')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg px-2 py-1.5 text-xs text-rose-400 hover:bg-rose-50" title="Видалити групу">🗑</button>
                                </form>
                            </div>
                        </div>

                        <div x-show="open" x-cloak class="mt-3 rounded-xl bg-slate-50 p-3">
                            <div class="mb-2 flex flex-wrap gap-1.5">
                                @forelse ($group->members as $m)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 text-xs font-medium text-slate-600 shadow-sm">
                                        {{ $m->name }}
                                        <form method="POST" action="{{ route('teacher.board-groups.uninvite', [$group, $m]) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button class="text-rose-400 hover:text-rose-600" title="Прибрати доступ">✕</button>
                                        </form>
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-400">Поки нікого не додано.</span>
                                @endforelse
                            </div>
                            @php $available = $students->whereNotIn('id', $group->members->pluck('id')); @endphp
                            @if ($available->isNotEmpty())
                                <form method="POST" action="{{ route('teacher.board-groups.invite', $group) }}" class="flex gap-2">
                                    @csrf
                                    <select name="student_id" required class="flex-1 rounded-lg border-slate-200 text-xs focus:border-indigo-400 focus:ring-indigo-400">
                                        <option value="">Додати учня…</option>
                                        @foreach ($available as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="rounded-lg bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-700">Додати</button>
                                </form>
                            @else
                                <p class="text-xs text-slate-400">Усіх ваших учнів уже додано.</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-400">Груп ще немає.</p>
                @endforelse
            </div>
        </div>

        {{-- ===== Filters ===== --}}
        <form method="GET" class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <div class="relative flex-1 min-w-[160px]">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
                <input name="search" value="{{ request('search') }}" placeholder="Пошук дошки…"
                       class="w-full rounded-xl border-slate-200 pl-9 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            </div>
            <select name="group" class="rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">Усі групи</option>
                <option value="none" @selected(request('group')==='none')>Без групи</option>
                @foreach ($groups as $g)
                    <option value="{{ $g->id }}" @selected(request('group')==(string)$g->id)>{{ $g->name }}</option>
                @endforeach
            </select>
            <select name="sort" class="rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="recent" @selected($sort==='recent')>Спочатку нові</option>
                <option value="name" @selected($sort==='name')>За назвою</option>
                <option value="content" @selected($sort==='content')>За наповненням</option>
            </select>
            <button class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Застосувати</button>
            @if (request()->hasAny(['search','group','sort']))
                <a href="{{ route('teacher.boards') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">Скинути</a>
            @endif
        </form>

        {{-- Active filter chips --}}
        @if (request('group') || request('search'))
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <span>Фільтр:</span>
                @if (request('search'))
                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-600">«{{ request('search') }}»</span>
                @endif
                @if (request('group') === 'none')
                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-600">Без групи</span>
                @elseif (request('group'))
                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-600">{{ optional($groups->firstWhere('id', (int) request('group')))->name }}</span>
                @endif
            </div>
        @endif

        {{-- ===== Boards list (compact) ===== --}}
        <div class="space-y-1.5">
            @forelse ($boards as $board)
                <x-board-card :board="$board" :groups="$groups" />
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <p class="text-4xl">🖊</p>
                    <p class="mt-3 font-semibold text-slate-700">Дошок не знайдено</p>
                    <p class="text-sm text-slate-400">Спробуйте змінити фільтри або створіть нову дошку.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($boards->hasPages())
            <div>{{ $boards->links() }}</div>
        @endif
    </div>
</x-app-shell>
