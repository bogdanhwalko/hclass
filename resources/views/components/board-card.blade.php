@props(['board', 'groups'])

@php
    $grad = $board->group?->gradient() ?? 'from-slate-600 to-slate-800';
@endphp

<div x-data="{ edit: false }" class="rounded-lg border border-slate-200 bg-white transition hover:border-slate-300">
    <div class="flex items-center gap-2.5 px-2.5 py-1.5">
        {{-- Thumbnail --}}
        <a href="{{ route('board.show', $board) }}" target="_blank"
           class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-gradient-to-br {{ $grad }} text-xs text-white/90">🖊</a>

        {{-- Title + meta (inline, compact) --}}
        <a href="{{ route('board.show', $board) }}" target="_blank" class="min-w-0 flex-1 truncate text-sm font-medium text-slate-800 hover:text-indigo-600">{{ $board->title }}</a>

        @if ($board->group)
            <span class="hidden shrink-0 items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-500 sm:inline-flex">
                <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-br {{ $board->group->gradient() }}"></span>{{ $board->group->name }}
            </span>
        @endif
        <span class="hidden shrink-0 text-[11px] text-slate-400 md:inline">{{ $board->widgets_count }}+{{ $board->strokes_count }}</span>

        {{-- Quick actions (icon-only) --}}
        <div class="flex shrink-0 items-center">
            <a href="{{ route('board.show', $board) }}" target="_blank" class="rounded-md px-1.5 py-1 text-sm text-slate-500 hover:bg-slate-100" title="Відкрити">↗</a>
            <a href="{{ route('board.show', $board) }}?view=1" target="_blank" class="rounded-md px-1.5 py-1 text-sm text-slate-500 hover:bg-slate-100" title="Перегляд">👁</a>
            <button onclick="navigator.clipboard.writeText('{{ route('board.show', $board) }}'); this.textContent='✓'; setTimeout(()=>this.textContent='🔗',1200)"
                    class="rounded-md px-1.5 py-1 text-sm text-slate-500 hover:bg-slate-100" title="Копіювати посилання">🔗</button>
            <button @click="edit=!edit" class="rounded-md px-1.5 py-1 text-sm text-slate-500 hover:bg-slate-100" title="Налаштування">⚙</button>
        </div>
    </div>

    {{-- Edit panel --}}
    <div x-show="edit" x-cloak class="flex flex-wrap items-end gap-2 border-t border-slate-100 bg-slate-50 px-2.5 py-2">
        <form method="POST" action="{{ route('teacher.boards.update', $board) }}" class="flex flex-1 flex-wrap items-end gap-2">
            @csrf @method('PATCH')
            <input name="title" value="{{ $board->title }}" class="min-w-[140px] flex-1 rounded-lg border-slate-200 text-xs focus:border-indigo-400 focus:ring-indigo-400" required>
            <select name="group_id" class="rounded-lg border-slate-200 text-xs focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">Без групи</option>
                @foreach ($groups as $g)
                    <option value="{{ $g->id }}" @selected($board->group_id === $g->id)>{{ $g->name }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">Зберегти</button>
        </form>
        <form method="POST" action="{{ route('teacher.boards.destroy', $board) }}" onsubmit="return confirm('Видалити дошку з усім вмістом?')">
            @csrf @method('DELETE')
            <button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-500 hover:bg-rose-50">Видалити</button>
        </form>
    </div>
</div>
