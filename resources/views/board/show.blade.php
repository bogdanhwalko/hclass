@php
    // Curated background gallery (free Unsplash images) — used for both the board
    // background and flashcard backgrounds.
    $gallery = [
        'Дошка зелена'   => 'https://images.unsplash.com/photo-1632571401005-458e9d244591?w=1200&q=70',
        'Класна дошка'   => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1200&q=70',
        'Папір у клітинку' => 'https://images.unsplash.com/photo-1517842645767-c639042777db?w=1200&q=70',
        'Космос'         => 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?w=1200&q=70',
        'Природа'        => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1200&q=70',
        'Геометрія'      => 'https://images.unsplash.com/photo-1550859492-d5da9d8e45f3?w=1200&q=70',
        'Абстракція'     => 'https://images.unsplash.com/photo-1557672172-298e090bd0f1?w=1200&q=70',
        'Мапа світу'     => 'https://images.unsplash.com/photo-1524661135-423995f22d0b?w=1200&q=70',
    ];
@endphp
<!DOCTYPE html>
<html lang="uk" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $board->title }} · HClass</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full overflow-hidden bg-slate-100 font-sans text-slate-800 antialiased">

<div class="flex h-full flex-col"
     x-data="board({
        isTeacher: {{ $isTeacher ? 'true' : 'false' }},
        canDrawInit: {{ $canDraw ? 'true' : 'false' }},
        strokesUrl: '{{ route('board.strokes', $board) }}',
        drawUrl: '{{ route('board.draw', $board) }}',
        permissionUrl: '{{ route('board.permission', $board) }}',
        clearUrl: '{{ route('board.clear', $board) }}',
        strokeMoveUrl: '{{ route('board.stroke.move', $board) }}',
        strokeDeleteUrl: '{{ route('board.stroke.delete', $board) }}',
        imageUploadUrl: '{{ route('board.image.upload', $board) }}',
        assetUploadUrl: '{{ route('board.asset.upload', $board) }}',
        imageMoveUrl: '{{ route('board.image.move', $board) }}',
        imageDeleteUrl: '{{ route('board.image.delete', $board) }}',
        widgetStoreUrl: '{{ route('board.widget.store', $board) }}',
        widgetMoveUrl: '{{ route('board.widget.move', $board) }}',
        widgetStyleUrl: '{{ route('board.widget.style', $board) }}',
        widgetDeleteUrl: '{{ route('board.widget.delete', $board) }}',
        widgetCheckUrl: '{{ route('board.widget.check', $board) }}',
        widgetLayerUrl: '{{ route('board.widget.layer', $board) }}',
        allowInit: {{ $board->students_can_draw ? 'true' : 'false' }},
        clearedInit: '{{ optional($board->cleared_at)->toISOString() }}',
        @if ($isTeacher)
        inviteUrl: '{{ route('board.invite', $board) }}',
        uninviteUrlBase: '{{ route('board.uninvite', [$board, 0]) }}',
        invitedInit: {{ Illuminate\Support\Js::from($invitedStudents->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()) }},
        availableInit: {{ Illuminate\Support\Js::from($availableStudents->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()) }},
        @endif
     })">

    {{-- ===== Toolbar ===== --}}
    <header class="z-10 border-b border-slate-200 bg-white shadow-sm">
        {{-- Row 1: brand + title + right controls --}}
        @php $backUrl = $isTeacher ? route('teacher.boards') : route('dashboard'); @endphp
        <div class="flex items-center gap-2 px-3 py-2 sm:px-4">
            <a href="{{ $backUrl }}"
               class="flex h-8 shrink-0 items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
               title="Повернутися{{ $isTeacher ? ' до моїх дошок' : '' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline">Назад</span>
            </a>
            <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2" title="На головну">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 font-bold text-white">H</div>
            </a>
            <h1 class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-800">{{ $board->title }}</h1>

            @if ($isTeacher)
                {{-- Teacher edit controls — hidden in view (presentation) mode --}}
                <div class="flex shrink-0 items-center gap-1.5" x-show="!viewMode">
                    <button @click="toggleAllow()"
                            :class="allow ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-700 hover:bg-slate-800'"
                            class="shrink-0 rounded-lg px-2.5 py-2 text-xs font-semibold text-white transition"
                            :title="allow ? 'Учні можуть малювати' : 'Дозволити учням малювати'">
                        <span x-text="allow ? '✓ Малюють' : 'Дозволити'"></span>
                    </button>
                    <button @click="clearBoard()" title="Очистити дошку"
                            class="shrink-0 rounded-lg border border-slate-200 px-2.5 py-2 text-xs font-medium text-rose-500 hover:bg-rose-50">
                        <span class="hidden sm:inline">Очистити</span><span class="sm:hidden">🗑</span>
                    </button>
                    <button @click="panel = !panel" title="Учасники"
                            class="shrink-0 rounded-lg border border-slate-200 px-2.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">
                        <span class="hidden sm:inline">👥 Учасники</span><span class="sm:hidden">👥</span>
                    </button>
                </div>
            @else
                <span class="shrink-0 truncate rounded-full bg-slate-100 px-2.5 py-0.5 text-xs text-slate-500" x-show="!viewMode">{{ $board->teacher->name }}</span>
            @endif

            {{-- View / Edit toggle — available to everyone for a clean presentation view --}}
            <button @click="viewMode = !viewMode"
                    class="shrink-0 rounded-lg border px-2.5 py-2 text-xs font-semibold transition"
                    :class="viewMode ? 'border-indigo-600 bg-indigo-600 text-white hover:bg-indigo-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                    :title="viewMode ? 'Вийти з режиму перегляду' : 'Режим перегляду'">
                <span x-show="!viewMode">👁<span class="ml-1 hidden sm:inline">Перегляд</span></span>
                <span x-show="viewMode" x-cloak>✏<span class="ml-1 hidden sm:inline">Редагувати</span></span>
            </button>
        </div>

        {{-- Row 2: drawing tools — horizontally scrollable so it never overflows on phones --}}
        <div class="flex items-center gap-1.5 overflow-x-auto border-t border-slate-100 px-3 py-2 sm:px-4"
             x-show="effectiveCanDraw()" x-cloak>
            <template x-for="t in tools" :key="t.id">
                <button @click="tool = t.id" :title="t.label"
                        :class="tool===t.id ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-base font-semibold transition" x-html="t.icon"></button>
            </template>

            <div class="mx-1 h-6 w-px shrink-0 bg-slate-200"></div>

            <template x-for="c in colors" :key="c">
                <button @click="color = c" :style="`background:${c}`"
                        :class="color===c ? 'ring-2 ring-offset-1 ring-slate-900' : 'border border-slate-200'"
                        class="h-7 w-7 shrink-0 rounded-full"></button>
            </template>

            <div class="mx-1 h-6 w-px shrink-0 bg-slate-200"></div>

            <input type="range" min="1" max="40" x-model.number="width" class="w-24 shrink-0 accent-indigo-600" title="Товщина">
            <span class="w-9 shrink-0 text-xs text-slate-500" x-text="width + 'px'"></span>

            <div class="mx-1 h-6 w-px shrink-0 bg-slate-200"></div>

            {{-- Image upload --}}
            <button @click="$refs.imgInput.click()" title="Завантажити зображення"
                    class="flex h-9 shrink-0 items-center gap-1.5 rounded-lg bg-slate-100 px-3 text-sm font-medium text-slate-600 hover:bg-slate-200">
                <span class="text-base">📷</span><span class="hidden sm:inline">Зображення</span>
            </button>
            <span x-show="uploading" x-cloak class="shrink-0 text-xs text-indigo-500">Завантаження…</span>
            <input x-ref="imgInput" type="file" accept="image/*" class="hidden" @change="uploadImage($event)">

            <div class="mx-1 h-6 w-px shrink-0 bg-slate-200"></div>

            {{-- Frame --}}
            <button @click="openFrame()" title="Додати фрейм"
                    class="flex h-9 shrink-0 items-center gap-1.5 rounded-lg bg-slate-100 px-3 text-sm font-medium text-slate-600 hover:bg-slate-200">
                <span class="text-base">🖼</span><span class="hidden sm:inline">Фрейм</span>
            </button>

            {{-- Quiz / Flashcard / Link --}}
            <button @click="openQuiz()" title="Додати тест"
                    class="flex h-9 shrink-0 items-center gap-1.5 rounded-lg bg-slate-100 px-3 text-sm font-medium text-slate-600 hover:bg-slate-200">
                <span class="text-base">📝</span><span class="hidden sm:inline">Тест</span>
            </button>
            <button @click="openCard()" title="Додати картку"
                    class="flex h-9 shrink-0 items-center gap-1.5 rounded-lg bg-slate-100 px-3 text-sm font-medium text-slate-600 hover:bg-slate-200">
                <span class="text-base">🃏</span><span class="hidden sm:inline">Картка</span>
            </button>
            <button @click="openLink()" title="Додати кнопку-посилання"
                    class="flex h-9 shrink-0 items-center gap-1.5 rounded-lg bg-slate-100 px-3 text-sm font-medium text-slate-600 hover:bg-slate-200">
                <span class="text-base">🔗</span><span class="hidden sm:inline">Лінк</span>
            </button>
        </div>
    </header>

    {{-- Status banner for students (only while editing, not in presentation mode) --}}
    @unless ($isTeacher)
        <div x-show="!viewMode && !effectiveCanDraw()" x-cloak class="bg-amber-50 px-4 py-2 text-center text-xs text-amber-700">
            Режим перегляду. Вчитель ще не дозволив малювати.
        </div>
    @endunless

    <div class="relative flex flex-1 overflow-hidden">
        {{-- ===== Viewport: infinite-canvas surface. The stage is panned/zoomed via a
                 CSS transform (Miro-style), so the wrapper never scrolls. ===== --}}
        <div class="relative flex-1 overflow-hidden bg-white touch-none select-none" x-ref="wrap"
             @wheel="onWheel($event)"
             @pointerdown="onViewportPointerDown($event)"
             :class="(tool==='hand'||spaceDown) ? (panning ? 'cursor-grabbing' : 'cursor-grab') : (!effectiveCanDraw() ? 'cursor-default' : (tool==='move' ? 'cursor-move' : (tool==='text' ? 'cursor-text' : 'cursor-crosshair')))">

            {{-- ===== Layer 1: FRAMES (behind the canvas, so strokes draw on top) ===== --}}
            {{-- Always click-through — frames are selected/moved via the canvas hit-test. --}}
            {{-- NOTE: pointer-events & z-index MUST live inside :style — a STRING :style
                 binding replaces the static style attribute, so static ones get dropped. --}}
            <div x-ref="stageBack" class="absolute left-0 top-0"
                 :style="`width:${baseW}px;height:${baseH}px;transform:translate(${panX}px,${panY}px) scale(${zoom});transform-origin:top left;pointer-events:none;z-index:0;`">
                <template x-for="wg in widgets.filter(w => w.type==='frame')" :key="wg.id">
                    <div class="absolute"
                         :style="`left:${wg.x*boardW()}px; top:${wg.y*boardW()}px; width:${widgetW(wg)}px; height:${widgetH(wg)}px; z-index:${wg.z||0}; opacity:${wg.opacity ?? 1};`">
                        <div class="h-full w-full overflow-hidden rounded-xl border-2 bg-cover bg-center shadow-sm"
                             :class="selectedWidget===wg.id ? 'border-indigo-500' : 'border-slate-300/70'"
                             :style="(wg.data.bg ? `background-image:url('${wg.data.bg}')` : `background-color:${wg.data.color||'#ffffff'}`)">
                            <div x-show="wg.data.title" class="absolute -top-6 left-0 max-w-full truncate rounded-md bg-slate-800 px-2 py-0.5 text-xs font-medium text-white" x-text="wg.data.title"></div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Drawing canvas: viewport-sized; camera (pan+zoom) applied in the 2D
                 context. Sits ABOVE frames (so strokes show over them) and is the single
                 surface that receives drawing + frame/stroke hit-testing. --}}
            <canvas x-ref="canvas" class="absolute inset-0 h-full w-full touch-none select-none" style="z-index:1;"></canvas>

            {{-- ===== Layer 2: interactive widgets (above canvas) ===== --}}
            <div x-ref="stage" class="absolute left-0 top-0"
                 :style="`width:${baseW}px;height:${baseH}px;transform:translate(${panX}px,${panY}px) scale(${zoom});transform-origin:top left;pointer-events:none;z-index:2;`">

            {{-- ===== Image layer (movable / resizable) ===== --}}
            <template x-for="img in images" :key="img.id">
                <div class="absolute select-none"
                     :style="`left:${img.x*boardW()}px; top:${img.y*boardW()}px; width:${img.w*boardW()}px; pointer-events:${widgetsInteractive() ? 'auto' : 'none'};`"
                     :class="selectedImg===img.id ? 'z-20' : 'z-10'">
                    <img :src="img.url" draggable="false"
                         @mousedown.stop.prevent="startDragImg($event, img)"
                         @touchstart.stop.prevent="startDragImg($event, img)"
                         @click.stop="if(effectiveCanDraw()) selectedImg = img.id"
                         class="block w-full rounded shadow-md ring-2 transition"
                         :class="selectedImg===img.id ? 'ring-indigo-500' : 'ring-transparent'"
                         :style="effectiveCanDraw() ? 'cursor:move' : ''">

                    {{-- Controls when selected --}}
                    <template x-if="selectedImg===img.id && effectiveCanDraw()">
                        <div>
                            {{-- Resize handle (bottom-right) --}}
                            <div @mousedown.stop.prevent="startResizeImg($event, img)"
                                 @touchstart.stop.prevent="startResizeImg($event, img)"
                                 class="absolute -bottom-2 -right-2 h-5 w-5 rounded-full border-2 border-white bg-indigo-500 shadow"
                                 style="cursor:nwse-resize"></div>
                            {{-- Delete --}}
                            <button @click.stop="removeImage(img)"
                                    class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full border-2 border-white bg-rose-500 text-[10px] text-white shadow"
                                    title="Видалити">✕</button>
                        </div>
                    </template>
                </div>
            </template>

            {{-- ===== Widget layer (quizzes / flashcards / links) — frames live behind ===== --}}
            <template x-for="wg in widgets.filter(w => w.type!=='frame')" :key="wg.id">
                <div class="absolute"
                     :style="`left:${wg.x*boardW()}px; top:${wg.y*boardW()}px; width:${widgetW(wg)}px; height:${widgetH(wg)}px; z-index:${10 + (wg.z||0)}; opacity:${wg.opacity ?? 1}; pointer-events:${widgetsInteractive() ? 'auto' : 'none'}; cursor:${effectiveCanDraw() && tool==='move' && wg.type!=='link' ? 'move' : ''};`"
                     @pointerdown="if (effectiveCanDraw() && tool==='move' && wg.type!=='link') startDragWidget($event, wg)">

                    {{-- selection outline --}}
                    <div x-show="selectedWidget===wg.id" x-cloak class="pointer-events-none absolute -inset-1 rounded-xl ring-2 ring-indigo-500"></div>

                    {{-- QUIZ --}}
                    <template x-if="wg.type==='quiz'">
                        <div class="h-full overflow-auto rounded-xl border border-slate-200 bg-white p-4 shadow-lg">
                            <p class="text-sm font-semibold text-slate-800" x-text="wg.data.question"></p>
                            <div class="mt-3 space-y-2">
                                <template x-for="(opt, i) in wg.data.options" :key="i">
                                    <button @click="if (!effectiveCanDraw()) answerQuiz(wg, i)"
                                            :disabled="wg._done"
                                            class="flex w-full items-center gap-2 rounded-lg border px-3 py-2 text-left text-sm transition"
                                            :class="quizOptClass(wg, i)">
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-xs"
                                              :class="quizBadgeClass(wg, i)"
                                              x-text="quizBadge(wg, i)"></span>
                                        <span x-text="opt"></span>
                                    </button>
                                </template>
                            </div>
                            <p x-show="wg._done" x-cloak class="mt-3 text-sm font-medium"
                               :class="wg._correct ? 'text-emerald-600' : 'text-rose-600'">
                                <span x-show="wg._correct">✓ Правильно!</span>
                                <span x-show="!wg._correct">✗ Спробуй ще раз.</span>
                                <button @click="resetQuiz(wg)" class="ml-2 text-indigo-600 underline">Повторити</button>
                            </p>
                        </div>
                    </template>

                    {{-- FLASHCARD --}}
                    <template x-if="wg.type==='flashcard'">
                        <div @click="if (!effectiveCanDraw() || selectedWidget!==wg.id) wg._flip = !wg._flip"
                             class="relative flex h-full cursor-pointer select-none flex-col items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-cover bg-center p-5 text-center shadow-lg transition"
                             :class="(wg._flip ? wg.data.bg_back : wg.data.bg_front) ? 'text-white' : (wg._flip ? 'bg-indigo-600 text-white' : 'bg-white text-slate-800')"
                             :style="((wg._flip ? wg.data.bg_back : wg.data.bg_front) ? `background-image:linear-gradient(rgba(15,23,42,.45),rgba(15,23,42,.55)),url('${wg._flip ? wg.data.bg_back : wg.data.bg_front}')` : '')">
                            <span class="mb-1 text-[10px] font-medium uppercase tracking-wide opacity-80"
                                  x-text="wg._flip ? 'Відповідь' : 'Питання'"></span>
                            <p class="text-base font-semibold" x-text="wg._flip ? wg.data.back : wg.data.front"></p>
                            <span class="mt-2 text-xs opacity-70">↻ натисни, щоб перевернути</span>
                        </div>
                    </template>

                    {{-- LINK button --}}
                    <template x-if="wg.type==='link'">
                        <a :href="wg.data.url" target="_blank" rel="noopener"
                           class="flex h-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold shadow-lg transition"
                           :class="wg.data.style==='secondary' ? 'bg-white text-indigo-700 hover:bg-indigo-50' : 'bg-indigo-600 text-white hover:bg-indigo-700 border-indigo-600'">
                            <span>🔗</span><span x-text="wg.data.label"></span>
                        </a>
                    </template>

                    {{-- ===== Edit overlay (only for the selected widget): resize handles
                             only — all actions live in the top-center bar (like shapes) ===== --}}
                    <template x-if="effectiveCanDraw() && selectedWidget===wg.id">
                        <div class="pointer-events-none absolute inset-0">
                            <div @pointerdown.stop.prevent="startResize($event, wg, 'both')"
                                 class="pointer-events-auto absolute -bottom-1.5 -right-1.5 h-4 w-4 rounded-full border-2 border-white bg-indigo-500 shadow" style="cursor:nwse-resize" title="Розмір (Shift — пропорційно)"></div>
                            <div @pointerdown.stop.prevent="startResize($event, wg, 'x')"
                                 class="pointer-events-auto absolute -right-1.5 top-1/2 h-4 w-4 -translate-y-1/2 rounded-full border-2 border-white bg-indigo-500 shadow" style="cursor:ew-resize" title="Ширина"></div>
                            <div @pointerdown.stop.prevent="startResize($event, wg, 'y')"
                                 class="pointer-events-auto absolute -bottom-1.5 left-1/2 h-4 w-4 -translate-x-1/2 rounded-full border-2 border-white bg-indigo-500 shadow" style="cursor:ns-resize" title="Висота"></div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- ===== Selected-FRAME overlay (frame lives behind canvas, so its handles
                     live here, in the front layer, positioned over the frame) ===== --}}
            <template x-for="wg in widgets.filter(w => w.type==='frame' && w.id===selectedWidget)" :key="'fo'+wg.id">
                <template x-if="effectiveCanDraw()">
                    <div class="absolute" :style="`left:${wg.x*boardW()}px; top:${wg.y*boardW()}px; width:${widgetW(wg)}px; height:${widgetH(wg)}px; z-index:50;`">
                        {{-- move handle: whole body (actions live in the top-center bar) --}}
                        <div @pointerdown.stop.prevent="startDragWidget($event, wg)"
                             class="absolute inset-0 cursor-move rounded-xl ring-2 ring-indigo-500" style="pointer-events:auto;"></div>
                        {{-- resize handles --}}
                        <div @pointerdown.stop.prevent="startResize($event, wg, 'both')"
                             class="absolute -bottom-1.5 -right-1.5 h-4 w-4 rounded-full border-2 border-white bg-indigo-500 shadow" style="cursor:nwse-resize; pointer-events:auto;"></div>
                        <div @pointerdown.stop.prevent="startResize($event, wg, 'x')"
                             class="absolute -right-1.5 top-1/2 h-4 w-4 -translate-y-1/2 rounded-full border-2 border-white bg-indigo-500 shadow" style="cursor:ew-resize; pointer-events:auto;"></div>
                        <div @pointerdown.stop.prevent="startResize($event, wg, 'y')"
                             class="absolute -bottom-1.5 left-1/2 h-4 w-4 -translate-x-1/2 rounded-full border-2 border-white bg-indigo-500 shadow" style="cursor:ns-resize; pointer-events:auto;"></div>
                    </div>
                </template>
            </template>

            {{-- Inline text input overlay --}}
            <div x-show="textBox.active" x-cloak
                 :style="`left:${textBox.px}px; top:${textBox.py}px;pointer-events:auto;`"
                 class="absolute z-20">
                <input x-ref="textInput" x-model="textBox.value"
                       @keydown.enter.prevent="commitText()" @keydown.escape="cancelText()"
                       @blur="commitText()"
                       :style="`color:${color}; font-size:${textFontPx()}px; line-height:1`"
                       class="min-w-[120px] border-b-2 border-indigo-400 bg-transparent font-semibold outline-none"
                       placeholder="Текст…">
            </div>
            </div>{{-- /stage --}}

            {{-- ===== Selected-STROKE resize box (SCREEN space, constant handle size) =====
                 Lives in the viewport (not the scaled stage) so handles stay grabbable at
                 any zoom and never get occluded by widget layers. Positioned via the camera. --}}
            <template x-if="effectiveCanDraw() && tool==='move' && selectedStroke !== null && strokeBoxScreen()">
                <div class="pointer-events-none absolute z-30 rounded ring-1 ring-indigo-400/70" :style="strokeBoxScreenStyle()">
                    {{-- corner = scale both, right = width, bottom = height --}}
                    <div @pointerdown.stop.prevent="startStrokeResize($event, 'both')"
                         class="pointer-events-auto absolute -bottom-2 -right-2 h-5 w-5 rounded-full border-2 border-white bg-indigo-500 shadow" style="cursor:nwse-resize" title="Розмір (Shift — пропорційно)"></div>
                    <div @pointerdown.stop.prevent="startStrokeResize($event, 'x')"
                         class="pointer-events-auto absolute -right-2 top-1/2 h-5 w-5 -translate-y-1/2 rounded-full border-2 border-white bg-indigo-500 shadow" style="cursor:ew-resize" title="Ширина"></div>
                    <div @pointerdown.stop.prevent="startStrokeResize($event, 'y')"
                         class="pointer-events-auto absolute -bottom-2 left-1/2 h-5 w-5 -translate-x-1/2 rounded-full border-2 border-white bg-indigo-500 shadow" style="cursor:ns-resize" title="Висота"></div>
                </div>
            </template>

            {{-- Floating action bar for a selected shape/text (move tool) — over viewport --}}
            <div x-show="tool==='move' && selectedStroke !== null" x-cloak
                 class="absolute left-1/2 top-3 z-30 flex -translate-x-1/2 items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs shadow-lg">
                <span class="text-slate-500">Обʼєкт вибрано</span>
                <button @click="strokeToFront()" class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600 hover:bg-slate-200" title="На передній план">⬆ Вгору</button>
                <button @click="strokeToBack()" class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600 hover:bg-slate-200" title="На задній план">⬇ Вниз</button>
                <button @click="deleteSelectedStroke()" class="rounded-full bg-rose-500 px-2.5 py-1 font-medium text-white hover:bg-rose-600">Видалити</button>
                <button @click="selectedStroke = null; redraw()" class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600 hover:bg-slate-200">Зняти</button>
            </div>

            {{-- Floating action bar for a selected WIDGET / FRAME (quiz, flashcard, frame) --}}
            <template x-if="effectiveCanDraw() && selWidget()">
                <div class="absolute left-1/2 top-3 z-30 flex -translate-x-1/2 items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs shadow-lg">
                    <span class="font-medium text-slate-500" x-text="selWidget().type==='quiz' ? 'Тест' : (selWidget().type==='flashcard' ? 'Картка' : (selWidget().type==='frame' ? 'Фрейм' : 'Блок'))"></span>
                    <button @click="layerWidget(selWidget(),'front')" class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600 hover:bg-slate-200" title="На передній план">⬆ Вгору</button>
                    <button @click="layerWidget(selWidget(),'back')" class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600 hover:bg-slate-200" title="На задній план">⬇ Вниз</button>
                    <span class="flex items-center gap-1" title="Прозорість">
                        <span>🌗</span>
                        <input type="range" min="0.1" max="1" step="0.05" :value="selWidget().opacity ?? 1"
                               @input="selWidget().opacity = parseFloat($event.target.value)"
                               @change="saveOpacity(selWidget())" class="w-16 accent-indigo-600">
                    </span>
                    <button @click="removeWidget(selWidget())" class="rounded-full bg-rose-500 px-2.5 py-1 font-medium text-white hover:bg-rose-600">Видалити</button>
                    <button @click="selectedWidget = null" class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600 hover:bg-slate-200">Зняти</button>
                </div>
            </template>

            {{-- ===== Zoom / pan controls (fixed bottom-right of viewport) ===== --}}
            <div class="absolute bottom-4 right-4 z-30 flex items-center gap-1 rounded-xl border border-slate-200 bg-white/95 p-1 shadow-lg backdrop-blur">
                <button @click="tool = (tool==='hand' ? 'pen' : 'hand')"
                        :class="tool==='hand' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-sm" title="Рука (переміщення) — або утримуйте Пробіл">🖐</button>
                <div class="mx-0.5 h-5 w-px bg-slate-200"></div>
                <button @click="zoomBy(-0.1)" class="flex h-8 w-8 items-center justify-center rounded-lg text-lg font-bold text-slate-600 hover:bg-slate-100" title="Зменшити">−</button>
                <button @click="resetView()" class="min-w-[3.5rem] rounded-lg px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100" title="Скинути масштаб і позицію" x-text="Math.round(zoom*100) + '%'"></button>
                <button @click="zoomBy(0.1)" class="flex h-8 w-8 items-center justify-center rounded-lg text-lg font-bold text-slate-600 hover:bg-slate-100" title="Збільшити">+</button>
            </div>
        </div>

        {{-- ===== Participants panel (teacher) ===== --}}
        @if ($isTeacher)
            {{-- Mobile backdrop --}}
            <div x-show="panel" x-cloak @click="panel = false"
                 class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"></div>

            <aside x-show="panel" x-cloak
                   class="fixed inset-y-0 right-0 z-40 w-80 max-w-[85%] overflow-y-auto border-l border-slate-200 bg-white p-4 shadow-2xl
                          lg:static lg:z-auto lg:w-72 lg:max-w-none lg:shadow-none">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-800">Учасники дошки</h2>
                    <button @click="panel = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 lg:hidden" title="Закрити">✕</button>
                </div>

                <div x-show="inviteMsg" x-cloak x-transition
                     class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-xs text-emerald-700" x-text="inviteMsg"></div>

                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-400">Запрошені</p>
                <ul class="mb-4 space-y-1">
                    <template x-for="s in invited" :key="s.id">
                        <li class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-sm text-slate-700" x-text="s.name"></span>
                            <button @click="uninviteStudent(s)" :disabled="inviteBusy"
                                    class="text-xs font-medium text-rose-500 hover:underline disabled:opacity-50" title="Скасувати доступ">✕</button>
                        </li>
                    </template>
                    <li x-show="!invited.length" class="rounded-lg border border-dashed border-slate-200 px-3 py-2 text-xs text-slate-400">Поки нікого не запрошено.</li>
                </ul>

                <div x-show="available.length" class="space-y-2">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Запросити учня</p>
                    <select x-model="invitePick" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="">Оберіть учня…</option>
                        <template x-for="s in available" :key="s.id">
                            <option :value="s.id" x-text="s.name"></option>
                        </template>
                    </select>
                    <button @click="inviteStudent()" :disabled="!invitePick || inviteBusy"
                            class="w-full rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                        <span x-text="inviteBusy ? 'Запрошуємо…' : 'Запросити'"></span>
                    </button>
                </div>
                <p x-show="!available.length" x-cloak class="text-xs text-slate-400">Усіх доступних учнів уже запрошено.</p>
            </aside>
        @endif
    </div>

    {{-- ===== Create Quiz modal ===== --}}
    <div x-show="quizModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="quizModal=false" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl" x-data="{}">
            <h3 class="text-lg font-bold text-slate-900">Новий тест</h3>
            <div class="mt-4 space-y-3">
                <input x-model="quizForm.question" placeholder="Питання"
                       class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <template x-for="(o, i) in quizForm.options" :key="i">
                    <div class="flex items-center gap-2">
                        <input type="radio" name="qans" :value="i" x-model.number="quizForm.answer"
                               class="text-emerald-600 focus:ring-emerald-400" title="Правильна відповідь">
                        <input x-model="quizForm.options[i]" :placeholder="`Варіант ${i+1}`"
                               class="flex-1 rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <button @click="quizForm.options.splice(i,1); if(quizForm.answer>=quizForm.options.length)quizForm.answer=0"
                                x-show="quizForm.options.length>2" class="text-rose-400 hover:text-rose-600">✕</button>
                    </div>
                </template>
                <div class="flex items-center justify-between">
                    <button @click="quizForm.options.push('')" x-show="quizForm.options.length<6"
                            class="text-sm font-medium text-indigo-600 hover:underline">+ Варіант</button>
                    <span class="text-xs text-slate-400">Позначте правильну відповідь кружечком</span>
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button @click="quizModal=false" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">Скасувати</button>
                <button @click="submitQuiz()" class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Додати на дошку</button>
            </div>
        </div>
    </div>

    {{-- ===== Create Flashcard modal ===== --}}
    <div x-show="cardModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="cardModal=false" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-900">Нова картка</h3>
            <div class="mt-4 space-y-3">
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-400">Лицьова сторона (питання)</label>
                    <textarea x-model="cardForm.front" rows="2" placeholder="Напр. «Столиця Франції?»"
                              class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-400">Зворотна сторона (відповідь)</label>
                    <textarea x-model="cardForm.back" rows="2" placeholder="Напр. «Париж»"
                              class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400"></textarea>
                </div>
                {{-- Background gallery for the card --}}
                <div x-data="{ side: 'front' }">
                    <div class="mb-2 flex items-center gap-2">
                        <label class="text-xs font-medium uppercase tracking-wide text-slate-400">Фон з галереї для:</label>
                        <div class="flex rounded-lg bg-slate-100 p-0.5 text-xs">
                            <button type="button" @click="side='front'" :class="side==='front' ? 'bg-white shadow' : 'text-slate-500'" class="rounded-md px-2.5 py-1 font-medium">лиця</button>
                            <button type="button" @click="side='back'" :class="side==='back' ? 'bg-white shadow' : 'text-slate-500'" class="rounded-md px-2.5 py-1 font-medium">звороту</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                        {{-- "none" option --}}
                        <button type="button"
                                @click="side==='front' ? cardForm.bg_front='' : cardForm.bg_back=''"
                                class="flex aspect-video items-center justify-center rounded-lg border-2 text-xs text-slate-400 transition"
                                :class="(side==='front' ? cardForm.bg_front : cardForm.bg_back) ? 'border-transparent bg-slate-50 hover:border-slate-300' : 'border-indigo-500 bg-slate-50'">
                            Без фону
                        </button>

                        {{-- upload own --}}
                        <button type="button" @click="$refs.cardBgInput.click()"
                                class="flex aspect-video flex-col items-center justify-center gap-0.5 rounded-lg border-2 border-dashed border-slate-300 text-xs text-slate-500 transition hover:border-indigo-300 hover:bg-indigo-50">
                            <span x-show="!cardBgUploading">⬆ Своє</span>
                            <span x-show="cardBgUploading" x-cloak class="text-indigo-500">…</span>
                        </button>
                        <input x-ref="cardBgInput" type="file" accept="image/*" class="hidden" @change="uploadCardBg($event, side)">

                        @foreach ($gallery as $name => $url)
                            <button type="button"
                                    @click="side==='front' ? cardForm.bg_front='{{ $url }}' : cardForm.bg_back='{{ $url }}'"
                                    class="relative aspect-video overflow-hidden rounded-lg border-2 transition"
                                    :class="(side==='front' ? cardForm.bg_front : cardForm.bg_back)==='{{ $url }}' ? 'border-indigo-500' : 'border-transparent hover:border-slate-300'"
                                    title="{{ $name }}">
                                <img src="{{ $url }}" alt="{{ $name }}" loading="lazy" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>

                    {{-- Preview of the currently selected bg for this side --}}
                    <div class="mt-2 flex items-center gap-2" x-show="(side==='front' ? cardForm.bg_front : cardForm.bg_back)" x-cloak>
                        <span class="text-[11px] text-slate-400">Обрано:</span>
                        <img :src="side==='front' ? cardForm.bg_front : cardForm.bg_back" class="h-8 w-14 rounded border border-slate-200 object-cover">
                        <button type="button" @click="side==='front' ? cardForm.bg_front='' : cardForm.bg_back=''" class="text-[11px] font-medium text-rose-500 hover:underline">Прибрати</button>
                    </div>

                    <p class="mt-1.5 text-[11px] text-slate-400">
                        Або вставте URL: лице
                        <input x-model="cardForm.bg_front" type="url" placeholder="https://…" class="mx-1 w-24 rounded border-slate-200 px-1.5 py-0.5 text-[11px] sm:w-28">
                        зворот
                        <input x-model="cardForm.bg_back" type="url" placeholder="https://…" class="mx-1 w-24 rounded border-slate-200 px-1.5 py-0.5 text-[11px] sm:w-28">
                    </p>
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button @click="cardModal=false" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">Скасувати</button>
                <button @click="submitCard()" class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Додати на дошку</button>
            </div>
        </div>
    </div>

    {{-- ===== Create Link modal ===== --}}
    <div x-show="linkModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="linkModal=false" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-900">Кнопка-посилання</h3>
            <div class="mt-4 space-y-3">
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-400">Текст кнопки</label>
                    <input x-model="linkForm.label" placeholder="Напр. «Наступна дошка»"
                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-400">Посилання на дошку</label>
                    <select x-model="linkForm.board_token"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="">— Оберіть дошку —</option>
                        @foreach ($otherBoards as $ob)
                            <option value="{{ $ob->token }}">{{ $ob->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <span class="h-px flex-1 bg-slate-200"></span> або зовнішнє посилання <span class="h-px flex-1 bg-slate-200"></span>
                </div>
                <input x-model="linkForm.url" type="url" placeholder="https://…"
                       class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-400">Стиль</label>
                    <select x-model="linkForm.style" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="primary">Основна (заповнена)</option>
                        <option value="secondary">Другорядна (контур)</option>
                    </select>
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button @click="linkModal=false" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">Скасувати</button>
                <button @click="submitLink()" class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Додати на дошку</button>
            </div>
        </div>
    </div>

    {{-- ===== Create Frame modal ===== --}}
    <div x-show="frameModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="frameModal=false" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Новий фрейм</h3>
                <span x-show="frameUploading" x-cloak class="text-xs text-indigo-500">Завантаження…</span>
            </div>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Назва (необов'язково)</label>
                    <input x-model="frameForm.title" placeholder="Напр. «Зона для вправ»" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Колір фону</label>
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-for="c in ['#ffffff','#f1f5f9','#fef3c7','#dcfce7','#dbeafe','#fce7f3','#1e293b']" :key="c">
                            <button type="button" @click="frameForm.color=c; frameForm.bg=''"
                                    :style="`background:${c}`"
                                    :class="frameForm.color===c && !frameForm.bg ? 'ring-2 ring-offset-1 ring-slate-900' : 'border border-slate-200'"
                                    class="h-7 w-7 rounded-full"></button>
                        </template>
                        <input type="color" x-model="frameForm.color" @input="frameForm.bg=''" class="h-7 w-9 cursor-pointer rounded border border-slate-200" title="Свій колір">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Фонове зображення (необов'язково)</label>
                    <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                        <button type="button" @click="frameForm.bg=''"
                                class="flex aspect-video items-center justify-center rounded-lg border-2 text-xs text-slate-400 transition"
                                :class="frameForm.bg ? 'border-transparent bg-slate-50 hover:border-slate-300' : 'border-indigo-500 bg-slate-50'">Без</button>
                        <button type="button" @click="$refs.frameBgInput.click()"
                                class="flex aspect-video flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 text-xs text-slate-500 transition hover:border-indigo-300 hover:bg-indigo-50">⬆ Своє</button>
                        <input x-ref="frameBgInput" type="file" accept="image/*" class="hidden" @change="uploadFrameBg($event)">
                        @foreach ($gallery as $name => $url)
                            <button type="button" @click="frameForm.bg='{{ $url }}'"
                                    class="relative aspect-video overflow-hidden rounded-lg border-2 transition"
                                    :class="frameForm.bg==='{{ $url }}' ? 'border-indigo-500' : 'border-transparent hover:border-slate-300'"
                                    title="{{ $name }}">
                                <img src="{{ $url }}" alt="{{ $name }}" loading="lazy" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <button @click="frameModal=false" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">Скасувати</button>
                <button @click="submitFrame()" class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Додати фрейм</button>
            </div>
        </div>
    </div>
</div>

@livewireScripts
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('board', (cfg) => ({
        ...cfg,
        ctx: null, canvas: null,
        strokes: [],            // {type,color,width,points:[[x,y]...],text}
        images: [],             // {id,url,x,y,w,rev} — normalized by board width
        selectedImg: null,
        imgDrag: null,          // active drag/resize gesture state
        uploading: false,
        widgets: [],            // {id,type,x,y,w,h,z,opacity,data,rev, _flip,_done,_correct,_choice}
        wgDrag: null,
        selectedWidget: null,   // id of the currently selected widget
        invited: [],            // {id,name} students with board access (teacher panel)
        available: [],          // {id,name} students that can still be invited
        invitePick: '',         // selected student id in the invite dropdown
        inviteBusy: false,
        inviteMsg: '',
        quizModal: false,
        cardModal: false,
        linkModal: false,
        frameModal: false,
        frameUploading: false,
        frameForm: { title: '', color: '#ffffff', bg: '' },
        cardBgUploading: false,
        zoom: 1,
        baseW: 0,           // unscaled stage size (board's logical pixel size)
        baseH: 0,
        panX: 0,            // stage translate offset (screen px)
        panY: 0,
        panning: false,     // currently dragging the canvas
        spaceDown: false,   // holding Space → temporary hand tool
        quizForm: { question: '', options: ['', ''], answer: 0 },
        cardForm: { front: '', back: '', bg_front: '', bg_back: '' },
        linkForm: { label: '', board_token: '', url: '', style: 'primary' },
        lastId: 0,
        clearedAt: null,
        drawing: false,
        current: null,
        start: null,
        tool: 'pen',
        color: '#1e293b',
        width: 3,
        allow: false,
        isOpen: true,
        panel: false,
        viewMode: new URLSearchParams(location.search).get('view') === '1', // ?view=1 opens in presentation mode
        textBox: { active: false, px: 0, py: 0, nx: 0, ny: 0, value: '' },
        colors: ['#1e293b', '#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ffffff'],
        selectedStroke: null,   // index of selected stroke (move tool)
        strokeDrag: null,       // active stroke drag gesture
        tools: [
            { id: 'hand',    label: 'Рука (переміщення дошки)', icon: '🖐' },
            { id: 'move',    label: 'Переміщення', icon: '✥' },
            { id: 'pen',     label: 'Олівець',  icon: '✏️' },
            { id: 'line',    label: 'Лінія',    icon: '╱' },
            { id: 'arrow',   label: 'Стрілка',  icon: '↗' },
            { id: 'rect',    label: 'Прямокутник', icon: '▭' },
            { id: 'ellipse', label: 'Овал',     icon: '◯' },
            { id: 'text',    label: 'Текст',    icon: 'T' },
        ],

        init() {
            this.allow = this.allowInit;
            this.clearedAt = this.clearedInit || null;
            this.invited = this.invitedInit || [];
            this.available = this.availableInit || [];
            this.canvas = this.$refs.canvas;
            this.ctx = this.canvas.getContext('2d');

            // Logical board size = the viewport size (coords are fractions of baseW, so
            // the normalized 0..1 range maps to the first screen — existing content
            // stays visible). The canvas BITMAP also stays viewport-sized; the camera
            // (pan+zoom) is applied to the 2D context, so you can still draw anywhere on
            // the infinite board by panning. baseW/baseH are refreshed in resizeCanvas().
            this.baseW = this.$refs.wrap.clientWidth || 1200;
            this.baseH = this.$refs.wrap.clientHeight || 800;
            this.zoom = 1;
            this.panX = 0;
            this.panY = 0;

            this.resizeCanvas();
            new ResizeObserver(() => this.resizeCanvas()).observe(this.$refs.wrap);
            // Re-render the canvas whenever the camera (pan/zoom) changes — the DOM
            // stage follows via its reactive CSS transform, the canvas needs a redraw.
            this.$watch('zoom', () => this.redraw());
            this.$watch('panX', () => this.redraw());
            this.$watch('panY', () => this.redraw());
            this.bindPointer();
            this.pollLoop();

            // Hold Space to temporarily pan (Miro/Figma convention). Ignore when typing.
            window.addEventListener('keydown', (e) => {
                if (e.code === 'Space' && !this.spaceDown && !/INPUT|TEXTAREA/.test(document.activeElement?.tagName)) {
                    this.spaceDown = true; e.preventDefault();
                }
            });
            window.addEventListener('keyup', (e) => {
                if (e.code === 'Space') this.spaceDown = false;
            });

            // Entering view mode clears any editing selection and redraws cleanly.
            this.$watch('viewMode', () => {
                this.selectedStroke = null;
                this.selectedImg = null;
                this.textBox.active = false;
                this.redraw();
            });

            // Switching to a drawing tool clears any widget/stroke selection so its
            // overlay (which would otherwise block drawing) goes away.
            this.$watch('tool', (t) => {
                if (t !== 'move' && t !== 'hand') {
                    this.selectedWidget = null;
                    this.selectedImg = null;
                }
            });
        },

        // True when the user may edit the board AND is not in presentation mode.
        // Drives the toolbar, drawing, and all editing handles. Widgets (quizzes,
        // flashcards, links) stay interactive regardless of this.
        effectiveCanDraw() {
            return !this.viewMode && this.isOpen && (this.isTeacher || this.allow);
        },

        // Should widgets capture pointer events? Only when the user isn't drawing — i.e.
        // with the select/hand tools, or when they can't draw at all (a viewer still
        // needs to click quizzes/cards/links). While a drawing tool is active, widgets
        // are click-through so strokes can be drawn on top of frames/cards.
        widgetsInteractive() {
            if (!this.effectiveCanDraw()) return true;          // viewers interact only
            return this.tool === 'move' || this.tool === 'hand';
        },

        // Logical board width: the unscaled stage width. All normalized coords use this,
        // so they're independent of the current zoom level.
        boardW() { return this.baseW || this.$refs.wrap.clientWidth; },

        // Screen pixels that correspond to 1.0 normalized unit at the current zoom —
        // used to convert pointer deltas (screen px) into normalized deltas when
        // dragging/resizing image & widget layers.
        screenW() { return this.boardW() * this.zoom; },

        /* -------------------------- Zoom & pan (Miro-style) -------------------------- */

        clampZoom(z) { return Math.min(4, Math.max(0.1, Math.round(z * 100) / 100)); },

        // Zoom toward a screen point (cx,cy relative to the viewport) so the content
        // under the cursor stays put — the natural Miro feel.
        zoomAt(newZoom, cx, cy) {
            const z = this.clampZoom(newZoom);
            if (z === this.zoom) return;
            // keep the board-point under the cursor fixed: solve for new pan
            this.panX = cx - (cx - this.panX) * (z / this.zoom);
            this.panY = cy - (cy - this.panY) * (z / this.zoom);
            this.zoom = z;
        },
        zoomBy(delta) {
            const r = this.$refs.wrap.getBoundingClientRect();
            this.zoomAt(this.zoom + delta, r.width / 2, r.height / 2); // toward centre
        },
        resetView() { this.zoom = 1; this.panX = 0; this.panY = 0; },

        onWheel(e) {
            e.preventDefault();
            const r = this.$refs.wrap.getBoundingClientRect();
            if (e.ctrlKey || e.metaKey) {
                // pinch / ctrl+wheel → zoom toward the cursor
                const factor = e.deltaY < 0 ? 1.1 : 1 / 1.1;
                this.zoomAt(this.zoom * factor, e.clientX - r.left, e.clientY - r.top);
            } else if (e.shiftKey) {
                this.panX -= e.deltaY;            // shift+wheel → horizontal pan
            } else {
                this.panX -= e.deltaX;            // trackpad horizontal
                this.panY -= e.deltaY;            // wheel → vertical pan
            }
        },

        // Start panning the whole board (hand tool, Space-drag, or middle mouse).
        onViewportPointerDown(e) {
            const wantsPan = this.tool === 'hand' || this.spaceDown || e.button === 1;
            if (!wantsPan) return;
            e.preventDefault();
            this.panning = true;
            let lx = e.clientX, ly = e.clientY;
            const move = (ev) => {
                this.panX += ev.clientX - lx;
                this.panY += ev.clientY - ly;
                lx = ev.clientX; ly = ev.clientY;
            };
            const up = () => {
                this.panning = false;
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', up);
            };
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', up);
        },

        /* ---------------------- Image upload / move / resize ---------------------- */

        async uploadImage(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.uploading = true;
            const fd = new FormData();
            fd.append('image', file);
            try {
                const res = await fetch(this.imageUploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: fd,
                });
                if (res.ok) {
                    const img = await res.json();
                    this.images.push({ id: img.id, url: img.url, x: img.x, y: img.y, w: img.w, rev: 0 });
                    this.selectedImg = img.id;
                } else {
                    alert('Не вдалося завантажити зображення.');
                }
            } catch (err) { /* ignore */ }
            this.uploading = false;
            e.target.value = '';
        },

        evtXY(e) {
            const t = e.touches ? e.touches[0] : e;
            return { x: t.clientX, y: t.clientY };
        },

        startDragImg(e, img) {
            if (!this.effectiveCanDraw()) return;
            this.selectedImg = img.id;
            const p = this.evtXY(e);
            const w = this.boardW();
            this.imgDrag = { id: img.id, mode: 'move', sx: p.x, sy: p.y, ox: img.x, oy: img.y };
            this.bindImgGesture();
        },

        startResizeImg(e, img) {
            if (!this.effectiveCanDraw()) return;
            const p = this.evtXY(e);
            this.imgDrag = { id: img.id, mode: 'resize', sx: p.x, ow: img.w };
            this.bindImgGesture();
        },

        bindImgGesture() {
            const move = (e) => {
                if (!this.imgDrag) return;
                e.preventDefault();
                const p = this.evtXY(e);
                const w = this.screenW(); // screen px per 1.0 normalized unit (zoom-aware)
                const img = this.images.find(i => i.id === this.imgDrag.id);
                if (!img) return;
                if (this.imgDrag.mode === 'move') {
                    img.x = this.imgDrag.ox + (p.x - this.imgDrag.sx) / w;
                    img.y = this.imgDrag.oy + (p.y - this.imgDrag.sy) / w;
                } else {
                    img.w = Math.max(0.05, Math.min(2, this.imgDrag.ow + (p.x - this.imgDrag.sx) / w));
                }
            };
            const up = () => {
                if (!this.imgDrag) return;
                const img = this.images.find(i => i.id === this.imgDrag.id);
                window.removeEventListener('mousemove', move);
                window.removeEventListener('mouseup', up);
                window.removeEventListener('touchmove', move);
                window.removeEventListener('touchend', up);
                this.imgDrag = null;
                if (img) this.saveImage(img);
            };
            window.addEventListener('mousemove', move);
            window.addEventListener('mouseup', up);
            window.addEventListener('touchmove', move, { passive: false });
            window.addEventListener('touchend', up);
        },

        async saveImage(img) {
            try {
                await fetch(this.imageMoveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify({ id: img.id, x: img.x, y: img.y, w: img.w }),
                });
            } catch (e) {}
        },

        async removeImage(img) {
            try {
                await fetch(this.imageDeleteUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify({ id: img.id }),
                });
            } catch (e) {}
            this.images = this.images.filter(i => i.id !== img.id);
            if (this.selectedImg === img.id) this.selectedImg = null;
        },

        /* ---------------------- Frames ---------------------- */

        openFrame() { this.frameForm = { title: '', color: '#ffffff', bg: '' }; this.frameModal = true; },

        // Frame pixel height from its normalized height (h is stored separately from w).
        frameH(wg) { return Math.max(60, (wg.h || 0.3) * this.boardW()); },

        async submitFrame() {
            const f = this.frameForm;
            await this.createWidget('frame', { title: f.title || null, color: f.color, bg: f.bg || null });
            this.frameModal = false;
        },

        async uploadFrameBg(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.frameUploading = true;
            const fd = new FormData();
            fd.append('image', file);
            try {
                const res = await fetch(this.assetUploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: fd,
                });
                if (res.ok) { this.frameForm.bg = (await res.json()).url; }
                else { alert('Не вдалося завантажити зображення.'); }
            } catch (err) {}
            this.frameUploading = false;
            e.target.value = '';
        },

        // Change a widget's layer (z-order).
        async layerWidget(wg, dir) {
            try {
                const res = await fetch(this.widgetLayerUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify({ id: wg.id, dir }),
                });
                if (res.ok) { wg.z = (await res.json()).z; this.widgets = [...this.widgets].sort((a,b)=>(a.z||0)-(b.z||0)); }
            } catch (e) {}
        },

        /* ---------------------- Widgets: quizzes & flashcards ---------------------- */

        openQuiz() { this.quizForm = { question: '', options: ['', ''], answer: 0 }; this.quizModal = true; },
        openCard() { this.cardForm = { front: '', back: '', bg_front: '', bg_back: '' }; this.cardModal = true; },
        openLink() { this.linkForm = { label: '', board_token: '', url: '', style: 'primary' }; this.linkModal = true; },

        // Widget pixel width: scales with the board but never shrinks below a usable
        // size — keeps quizzes/cards legible on narrow (mobile) screens. Capped to the
        // board width so it never overflows.
        // Pixel width of a widget = stored normalized w × board. Every widget is now
        // freely sized (separate w & h), so this is uniform across types.
        widgetW(wg) { return Math.max(40, (wg.w || 0.28) * this.boardW()); },

        // Pixel height. Uses stored h when present; otherwise a sensible default per type
        // (so old widgets created before free-height still look right).
        widgetH(wg) {
            if (wg.h) return Math.max(40, wg.h * this.boardW());
            if (wg.type === 'frame') return 0.3 * this.boardW();
            if (wg.type === 'link')  return 52;
            return this.widgetW(wg) * 0.62;
        },

        async submitQuiz() {
            const f = this.quizForm;
            if (!f.question.trim() || f.options.some(o => !o.trim())) { alert('Заповніть питання та всі варіанти.'); return; }
            await this.createWidget('quiz', { question: f.question, options: f.options, answer: f.answer });
            this.quizModal = false;
        },

        async submitCard() {
            const f = this.cardForm;
            if (!f.front.trim() || !f.back.trim()) { alert('Заповніть обидві сторони картки.'); return; }
            await this.createWidget('flashcard', { front: f.front, back: f.back, bg_front: f.bg_front || null, bg_back: f.bg_back || null });
            this.cardModal = false;
        },

        // Upload an own image as the background of the given card side.
        async uploadCardBg(e, side) {
            const file = e.target.files[0];
            if (!file) return;
            this.cardBgUploading = true;
            const fd = new FormData();
            fd.append('image', file);
            try {
                const res = await fetch(this.assetUploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: fd,
                });
                if (res.ok) {
                    const url = (await res.json()).url;
                    if (side === 'front') this.cardForm.bg_front = url; else this.cardForm.bg_back = url;
                } else { alert('Не вдалося завантажити зображення.'); }
            } catch (err) {}
            this.cardBgUploading = false;
            e.target.value = '';
        },

        async submitLink() {
            const f = this.linkForm;
            if (!f.label.trim()) { alert('Вкажіть текст кнопки.'); return; }
            if (!f.board_token && !f.url.trim()) { alert('Оберіть дошку або вкажіть URL.'); return; }
            await this.createWidget('link', { label: f.label, board_token: f.board_token || null, url: f.url || null, style: f.style });
            this.linkModal = false;
        },

        async createWidget(type, data) {
            const w = type === 'frame' ? 0.4 : (type === 'link' ? 0.22 : 0.28);
            const h = type === 'frame' ? 0.3 : null;
            // Frames are containers/backdrops → spawn them BEHIND everything so other
            // widgets stay visible and clickable on top. Everything else spawns on top.
            const z = type === 'frame'
                ? this.widgets.reduce((m, x) => Math.min(m, x.z || 0), 0) - 1
                : this.widgets.reduce((m, x) => Math.max(m, x.z || 0), 0) + 1;
            try {
                const res = await fetch(this.widgetStoreUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify({ type, x: 0.1, y: 0.12, w, h, ...data }),
                });
                if (res.ok) {
                    const j = await res.json();
                    // For links the server stores the resolved url; re-fetch on next poll.
                    const stored = { ...data };
                    if (type === 'link' && data.board_token) stored.url = stored.url || '';
                    this.widgets.push({ id: j.id, type, x: 0.1, y: 0.12, w, h, z, opacity: 1, data: stored, rev: 0, _flip: false, _done: false, _correct: false, _choice: null });
                    this.selectedWidget = j.id;
                } else {
                    const err = await res.json().catch(() => ({}));
                    alert(err.message || 'Не вдалося додати віджет.');
                }
            } catch (e) {}
        },

        // Select a widget (shows the edit overlay). Clicking empty canvas clears it.
        selectWidget(wg) { this.selectedWidget = wg.id; this.selectedStroke = null; },
        // The currently-selected widget/frame object (for the top-center action bar).
        selWidget() { return this.selectedWidget === null ? null : this.widgets.find(w => w.id === this.selectedWidget) || null; },

        // Geometry of a widget in normalized units (x,y,w,h) — h falls back to the
        // rendered default so frame-containment works even for auto-height widgets.
        wgBox(wg) {
            const h = wg.h || (this.widgetH(wg) / this.boardW());
            return { x: wg.x, y: wg.y, w: wg.w, h };
        },

        // Widgets visually inside a frame (their centre lies within the frame box) —
        // moving the frame moves them together, like grouping on a frame.
        frameChildren(frame) {
            const f = this.wgBox(frame);
            return this.widgets.filter(o => {
                if (o.id === frame.id || o.type === 'frame') return false;
                const b = this.wgBox(o);
                const cx = b.x + b.w / 2, cy = b.y + b.h / 2;
                return cx >= f.x && cx <= f.x + f.w && cy >= f.y && cy <= f.y + f.h;
            });
        },

        // Unified free resize: mode 'both' (corner, Shift = keep ratio), 'x' (width),
        // 'y' (height). Works for every widget type.
        startResize(e, wg, mode) {
            if (!this.effectiveCanDraw()) return;
            this.selectedWidget = wg.id;
            const p = this.evtXY(e);
            const start = { sx: p.x, sy: p.y, ow: wg.w, oh: wg.h || (this.widgetH(wg) / this.boardW()) };
            const ratio = start.oh / start.ow;
            const move = (ev) => {
                ev.preventDefault();
                const q = this.evtXY(ev);
                const s = this.screenW();
                const t = this.widgets.find(i => i.id === wg.id);
                if (!t) return;
                const dx = (q.x - start.sx) / s, dy = (q.y - start.sy) / s;
                if (mode === 'x' || mode === 'both') t.w = Math.max(0.05, Math.min(3, start.ow + dx));
                if (mode === 'y' || mode === 'both') t.h = Math.max(0.05, Math.min(3, start.oh + dy));
                if (mode === 'both' && ev.shiftKey) t.h = t.w * ratio; // proportional
            };
            const up = () => {
                const t = this.widgets.find(i => i.id === wg.id);
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', up);
                if (t) this.saveWidget(t);
            };
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', up);
        },

        startDragWidget(e, wg) {
            if (!this.effectiveCanDraw()) return;
            this.selectedWidget = wg.id;
            this.selectedStroke = null;
            const p = this.evtXY(e);
            // For a frame, capture its children so they travel with it.
            const kids = wg.type === 'frame'
                ? this.frameChildren(wg).map(k => ({ id: k.id, ox: k.x, oy: k.y }))
                : [];
            this.wgDrag = { id: wg.id, sx: p.x, sy: p.y, ox: wg.x, oy: wg.y, kids, moved: false };
            const move = (ev) => {
                if (!this.wgDrag) return;
                ev.preventDefault();
                const q = this.evtXY(ev);
                const w = this.screenW();
                const dx = (q.x - this.wgDrag.sx) / w, dy = (q.y - this.wgDrag.sy) / w;
                const t = this.widgets.find(i => i.id === this.wgDrag.id);
                if (!t) return;
                this.wgDrag.moved = true;
                t.x = this.wgDrag.ox + dx;
                t.y = this.wgDrag.oy + dy;
                this.wgDrag.kids.forEach(k => {
                    const c = this.widgets.find(i => i.id === k.id);
                    if (c) { c.x = k.ox + dx; c.y = k.oy + dy; }
                });
            };
            const up = () => {
                const t = this.widgets.find(i => i.id === this.wgDrag?.id);
                const kids = this.wgDrag?.kids || [];
                const moved = this.wgDrag?.moved;
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', up);
                this.wgDrag = null;
                if (moved && t) this.saveWidget(t);
                if (moved) kids.forEach(k => { const c = this.widgets.find(i => i.id === k.id); if (c) this.saveWidget(c); });
            };
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', up);
        },

        async saveWidget(wg) {
            try {
                await fetch(this.widgetMoveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify({ id: wg.id, x: wg.x, y: wg.y, w: wg.w, h: wg.h ?? null }),
                });
            } catch (e) {}
        },

        async saveOpacity(wg) {
            try {
                await fetch(this.widgetStyleUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify({ id: wg.id, opacity: wg.opacity ?? 1 }),
                });
            } catch (e) {}
        },

        async removeWidget(wg) {
            try {
                await fetch(this.widgetDeleteUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify({ id: wg.id }),
                });
            } catch (e) {}
            this.widgets = this.widgets.filter(i => i.id !== wg.id);
            if (this.selectedWidget === wg.id) this.selectedWidget = null;
        },

        // Quiz interaction — answer is verified server-side (hidden from students).
        async answerQuiz(wg, i) {
            if (wg._done) return;
            wg._choice = i;
            try {
                const res = await fetch(this.widgetCheckUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify({ id: wg.id, choice: i }),
                });
                if (res.ok) {
                    const j = await res.json();
                    wg._correct = j.correct;
                    wg._answer = j.answer;
                    wg._done = true;
                }
            } catch (e) {}
        },
        resetQuiz(wg) { wg._done = false; wg._correct = false; wg._choice = null; wg._answer = undefined; },

        quizOptClass(wg, i) {
            if (!wg._done) return 'border-slate-200 bg-white text-slate-700 hover:border-indigo-300 hover:bg-indigo-50';
            if (i === wg._answer) return 'border-emerald-300 bg-emerald-50 text-emerald-700';
            if (i === wg._choice) return 'border-rose-300 bg-rose-50 text-rose-700';
            return 'border-slate-200 text-slate-400';
        },
        quizBadgeClass(wg, i) {
            if (wg._done && i === wg._answer) return 'border-emerald-500 bg-emerald-500 text-white';
            if (wg._done && i === wg._choice) return 'border-rose-500 bg-rose-500 text-white';
            return 'border-slate-300 text-slate-400';
        },
        quizBadge(wg, i) {
            if (wg._done && i === wg._answer) return '✓';
            if (wg._done && i === wg._choice && i !== wg._answer) return '✕';
            return String.fromCharCode(65 + i);
        },

        // Logical board box (unscaled). Used for normalization.
        rect() { return { width: this.baseW, height: this.baseH }; },
        // Normalize BOTH axes by width so shapes keep their aspect ratio.
        toPx(p) { return [p[0] * this.baseW, p[1] * this.baseW]; },
        textFontPx() { return Math.max(12, this.width * 6); },

        drawOne(s) {
            const ctx = this.ctx;
            ctx.strokeStyle = s.color;
            ctx.fillStyle = s.color;
            ctx.lineWidth = s.width;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            const pts = s.points || [];
            if (s.type === 'text') {
                if (!pts.length) return;
                const [x, y] = this.toPx(pts[0]);
                ctx.font = `600 ${Math.max(12, s.width * 6)}px figtree, sans-serif`;
                ctx.textBaseline = 'top';
                ctx.fillText(s.text || '', x, y);
                return;
            }
            if (pts.length === 0) return;
            if (s.type === 'pen') {
                ctx.beginPath();
                const [x0, y0] = this.toPx(pts[0]);
                ctx.moveTo(x0, y0);
                for (let i = 1; i < pts.length; i++) { const [x, y] = this.toPx(pts[i]); ctx.lineTo(x, y); }
                if (pts.length === 1) ctx.lineTo(x0 + 0.1, y0 + 0.1);
                ctx.stroke();
                return;
            }
            // shapes use two points: start + end
            const [ax, ay] = this.toPx(pts[0]);
            const [bx, by] = this.toPx(pts[pts.length - 1]);
            ctx.beginPath();
            if (s.type === 'line') { ctx.moveTo(ax, ay); ctx.lineTo(bx, by); ctx.stroke(); }
            else if (s.type === 'rect') { ctx.strokeRect(ax, ay, bx - ax, by - ay); }
            else if (s.type === 'ellipse') {
                ctx.ellipse((ax + bx) / 2, (ay + by) / 2, Math.abs(bx - ax) / 2, Math.abs(by - ay) / 2, 0, 0, Math.PI * 2);
                ctx.stroke();
            }
            else if (s.type === 'arrow') {
                // shaft
                ctx.moveTo(ax, ay); ctx.lineTo(bx, by); ctx.stroke();
                // head — sized to line width, clamped so short arrows still look right
                const ang = Math.atan2(by - ay, bx - ax);
                const head = Math.max(8, s.width * 3.5);
                ctx.beginPath();
                ctx.moveTo(bx, by);
                ctx.lineTo(bx - head * Math.cos(ang - Math.PI / 6), by - head * Math.sin(ang - Math.PI / 6));
                ctx.moveTo(bx, by);
                ctx.lineTo(bx - head * Math.cos(ang + Math.PI / 6), by - head * Math.sin(ang + Math.PI / 6));
                ctx.stroke();
            }
        },

        // Size the canvas bitmap to the viewport (NOT the whole board) — keeps it small
        // and within browser canvas limits regardless of how big the board is.
        resizeCanvas() {
            const vw = this.$refs.wrap.clientWidth;
            const vh = this.$refs.wrap.clientHeight;
            if (!vw || !vh) return;
            // Keep the logical board sized to the viewport so 0..1 coords map to the
            // first screen (content stays where it was drawn).
            this.baseW = vw;
            this.baseH = vh;
            const dpr = window.devicePixelRatio || 1;
            this.canvas.width = Math.round(vw * dpr);
            this.canvas.height = Math.round(vh * dpr);
            this.redraw();
        },

        // Coalesce redraws to one per animation frame — fast mousemove during drawing
        // fires far more often than the screen refreshes, so painting every event just
        // stutters. This keeps fast freehand/shape drawing smooth.
        scheduleRedraw() {
            if (this._rafPending) return;
            this._rafPending = true;
            requestAnimationFrame(() => { this._rafPending = false; this.redraw(); });
        },

        redraw() {
            const ctx = this.ctx;
            const dpr = window.devicePixelRatio || 1;
            // Clear the raw bitmap.
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            // Apply the camera: logical px -> screen px = pan + logical*zoom, then *dpr.
            ctx.setTransform(dpr * this.zoom, 0, 0, dpr * this.zoom, dpr * this.panX, dpr * this.panY);
            this.strokes.forEach((s, i) => {
                this.drawOne(s);
                if (i === this.selectedStroke) this.drawSelection(s);
            });
            if (this.current) this.drawOne(this.current);
        },

        // Axis-aligned bounding box of a stroke in normalized (width) units.
        bbox(s) {
            const pts = s.points || [];
            if (!pts.length) return null;
            if (s.type === 'text') {
                // Measure the real rendered width so the click target matches the glyphs.
                const fontPx = Math.max(12, s.width * 6);
                this.ctx.save();
                this.ctx.font = `600 ${fontPx}px figtree, sans-serif`;
                const wPx = this.ctx.measureText(s.text || '').width;
                this.ctx.restore();
                const w = Math.max(wPx, 12) / this.baseW;          // normalized width
                const h = (fontPx * 1.3) / this.baseW;             // normalized height
                return { x0: pts[0][0], y0: pts[0][1], x1: pts[0][0] + w, y1: pts[0][1] + h };
            }
            let x0 = Infinity, y0 = Infinity, x1 = -Infinity, y1 = -Infinity;
            pts.forEach(p => { x0 = Math.min(x0, p[0]); y0 = Math.min(y0, p[1]); x1 = Math.max(x1, p[0]); y1 = Math.max(y1, p[1]); });
            return { x0, y0, x1, y1 };
        },

        drawSelection(s) {
            const b = this.bbox(s);
            if (!b) return;
            const pad = 6;
            const [ax, ay] = this.toPx([b.x0, b.y0]);
            const [bx, by] = this.toPx([b.x1, b.y1]);
            this.ctx.save();
            this.ctx.strokeStyle = '#6366f1';
            this.ctx.lineWidth = 1.5;
            this.ctx.setLineDash([5, 4]);
            this.ctx.strokeRect(ax - pad, ay - pad, (bx - ax) + pad * 2, (by - ay) + pad * 2);
            this.ctx.restore();
        },

        // Find topmost stroke whose bbox contains the normalized point p.
        // Tolerance scales with zoom so thin lines / text stay easy to grab.
        hitStroke(p) {
            const tol = 18 / (this.boardW() * this.zoom);
            for (let i = this.strokes.length - 1; i >= 0; i--) {
                const b = this.bbox(this.strokes[i]);
                if (!b) continue;
                if (p[0] >= b.x0 - tol && p[0] <= b.x1 + tol && p[1] >= b.y0 - tol && p[1] <= b.y1 + tol) {
                    return i;
                }
            }
            return null;
        },

        // Topmost frame containing point p (frames are click-through DOM behind the
        // canvas, so the move tool picks them here). Returns the widget or null.
        hitFrame(p) {
            const frames = this.widgets.filter(w => w.type === 'frame')
                .sort((a, b) => (b.z || 0) - (a.z || 0)); // top first
            for (const f of frames) {
                const b = this.wgBox(f);
                if (p[0] >= b.x && p[0] <= b.x + b.w && p[1] >= b.y && p[1] <= b.y + b.h) return f;
            }
            return null;
        },

        pos(e) {
            // Screen point -> logical board point (inverse camera) -> normalized by baseW.
            // logical = (screen - viewportOrigin - pan) / zoom
            const r = this.$refs.wrap.getBoundingClientRect();
            const t = e.touches ? e.touches[0] : e;
            const lx = (t.clientX - r.left - this.panX) / this.zoom;
            const ly = (t.clientY - r.top  - this.panY) / this.zoom;
            return [lx / this.baseW, ly / this.baseW];
        },

        bindPointer() {
            const down = (e) => {
                // Panning gestures own the pointer — never draw while panning the board.
                if (this.tool === 'hand' || this.spaceDown || this.panning || e.button === 1) return;
                if (!this.effectiveCanDraw()) return;
                this.selectedImg = null;
                this.selectedWidget = null; // clicking empty canvas deselects widgets
                const p = this.pos(e);

                // Move tool: first try a stroke under the cursor; if none, a frame
                // (frames render behind the canvas, so they're picked via hit-test here).
                if (this.tool === 'move') {
                    e.preventDefault();
                    const hit = this.hitStroke(p);
                    if (hit !== null) {
                        this.selectedStroke = hit;
                        this.selectedWidget = null;
                        this.strokeDrag = { stroke: this.strokes[hit], sx: p[0], sy: p[1], moved: false };
                        this.redraw();
                        return;
                    }
                    const frame = this.hitFrame(p);
                    this.selectedStroke = null;
                    this.selectedWidget = frame ? frame.id : null;
                    if (frame) this.startDragWidget(e, frame);
                    this.redraw();
                    return;
                }

                this.selectedStroke = null;
                if (this.tool === 'text') { this.openText(e); return; }
                e.preventDefault();
                this.drawing = true;
                this.start = p;
                this.current = { type: this.tool, color: this.color, width: this.width, points: [p] };
            };
            const move = (e) => {
                // Dragging an existing stroke (move tool).
                if (this.strokeDrag) {
                    e.preventDefault();
                    const p = this.pos(e);
                    const dx = p[0] - this.strokeDrag.sx;
                    const dy = p[1] - this.strokeDrag.sy;
                    const s = this.strokeDrag.stroke;
                    if (s) {
                        s.points = s.points.map(pt => [pt[0] + dx, pt[1] + dy]);
                        this.strokeDrag.sx = p[0];
                        this.strokeDrag.sy = p[1];
                        this.strokeDrag.moved = true;
                        this.scheduleRedraw();
                    }
                    return;
                }
                if (!this.drawing) return;
                e.preventDefault();
                const p = this.pos(e);
                if (this.tool === 'pen') this.current.points.push(p);
                else this.current.points = [this.start, p]; // shapes: keep start+current
                this.scheduleRedraw();
            };
            const up = () => {
                // Finish a stroke drag -> persist new absolute position.
                if (this.strokeDrag) {
                    const s = this.strokeDrag.stroke;
                    const moved = this.strokeDrag.moved;
                    this.strokeDrag = null;
                    if (s && s.id && moved) {
                        s._dirtyUntil = Date.now() + 2500; // ignore stale poll echoes briefly
                        this.saveStrokePosition(s);
                    }
                    return;
                }
                if (!this.drawing) return;
                this.drawing = false;
                if (this.current && this.current.points.length) {
                    this.strokes.push(this.current);
                    this.send(this.current);
                }
                this.current = null;
            };
            // Pointer events (mouse + touch unified). The press starts on the canvas,
            // but move/up are bound to WINDOW so the in-progress shape keeps following
            // the cursor even when it leaves the canvas or passes over a widget layer.
            this.canvas.addEventListener('pointerdown', down);
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', up);
        },

        openText(e) {
            const p = this.pos(e);
            const [px, py] = this.toPx(p);
            this.textBox = { active: true, px, py, nx: p[0], ny: p[1], value: '' };
            this.$nextTick(() => this.$refs.textInput && this.$refs.textInput.focus());
        },
        commitText() {
            if (!this.textBox.active) return;
            const val = this.textBox.value.trim();
            this.textBox.active = false;
            if (!val) return;
            const s = { type: 'text', color: this.color, width: this.width, points: [[this.textBox.nx, this.textBox.ny]], text: val };
            this.strokes.push(s);
            this.send(s);
            this.redraw();
        },
        cancelText() { this.textBox.active = false; this.textBox.value = ''; },

        csrf() { return document.querySelector('meta[name=csrf-token]').content; },

        /* ---------------------- Invite students (AJAX, no reload) ---------------------- */
        async inviteStudent() {
            const id = parseInt(this.invitePick, 10);
            if (!id || this.inviteBusy) return;
            this.inviteBusy = true;
            try {
                const res = await fetch(this.inviteUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify({ student_id: id }),
                });
                if (res.ok) {
                    const { student } = await res.json();
                    this.available = this.available.filter(s => s.id !== student.id);
                    if (!this.invited.some(s => s.id === student.id)) this.invited.push(student);
                    this.invitePick = '';
                    this.flashInvite(`Учня ${student.name} запрошено.`);
                } else {
                    this.flashInvite('Не вдалося запросити учня.');
                }
            } catch (e) { this.flashInvite('Помилка мережі.'); }
            this.inviteBusy = false;
        },
        async uninviteStudent(student) {
            if (this.inviteBusy) return;
            this.inviteBusy = true;
            try {
                const res = await fetch(this.uninviteUrlBase.replace(/\/0$/, '/' + student.id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                });
                if (res.ok) {
                    this.invited = this.invited.filter(s => s.id !== student.id);
                    if (!this.available.some(s => s.id === student.id)) this.available.push(student);
                    this.flashInvite(`Доступ для ${student.name} скасовано.`);
                } else {
                    this.flashInvite('Не вдалося скасувати доступ.');
                }
            } catch (e) { this.flashInvite('Помилка мережі.'); }
            this.inviteBusy = false;
        },
        flashInvite(msg) {
            this.inviteMsg = msg;
            clearTimeout(this._inviteTimer);
            this._inviteTimer = setTimeout(() => { this.inviteMsg = ''; }, 3000);
        },

        async send(s) {
            try {
                const res = await fetch(this.drawUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify({ type: s.type, color: s.color, width: s.width, points: s.points, text: s.text ?? null }),
                });
                if (res.ok) { const j = await res.json(); if (j.id) s.id = j.id; }
                return;
            } catch (e) {}
        },

        async saveStrokePosition(s) {
            // The drag/resize already updated s locally; persist points (+ width for text).
            try {
                const body = { id: s.id, points: s.points };
                if (s.type === 'text') body.width = s.width;
                await fetch(this.strokeMoveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify(body),
                });
            } catch (e) {}
        },

        async deleteSelectedStroke() {
            if (this.selectedStroke === null) return;
            const s = this.strokes[this.selectedStroke];
            if (s && s.id) {
                try {
                    await fetch(this.strokeDeleteUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                        body: JSON.stringify({ id: s.id }),
                    });
                } catch (e) {}
            }
            this.strokes.splice(this.selectedStroke, 1);
            this.selectedStroke = null;
            this.redraw();
        },

        // Reorder the selected stroke. Strokes are painted in array order, so moving an
        // item to the end draws it on top; to the front draws it underneath everything.
        strokeToFront() {
            if (this.selectedStroke === null) return;
            const [s] = this.strokes.splice(this.selectedStroke, 1);
            this.strokes.push(s);
            this.selectedStroke = this.strokes.length - 1;
            this.redraw();
        },
        strokeToBack() {
            if (this.selectedStroke === null) return;
            const [s] = this.strokes.splice(this.selectedStroke, 1);
            this.strokes.unshift(s);
            this.selectedStroke = 0;
            this.redraw();
        },

        /* ---- Selected-stroke resize (works for every stroke type) ---- */

        // Bounding box of the selected stroke in LOGICAL px (camera-space, before pan/zoom).
        strokeBoxPx() {
            if (this.selectedStroke === null) return null;
            const s = this.strokes[this.selectedStroke];
            if (!s) return null;
            const b = this.bbox(s);
            if (!b) return null;
            return { x: b.x0 * this.baseW, y: b.y0 * this.baseW, w: (b.x1 - b.x0) * this.baseW, h: (b.y1 - b.y0) * this.baseW };
        },
        // Same box but in SCREEN px (viewport-relative): apply the camera (zoom + pan).
        // Used by the screen-space resize overlay so handles keep a constant size.
        strokeBoxScreen() {
            const b = this.strokeBoxPx();
            if (!b) return null;
            return {
                x: b.x * this.zoom + this.panX,
                y: b.y * this.zoom + this.panY,
                w: Math.max(8, b.w * this.zoom),
                h: Math.max(8, b.h * this.zoom),
            };
        },
        strokeBoxScreenStyle() {
            const b = this.strokeBoxScreen();
            if (!b) return 'display:none';
            return `left:${b.x}px; top:${b.y}px; width:${b.w}px; height:${b.h}px;`;
        },

        // Scale all points of the selected stroke around its top-left corner.
        startStrokeResize(e, mode) {
            if (this.selectedStroke === null) return;
            const s = this.strokes[this.selectedStroke];
            if (!s) return;
            const b0 = this.bbox(s);
            const ox = b0.x0, oy = b0.y0;                 // anchor (top-left, normalized)
            const w0 = Math.max(b0.x1 - b0.x0, 1e-4);
            const h0 = Math.max(b0.y1 - b0.y0, 1e-4);
            const orig = s.points.map(pt => [pt[0], pt[1]]); // snapshot
            const origWidth = s.width;
            const start = this.evtXY(e);
            const sw = this.screenW();

            const move = (ev) => {
                ev.preventDefault();
                const q = this.evtXY(ev);
                const dx = (q.x - start.x) / sw, dy = (q.y - start.y) / sw;
                let fx = (mode === 'y') ? 1 : Math.max(0.05, (w0 + dx) / w0);
                let fy = (mode === 'x') ? 1 : Math.max(0.05, (h0 + dy) / h0);
                if (mode === 'both' && ev.shiftKey) { fy = fx; } // proportional
                if (s.type === 'text') {
                    // Text size is driven by `width` (font), not by point spread —
                    // scale the font by the larger factor and keep its anchor point.
                    const f = Math.max(fx, fy);
                    s.width = Math.max(1, Math.min(200, Math.round(origWidth * f)));
                } else {
                    s.points = orig.map(pt => [ox + (pt[0] - ox) * fx, oy + (pt[1] - oy) * fy]);
                }
                this.scheduleRedraw();
            };
            const up = () => {
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', up);
                if (s.id) { s._dirtyUntil = Date.now() + 2500; this.saveStrokePosition(s); }
            };
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', up);
        },

        // Are we mid-interaction? While the user draws / drags / pans / types we must
        // NOT poll-redraw (it would interrupt the in-progress shape) nor fight their edits.
        interacting() {
            return this.drawing || !!this.strokeDrag || !!this.wgDrag || !!this.imgDrag
                || this.panning || this.textBox.active;
        },

        // Self-scheduling poll loop: never overlaps requests (waits for the previous one)
        // and pauses entirely while the user is interacting, so fast drawing stays smooth.
        async pollLoop() {
            if (!this.interacting()) {
                await this.poll();
            }
            // Idle ~1.2s; a touch quicker right after the user stops, otherwise relaxed.
            setTimeout(() => this.pollLoop(), this.interacting() ? 400 : 1200);
        },

        async poll() {
            try {
                const res = await fetch(this.strokesUrl + '?since=' + this.lastId, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                this.isOpen = data.is_open;
                if (!this.isTeacher) this.allow = data.students_can_draw;

                // Sync board background (don't fight the teacher's in-flight change).
                if (!this.bgUploading && data.background !== undefined && data.background !== this.background) {
                    this.background = data.background;
                }
                if (data.background_mode && data.background_mode !== this.bgMode) {
                    this.bgMode = data.background_mode;
                }

                // Detect clear: cleared_at advanced -> wipe local canvas + images.
                if (data.cleared_at && data.cleared_at !== this.clearedAt) {
                    this.clearedAt = data.cleared_at;
                    this.strokes = [];
                    this.images = [];
                    this.widgets = [];
                    this.selectedImg = null;
                    this.lastId = 0;
                    this.redraw();
                    return; // next poll fetches fresh strokes from 0
                }

                let dirty = false;

                if (data.strokes.length) {
                    data.strokes.forEach(s => {
                        // Skip strokes we created locally that already have this id.
                        if (s.id && this.strokes.some(x => x.id === s.id)) return;
                        this.strokes.push({
                            id: s.id, type: s.type, color: s.color, width: s.width, points: s.points, text: s.text,
                        });
                    });
                    this.lastId = data.last_id;
                    dirty = true;
                }

                // Sync repositions made by other participants. Skip a stroke that we're
                // dragging right now, or that we just moved (its save may still be in
                // flight, so a stale echo would otherwise snap it back).
                if (Array.isArray(data.moved)) {
                    const now = Date.now();
                    data.moved.forEach(m => {
                        const local = this.strokes.find(x => x.id === m.id);
                        if (!local) return;
                        const dragging = this.strokeDrag && this.strokeDrag.stroke?.id === m.id;
                        const fresh = local._dirtyUntil && local._dirtyUntil > now;
                        if (!dragging && !fresh && JSON.stringify(local.points) !== JSON.stringify(m.points)) {
                            local.points = m.points;
                            dirty = true;
                        }
                    });
                }

                // Prune strokes deleted by others (only those that have a server id).
                if (Array.isArray(data.ids)) {
                    const alive = new Set(data.ids);
                    const before = this.strokes.length;
                    this.strokes = this.strokes.filter(x => !x.id || alive.has(x.id));
                    if (this.strokes.length !== before) { this.selectedStroke = null; dirty = true; }
                }

                if (dirty) this.redraw();

                // Sync images (full list each poll). Don't clobber an image the user
                // is actively dragging/resizing right now.
                if (Array.isArray(data.images)) {
                    const incoming = {};
                    data.images.forEach(i => incoming[i.id] = i);
                    // remove images deleted by others
                    this.images = this.images.filter(i => incoming[i.id] || (this.imgDrag && this.imgDrag.id === i.id));
                    data.images.forEach(srv => {
                        const local = this.images.find(i => i.id === srv.id);
                        if (!local) {
                            this.images.push({ id: srv.id, url: srv.url, x: srv.x, y: srv.y, w: srv.w, rev: srv.rev });
                        } else if ((!this.imgDrag || this.imgDrag.id !== srv.id) && srv.rev !== local.rev) {
                            local.x = srv.x; local.y = srv.y; local.w = srv.w; local.rev = srv.rev;
                        }
                    });
                }

                // Sync widgets (quizzes / flashcards). Preserve the local interactive
                // state (_flip, _done, ...) and skip a widget being dragged right now.
                if (Array.isArray(data.widgets)) {
                    const incoming = {};
                    data.widgets.forEach(w => incoming[w.id] = w);
                    this.widgets = this.widgets.filter(w => incoming[w.id] || (this.wgDrag && this.wgDrag.id === w.id) || this.selectedWidget === w.id);
                    data.widgets.forEach(srv => {
                        const local = this.widgets.find(w => w.id === srv.id);
                        if (!local) {
                            this.widgets.push({ id: srv.id, type: srv.type, x: srv.x, y: srv.y, w: srv.w, h: srv.h, z: srv.z, opacity: srv.opacity, data: srv.data, rev: srv.rev, _flip: false, _done: false, _correct: false, _choice: null });
                        } else {
                            // Don't clobber a widget the user is dragging or has selected
                            // (they may be mid-edit: moving/resizing/opacity).
                            const busy = (this.wgDrag && this.wgDrag.id === srv.id) || this.selectedWidget === srv.id;
                            if (!busy && srv.rev !== local.rev) {
                                local.x = srv.x; local.y = srv.y; local.w = srv.w; local.h = srv.h; local.z = srv.z; local.opacity = srv.opacity; local.data = srv.data; local.rev = srv.rev;
                            }
                        }
                    });
                    this.widgets.sort((a, b) => (a.z || 0) - (b.z || 0));
                }

                // Wipe widgets too when board cleared (handled above via early return only
                // resets strokes/images — widgets list is rebuilt from this poll anyway).
            } catch (e) {}
        },

        async toggleAllow() {
            this.allow = !this.allow;
            await fetch(this.permissionUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
                body: JSON.stringify({ allow: this.allow }),
            });
        },

        async clearBoard() {
            if (!confirm('Очистити дошку для всіх?')) return;
            const res = await fetch(this.clearUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' } });
            const data = await res.json().catch(() => ({}));
            if (data.cleared_at) this.clearedAt = data.cleared_at;
            this.strokes = []; this.lastId = 0; this.redraw();
        },
    }));
});
</script>
</body>
</html>
