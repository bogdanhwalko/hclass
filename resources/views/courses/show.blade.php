@php $preview = $preview ?? false; @endphp
<x-app-shell title="{{ $course->title }}">
    <div class="mx-auto max-w-3xl space-y-6">

        @if ($preview)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                <p class="text-sm font-medium text-amber-700">👁 Режим перегляду — ви проходите курс очима учня. Тести можна складати, прогрес не зберігається.</p>
                <a href="{{ route('teacher.courses.edit', $course) }}" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">← Назад до редагування</a>
            </div>
        @else
            <a href="{{ route('student.courses') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-800">← Усі курси</a>
        @endif

        {{-- Course header --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-5 bg-gradient-to-br {{ $course->gradient() }} p-8 text-white">
                <span class="text-6xl">{{ $course->emoji }}</span>
                <div>
                    <h1 class="text-3xl font-black">{{ $course->title }}</h1>
                    <p class="mt-1 text-white/85">{{ $course->summary }}</p>
                    <p class="mt-3 text-sm text-white/70">Викладач: {{ $course->teacher->name }}@if ($course->subject) · {{ $course->subject->name }}@endif</p>
                </div>
            </div>
        </div>

        {{-- Modules & blocks --}}
        @forelse ($course->lessons as $lesson)
            <section class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                <h2 class="mb-5 flex items-center gap-2 text-xl font-bold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br {{ $course->gradient() }} text-sm text-white">{{ $loop->iteration }}</span>
                    {{ $lesson->title }}
                </h2>

                <div class="space-y-6">
                    @foreach ($lesson->blocks as $block)
                        @if ($block->type === 'text')
                            <div class="prose prose-slate max-w-none whitespace-pre-line text-slate-700">{{ $block->data['text'] ?? '' }}</div>

                        @elseif ($block->type === 'image')
                            <figure>
                                <img src="{{ $block->data['url'] ?? '' }}" alt="{{ $block->data['caption'] ?? '' }}" class="w-full rounded-xl border border-slate-200" loading="lazy">
                                @if (!empty($block->data['caption']))
                                    <figcaption class="mt-2 text-center text-sm text-slate-400">{{ $block->data['caption'] }}</figcaption>
                                @endif
                            </figure>

                        @elseif ($block->type === 'button')
                            <div>
                                <a href="{{ $block->data['url'] ?? '#' }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold transition
                                          {{ ($block->data['style'] ?? 'primary')==='primary'
                                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-700'
                                                : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }}">
                                    {{ $block->data['label'] ?? 'Відкрити' }}
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </a>
                            </div>

                        @elseif ($block->type === 'quiz')
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5"
                                 x-data="{ answer: {{ (int) ($block->data['answer'] ?? 0) }}, picked: null, done: false }">
                                <p class="mb-3 flex items-center gap-2 font-semibold text-slate-800">
                                    <span class="rounded-lg bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">Тест</span>
                                    {{ $block->data['question'] ?? '' }}
                                </p>
                                <div class="space-y-2">
                                    @foreach (($block->data['options'] ?? []) as $i => $opt)
                                        <button type="button"
                                                @click="if(!done){ picked = {{ $i }}; done = true }"
                                                :disabled="done"
                                                class="flex w-full items-center gap-3 rounded-xl border px-4 py-3 text-left text-sm transition"
                                                :class="done
                                                    ? ({{ $i }} === answer
                                                        ? 'border-emerald-300 bg-emerald-50 text-emerald-700'
                                                        : (picked === {{ $i }} ? 'border-rose-300 bg-rose-50 text-rose-700' : 'border-slate-200 text-slate-400'))
                                                    : 'border-slate-200 bg-white text-slate-700 hover:border-indigo-300 hover:bg-indigo-50'">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-xs"
                                                  :class="done && {{ $i }} === answer ? 'border-emerald-500 bg-emerald-500 text-white'
                                                          : (done && picked === {{ $i }} ? 'border-rose-500 bg-rose-500 text-white' : 'border-slate-300 text-slate-400')">
                                                <span x-show="done && {{ $i }} === answer">✓</span>
                                                <span x-show="done && picked === {{ $i }} && picked !== answer">✕</span>
                                                <span x-show="!done">{{ chr(65 + $i) }}</span>
                                            </span>
                                            {{ $opt }}
                                        </button>
                                    @endforeach
                                </div>
                                <p x-show="done" x-cloak class="mt-3 text-sm font-medium"
                                   :class="picked === answer ? 'text-emerald-600' : 'text-rose-600'">
                                    <span x-show="picked === answer">✓ Правильно! Чудова робота.</span>
                                    <span x-show="picked !== answer">✗ Спробуй ще раз наступного разу.</span>
                                    <button @click="done=false; picked=null" class="ml-2 text-indigo-600 underline">Повторити</button>
                                </p>
                            </div>
                        @endif
                    @endforeach

                    @if ($lesson->blocks->isEmpty())
                        <p class="text-sm text-slate-400">Матеріали для цього модуля готуються.</p>
                    @endif
                </div>
            </section>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <p class="text-4xl">📭</p>
                <p class="mt-3 font-semibold text-slate-700">Курс ще наповнюється</p>
                <p class="text-sm text-slate-400">Зазирни сюди трохи згодом.</p>
            </div>
        @endforelse
    </div>
</x-app-shell>
