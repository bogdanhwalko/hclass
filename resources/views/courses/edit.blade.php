<x-app-shell title="Конструктор курсу">
    <div class="mx-auto max-w-4xl space-y-6">

        {{-- Course header / settings --}}
        <div x-data="{ settings: false }" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-4 bg-gradient-to-br {{ $course->gradient() }} p-6 text-white">
                <span class="text-5xl">{{ $course->emoji }}</span>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold">{{ $course->title }}</h2>
                    <p class="text-sm text-white/80">{{ $course->summary ?: 'Без опису' }}</p>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-medium">
                        {{ $course->is_published ? 'Опубліковано' : 'Чернетка' }}
                    </span>
                    <div class="flex gap-2">
                        <a href="{{ route('teacher.courses.preview', $course) }}" target="_blank" class="rounded-lg bg-white/20 px-3 py-1.5 text-xs font-medium hover:bg-white/30">👁 Пройти як учень</a>
                        <button @click="settings = !settings" class="rounded-lg bg-white/20 px-3 py-1.5 text-xs font-medium hover:bg-white/30">Налаштування</button>
                    </div>
                </div>
            </div>

            <div x-show="settings" x-cloak class="border-t border-slate-100 p-6">
                <form method="POST" action="{{ route('teacher.courses.update', $course) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @csrf @method('PATCH')
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-600">Назва</label>
                        <input name="title" value="{{ $course->title }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-600">Опис</label>
                        <textarea name="summary" rows="2" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">{{ $course->summary }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-600">Емодзі</label>
                            <input name="emoji" value="{{ $course->emoji }}" maxlength="4" class="w-full rounded-xl border-slate-200 text-center text-lg focus:border-indigo-400 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-600">Колір</label>
                            <select name="cover_color" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                                @foreach (['indigo'=>'Індиго','violet'=>'Фіолетовий','emerald'=>'Зелений','amber'=>'Бурштин','rose'=>'Рожевий','sky'=>'Блакитний'] as $v=>$l)
                                    <option value="{{ $v }}" @selected($course->cover_color===$v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600 sm:col-span-2">
                        <input type="checkbox" name="is_published" value="1" @checked($course->is_published) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                        Опублікувати курс (видимий учням)
                    </label>
                    <div class="sm:col-span-2">
                        <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Зберегти</button>
                    </div>
                </form>

                {{-- Separate form so it never submits the settings form (nested forms are invalid HTML) --}}
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <form method="POST" action="{{ route('teacher.courses.destroy', $course) }}" onsubmit="return confirm('Видалити курс назавжди?')">
                        @csrf @method('DELETE')
                        <button class="text-sm font-medium text-rose-500 hover:underline">Видалити курс</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modules --}}
        @foreach ($course->lessons as $lesson)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" x-data="{ adder: false, btype: 'text' }">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">
                        <span class="mr-1 text-slate-400">Модуль {{ $loop->iteration }}.</span>{{ $lesson->title }}
                    </h3>
                    <form method="POST" action="{{ route('teacher.courses.lessons.destroy', [$course, $lesson]) }}" onsubmit="return confirm('Видалити модуль з усім вмістом?')">
                        @csrf @method('DELETE')
                        <button class="rounded-lg px-2 py-1 text-xs font-medium text-rose-500 hover:bg-rose-50">Видалити модуль</button>
                    </form>
                </div>

                {{-- Existing blocks --}}
                <div class="mt-4 space-y-3">
                    @forelse ($lesson->blocks as $block)
                        <div class="group relative rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                            <form method="POST" action="{{ route('teacher.courses.blocks.destroy', [$course, $lesson, $block]) }}"
                                  class="absolute right-2 top-2 opacity-0 transition group-hover:opacity-100">
                                @csrf @method('DELETE')
                                <button class="rounded-md bg-white px-2 py-0.5 text-xs text-rose-500 shadow hover:bg-rose-50">✕</button>
                            </form>

                            @if ($block->type === 'text')
                                <span class="mb-1 inline-block rounded bg-slate-200 px-1.5 text-[10px] font-medium uppercase text-slate-500">Текст</span>
                                <p class="whitespace-pre-line text-sm text-slate-700">{{ $block->data['text'] ?? '' }}</p>
                            @elseif ($block->type === 'image')
                                <span class="mb-1 inline-block rounded bg-slate-200 px-1.5 text-[10px] font-medium uppercase text-slate-500">Зображення</span>
                                <img src="{{ $block->data['url'] ?? '' }}" alt="" class="max-h-56 rounded-lg border border-slate-200">
                                @if (!empty($block->data['caption']))
                                    <p class="mt-1 text-xs text-slate-400">{{ $block->data['caption'] }}</p>
                                @endif
                            @elseif ($block->type === 'button')
                                <span class="mb-1 inline-block rounded bg-slate-200 px-1.5 text-[10px] font-medium uppercase text-slate-500">Кнопка</span>
                                <span class="inline-block rounded-xl px-4 py-2 text-sm font-semibold {{ ($block->data['style'] ?? 'primary')==='primary' ? 'bg-indigo-600 text-white' : 'border border-slate-300 text-slate-700' }}">
                                    {{ $block->data['label'] ?? 'Кнопка' }}
                                </span>
                                <p class="mt-1 text-xs text-slate-400">→ {{ $block->data['url'] ?? '' }}</p>
                            @elseif ($block->type === 'quiz')
                                <span class="mb-1 inline-block rounded bg-slate-200 px-1.5 text-[10px] font-medium uppercase text-slate-500">Тест</span>
                                <p class="text-sm font-medium text-slate-800">{{ $block->data['question'] ?? '' }}</p>
                                <ul class="mt-2 space-y-1">
                                    @foreach (($block->data['options'] ?? []) as $i => $opt)
                                        <li class="flex items-center gap-2 text-sm {{ $i === ($block->data['answer'] ?? -1) ? 'font-semibold text-emerald-600' : 'text-slate-600' }}">
                                            <span class="flex h-4 w-4 items-center justify-center rounded-full border {{ $i === ($block->data['answer'] ?? -1) ? 'border-emerald-500 bg-emerald-500 text-[10px] text-white' : 'border-slate-300' }}">{{ $i === ($block->data['answer'] ?? -1) ? '✓' : '' }}</span>
                                            {{ $opt }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">У цьому модулі ще немає блоків.</p>
                    @endforelse
                </div>

                {{-- Add block --}}
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <div class="flex flex-wrap gap-2">
                        <button @click="adder=true; btype='text'"   :class="adder&&btype==='text' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'" class="rounded-lg px-3 py-1.5 text-sm font-medium">＋ Текст</button>
                        <button @click="adder=true; btype='image'"  :class="adder&&btype==='image' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'" class="rounded-lg px-3 py-1.5 text-sm font-medium">＋ Зображення</button>
                        <button @click="adder=true; btype='quiz'"   :class="adder&&btype==='quiz' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'" class="rounded-lg px-3 py-1.5 text-sm font-medium">＋ Тест</button>
                        <button @click="adder=true; btype='button'" :class="adder&&btype==='button' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'" class="rounded-lg px-3 py-1.5 text-sm font-medium">＋ Кнопка</button>
                    </div>

                    {{-- Text form --}}
                    <form x-show="adder&&btype==='text'" x-cloak method="POST" action="{{ route('teacher.courses.blocks.store', [$course, $lesson]) }}" class="mt-3 space-y-2">
                        @csrf <input type="hidden" name="type" value="text">
                        <textarea name="text" rows="3" placeholder="Текст матеріалу…" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required></textarea>
                        <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Додати текст</button>
                    </form>

                    {{-- Image form --}}
                    <form x-show="adder&&btype==='image'" x-cloak method="POST" action="{{ route('teacher.courses.blocks.store', [$course, $lesson]) }}" class="mt-3 space-y-2">
                        @csrf <input type="hidden" name="type" value="image">
                        <input name="url" type="url" placeholder="https://…/image.jpg" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                        <input name="caption" placeholder="Підпис (необов'язково)" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Додати зображення</button>
                    </form>

                    {{-- Quiz form --}}
                    <form x-show="adder&&btype==='quiz'" x-cloak method="POST" action="{{ route('teacher.courses.blocks.store', [$course, $lesson]) }}"
                          class="mt-3 space-y-2" x-data="{ opts: ['',''], answer: 0 }">
                        @csrf <input type="hidden" name="type" value="quiz">
                        <input name="question" placeholder="Питання" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                        <template x-for="(o, i) in opts" :key="i">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="answer" :value="i" x-model.number="answer" class="text-emerald-600 focus:ring-emerald-400" title="Правильна відповідь">
                                <input :name="`options[${i}]`" x-model="opts[i]" :placeholder="`Варіант ${i+1}`" class="flex-1 rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                                <button type="button" @click="opts.splice(i,1); if(answer>=opts.length)answer=0" x-show="opts.length>2" class="text-rose-400 hover:text-rose-600">✕</button>
                            </div>
                        </template>
                        <div class="flex items-center justify-between">
                            <button type="button" @click="opts.push('')" x-show="opts.length<6" class="text-sm font-medium text-indigo-600 hover:underline">+ Варіант</button>
                            <span class="text-xs text-slate-400">Позначте кружечком правильну відповідь</span>
                        </div>
                        <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Додати тест</button>
                    </form>

                    {{-- Button form --}}
                    <form x-show="adder&&btype==='button'" x-cloak method="POST" action="{{ route('teacher.courses.blocks.store', [$course, $lesson]) }}" class="mt-3 space-y-2">
                        @csrf <input type="hidden" name="type" value="button">
                        <input name="label" placeholder="Текст кнопки (напр. «Завантажити PDF»)" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                        <input name="url" type="url" placeholder="https://…" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                        <select name="style" class="rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                            <option value="primary">Основна (заповнена)</option>
                            <option value="secondary">Другорядна (контур)</option>
                        </select>
                        <button class="block rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Додати кнопку</button>
                    </form>
                </div>
            </div>
        @endforeach

        {{-- Add module --}}
        <form method="POST" action="{{ route('teacher.courses.lessons.store', $course) }}" class="flex gap-2 rounded-2xl border border-dashed border-slate-300 bg-white p-4">
            @csrf
            <input name="title" placeholder="Назва нового модуля…" class="flex-1 rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
            <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">+ Модуль</button>
        </form>

        <div class="flex items-center justify-between">
            <a href="{{ route('teacher.courses') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800">← До всіх курсів</a>
            <a href="{{ route('teacher.courses.preview', $course) }}" target="_blank" class="text-sm font-medium text-indigo-600 hover:underline">Пройти як учень →</a>
        </div>
    </div>
</x-app-shell>
