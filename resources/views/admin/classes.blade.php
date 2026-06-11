<x-app-shell title="Групи">
    <div x-data="{ showForm: false }" class="space-y-6">

        <div class="flex justify-end">
            <button @click="showForm = !showForm" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">+ Нова група</button>
        </div>

        <div x-show="showForm" x-cloak class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('admin.classes.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Назва групи</label>
                    <input name="name" placeholder="Математика · Початковий рівень" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                    @error('name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Куратор</label>
                    <select name="homeroom_teacher_id" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="">—</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Створити</button>
                </div>
            </form>
        </div>

        <div class="space-y-5">
            @forelse ($classes as $class)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" x-data="{ tab: 'students' }">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">{{ $class->name }}</h3>
                            <p class="text-sm text-slate-400">
                                Куратор: {{ $class->homeroomTeacher?->name ?? '—' }} · {{ $class->students_count }} учасників · {{ $class->subjects->count() }} напрямів
                            </p>
                        </div>
                        <form method="POST" action="{{ route('admin.classes.destroy', $class) }}" onsubmit="return confirm('Видалити групу?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg px-3 py-1.5 text-xs font-medium text-rose-500 hover:bg-rose-50">Видалити групу</button>
                        </form>
                    </div>

                    <div class="mt-4 flex gap-2 border-b border-slate-100">
                        <button @click="tab='students'" :class="tab==='students' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-400'" class="border-b-2 px-3 py-2 text-sm font-medium">Учасники</button>
                        <button @click="tab='subjects'" :class="tab==='subjects' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-400'" class="border-b-2 px-3 py-2 text-sm font-medium">Напрями</button>
                    </div>

                    <div x-show="tab==='students'" class="mt-4 space-y-3">
                        <form method="POST" action="{{ route('admin.classes.students.attach', $class) }}" class="flex gap-2">
                            @csrf
                            <select name="student_id" class="flex-1 rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                                <option value="">Обрати учня…</option>
                                @foreach ($students as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-xl bg-slate-800 px-4 text-sm font-medium text-white hover:bg-slate-700">Додати</button>
                        </form>
                        <div class="flex flex-wrap gap-2">
                            @forelse ($class->students as $s)
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ $s->name }}</span>
                            @empty
                                <span class="text-sm text-slate-400">Учасників ще немає.</span>
                            @endforelse
                        </div>
                    </div>

                    <div x-show="tab==='subjects'" x-cloak class="mt-4 space-y-3">
                        <form method="POST" action="{{ route('admin.classes.subjects.attach', $class) }}" class="flex flex-wrap gap-2">
                            @csrf
                            <select name="subject_id" class="flex-1 rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                                <option value="">Предмет…</option>
                                @foreach ($subjects as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                            <select name="teacher_id" class="flex-1 rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                                <option value="">Вчитель…</option>
                                @foreach ($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-xl bg-slate-800 px-4 text-sm font-medium text-white hover:bg-slate-700">Додати</button>
                        </form>
                        <div class="flex flex-wrap gap-2">
                            @forelse ($class->subjects as $sub)
                                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600">{{ $sub->name }}</span>
                            @empty
                                <span class="text-sm text-slate-400">Напрямів ще немає.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400">Груп ще немає.</p>
            @endforelse
        </div>
    </div>
</x-app-shell>
