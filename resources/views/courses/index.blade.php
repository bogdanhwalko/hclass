<x-app-shell title="Мої курси">
    <div x-data="{ showForm: false }" class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">Створюйте власні курси та наповнюйте їх текстом, зображеннями, тестами й кнопками.</p>
            <button @click="showForm = !showForm" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">+ Новий курс</button>
        </div>

        <div x-show="showForm" x-cloak class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('teacher.courses.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-600">Назва курсу</label>
                    <input name="title" value="{{ old('title') }}" placeholder="Основи математики" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                    @error('title') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-600">Короткий опис</label>
                    <textarea name="summary" rows="2" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">{{ old('summary') }}</textarea>
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
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-600">Емодзі</label>
                        <input name="emoji" value="📘" maxlength="4" class="w-full rounded-xl border-slate-200 text-center text-lg focus:border-indigo-400 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-600">Колір</label>
                        <select name="cover_color" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                            @foreach (['indigo'=>'Індиго','violet'=>'Фіолетовий','emerald'=>'Зелений','amber'=>'Бурштин','rose'=>'Рожевий','sky'=>'Блакитний'] as $v=>$l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Створити та наповнити</button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($courses as $course)
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex h-28 items-center justify-center bg-gradient-to-br {{ $course->gradient() }} text-5xl">{{ $course->emoji }}</div>
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-slate-900">{{ $course->title }}</h3>
                            @if ($course->is_published)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Опубліковано</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Чернетка</span>
                            @endif
                        </div>
                        <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $course->summary ?: 'Без опису' }}</p>
                        <div class="mt-3 flex items-center gap-3 text-xs text-slate-400">
                            <span>{{ $course->lessons_count }} модулів</span>
                            <span>·</span>
                            <span>{{ $course->students_count }} учнів</span>
                        </div>
                        <a href="{{ route('teacher.courses.edit', $course) }}" class="mt-4 block rounded-xl bg-slate-900 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-slate-800">Редагувати</a>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <p class="text-4xl">📚</p>
                    <p class="mt-3 font-semibold text-slate-700">Курсів ще немає</p>
                    <p class="text-sm text-slate-400">Створіть перший курс кнопкою «Новий курс».</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-shell>
