<!DOCTYPE html>
<html lang="uk" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HClass · Платформа онлайн-навчання</title>
    <meta name="description" content="HClass — сучасна платформа онлайн-навчання: власні курси, інтерактивна дошка, уроки в реальному часі для вчителів, учнів і батьків.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-white font-sans text-slate-800 antialiased">

{{-- ===================== Navbar ===================== --}}
<header x-data="{ scrolled: false, menu: false }" @scroll.window="scrolled = window.scrollY > 20"
        class="fixed inset-x-0 top-0 z-50 transition"
        :class="scrolled ? 'border-b border-slate-200 bg-white/90 backdrop-blur' : ''">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
        <a href="#" class="flex items-center gap-2">
            <x-application-logo class="h-9 w-9" />
            <span class="text-xl font-extrabold text-slate-900">HClass</span>
        </a>
        <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
            <a href="#features" class="hover:text-slate-900">Можливості</a>
            <a href="#how" class="hover:text-slate-900">Як це працює</a>
            <a href="#audience" class="hover:text-slate-900">Для кого</a>
            <a href="#reviews" class="hover:text-slate-900">Відгуки</a>
        </nav>
        <div class="flex items-center gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-700">Мій кабінет</a>
            @else
                <a href="{{ route('login') }}" class="hidden rounded-xl px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 sm:block">Увійти</a>
                <a href="{{ route('register') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-700">Спробувати</a>
            @endauth
        </div>
    </div>
</header>

{{-- ===================== Hero ===================== --}}
<section class="relative overflow-hidden pt-32 pb-20">
    {{-- decorative blobs --}}
    <div class="pointer-events-none absolute -left-40 top-10 h-96 w-96 rounded-full bg-indigo-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-32 top-40 h-96 w-96 rounded-full bg-violet-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute left-1/3 -bottom-20 h-72 w-72 rounded-full bg-emerald-200/40 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-6 lg:grid-cols-2">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-4 py-1.5 text-sm font-medium text-indigo-700">
                <span class="flex h-2 w-2 rounded-full bg-indigo-500"></span>
                Нова ера онлайн-освіти
            </span>
            <h1 class="mt-6 text-5xl font-black leading-[1.05] tracking-tight text-slate-900 sm:text-6xl">
                Навчайся і викладай у
                <span class="relative whitespace-nowrap">
                    <span class="bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-600 bg-clip-text text-transparent">одному просторі</span>
                    <svg class="absolute -bottom-2 left-0 w-full" height="12" viewBox="0 0 200 12" fill="none"><path d="M2 8c40-6 158-6 196 0" stroke="#a78bfa" stroke-width="3" stroke-linecap="round"/></svg>
                </span>
            </h1>
            <p class="mt-6 max-w-xl text-lg text-slate-500">
                Створюйте власні курси з тестами та матеріалами, проводьте уроки на інтерактивній дошці в реальному часі та відстежуйте прогрес — усе на одній платформі.
            </p>
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-indigo-500/30 transition hover:bg-indigo-700">
                    Почати безкоштовно
                    <svg class="h-5 w-5 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="#how" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-base font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-5 w-5 text-indigo-600" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    Як це працює
                </a>
            </div>
            <div class="mt-10 flex items-center gap-6">
                <div class="flex -space-x-3">
                    @foreach (['from-indigo-400 to-indigo-600','from-emerald-400 to-emerald-600','from-amber-400 to-amber-600','from-rose-400 to-rose-600'] as $g)
                        <div class="h-10 w-10 rounded-full border-2 border-white bg-gradient-to-br {{ $g }}"></div>
                    @endforeach
                </div>
                <div>
                    <div class="flex text-amber-400">★★★★★</div>
                    <p class="text-sm text-slate-500"><span class="font-semibold text-slate-700">5 000+</span> учнів уже з нами</p>
                </div>
            </div>
        </div>

        {{-- Hero mock card --}}
        <div class="relative">
            <div class="absolute inset-0 -rotate-6 rounded-3xl bg-gradient-to-br from-indigo-200 to-violet-200"></div>
            <div class="relative rounded-3xl border border-slate-200 bg-white p-5 shadow-2xl shadow-slate-300/50">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="h-3 w-3 rounded-full bg-rose-400"></span>
                    <span class="h-3 w-3 rounded-full bg-amber-400"></span>
                    <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
                    <span class="ml-2 text-xs text-slate-400">hclass.app/course/math</span>
                </div>
                <div class="mt-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-lg font-bold text-slate-900">Основи математики</p>
                            <p class="text-xs text-slate-400">Урок 3 із 12 · Дроби</p>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Активний</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full w-2/3 rounded-full bg-gradient-to-r from-indigo-500 to-violet-500"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-indigo-50 p-3">
                            <p class="text-2xl font-bold text-indigo-600">8</p>
                            <p class="text-xs text-indigo-500">уроків пройдено</p>
                        </div>
                        <div class="rounded-xl bg-violet-50 p-3">
                            <p class="text-2xl font-bold text-violet-600">92%</p>
                            <p class="text-xs text-violet-500">середній бал</p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 p-3">
                        <p class="mb-2 text-xs font-semibold text-slate-500">Тест: оберіть правильну відповідь</p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                                <span class="flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[10px] text-white">✓</span> 1/2 + 1/4 = 3/4
                            </div>
                            <div class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-500">1/2 + 1/4 = 2/6</div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- floating badge --}}
            <div class="absolute -bottom-5 -left-5 flex items-center gap-2 rounded-2xl border border-slate-100 bg-white px-4 py-3 shadow-xl">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white">✓</div>
                <div><p class="text-sm font-bold text-slate-900">Тест складено!</p><p class="text-xs text-slate-400">+50 балів</p></div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== Logos / trust bar ===================== --}}
<section class="border-y border-slate-100 bg-slate-50/60 py-8">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-center gap-x-12 gap-y-4 px-6 text-sm font-semibold uppercase tracking-wide text-slate-400">
        <span>Нам довіряють:</span>
        <span>EduCenter</span><span>SmartSchool</span><span>BrightKids</span><span>LinguaPro</span><span>CodeAcademy</span>
    </div>
</section>

{{-- ===================== Stats ===================== --}}
<section class="mx-auto max-w-7xl px-6 py-20">
    <div class="grid grid-cols-2 gap-6 lg:grid-cols-4">
        @foreach ([
            ['5 000+','Активних учнів','from-indigo-500 to-indigo-600'],
            ['300+','Власних курсів','from-violet-500 to-violet-600'],
            ['15 000','Проведених уроків','from-emerald-500 to-emerald-600'],
            ['98%','Рекомендують нас','from-amber-500 to-amber-600'],
        ] as $s)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                <p class="bg-gradient-to-br {{ $s[2] }} bg-clip-text text-4xl font-black text-transparent">{{ $s[0] }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ $s[1] }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ===================== Features ===================== --}}
<section id="features" class="mx-auto max-w-7xl px-6 py-20">
    <div class="mx-auto max-w-2xl text-center">
        <span class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Можливості</span>
        <h2 class="mt-3 text-4xl font-black tracking-tight text-slate-900">Усе, що потрібно для навчання</h2>
        <p class="mt-4 text-lg text-slate-500">Потужні інструменти для викладачів і зручний досвід для учнів.</p>
    </div>

    <div class="mt-14 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['🎨','Інтерактивна дошка','Малюйте, додавайте фігури й текст у реальному часі. Запрошуйте учнів і керуйте доступом.','from-indigo-500 to-indigo-600'],
            ['📚','Конструктор курсів','Створюйте курси з тексту, зображень, тестів і кнопок — без жодного рядка коду.','from-violet-500 to-violet-600'],
            ['🎥','Уроки наживо','Плануйте та проводьте уроки, відстежуйте статуси й прогрес кожного учня.','from-emerald-500 to-emerald-600'],
            ['📝','Тести й завдання','Вбудовані тести з автоматичною перевіркою прямо в матеріалах курсу.','from-amber-500 to-amber-600'],
            ['👨‍👩‍👧','Доступ для батьків','Батьки бачать прогрес дітей у зручному особистому кабінеті.','from-rose-500 to-rose-600'],
            ['🛡️','Ролі та безпека','Чітке розмежування доступу: адмін, вчитель, учень, батьки.','from-sky-500 to-sky-600'],
        ] as $f)
            <div class="group rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br {{ $f[3] }} text-3xl shadow-lg">{{ $f[0] }}</div>
                <h3 class="mt-5 text-xl font-bold text-slate-900">{{ $f[1] }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $f[2] }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ===================== How it works ===================== --}}
<section id="how" class="bg-slate-900 py-24">
    <div class="mx-auto max-w-7xl px-6">
        <div class="mx-auto max-w-2xl text-center">
            <span class="text-sm font-semibold uppercase tracking-wide text-indigo-400">Як це працює</span>
            <h2 class="mt-3 text-4xl font-black tracking-tight text-white">Три кроки до старту</h2>
        </div>
        <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-3">
            @foreach ([
                ['01','Зареєструйтесь','Створіть акаунт вчителя або учня за хвилину.'],
                ['02','Створіть курс','Наповніть його матеріалами, тестами та зображеннями.'],
                ['03','Навчайте наживо','Проводьте уроки на інтерактивній дошці та стежте за прогресом.'],
            ] as $step)
                <div class="relative rounded-2xl border border-white/10 bg-white/5 p-8">
                    <span class="bg-gradient-to-br from-indigo-400 to-violet-400 bg-clip-text text-6xl font-black text-transparent">{{ $step[0] }}</span>
                    <h3 class="mt-4 text-xl font-bold text-white">{{ $step[1] }}</h3>
                    <p class="mt-2 text-sm text-slate-400">{{ $step[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== Audience ===================== --}}
<section id="audience" class="mx-auto max-w-7xl px-6 py-24">
    <div class="mx-auto max-w-2xl text-center">
        <span class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Для кого</span>
        <h2 class="mt-3 text-4xl font-black tracking-tight text-slate-900">Зручно кожному учаснику</h2>
    </div>
    <div class="mt-14 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['🎓','Вчителям','Створюйте курси, проводьте уроки, керуйте групами та дошкою.','bg-indigo-50','text-indigo-700'],
            ['📚','Учням','Проходьте курси, складайте тести й приєднуйтесь до уроків наживо.','bg-emerald-50','text-emerald-700'],
            ['👨‍👩‍👧','Батькам','Стежте за успіхами дітей у зрозумілому особистому кабінеті.','bg-amber-50','text-amber-700'],
        ] as $a)
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl {{ $a[3] }} text-3xl">{{ $a[0] }}</div>
                <h3 class="mt-5 text-xl font-bold text-slate-900">{{ $a[1] }}</h3>
                <p class="mt-2 text-sm text-slate-500">{{ $a[2] }}</p>
                <ul class="mt-4 space-y-2 text-sm text-slate-600">
                    @foreach (['Простий інтерфейс','Доступ із будь-якого пристрою','Безкоштовний старт'] as $li)
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> {{ $li }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</section>

{{-- ===================== Reviews ===================== --}}
<section id="reviews" class="bg-slate-50 py-24">
    <div class="mx-auto max-w-7xl px-6">
        <div class="mx-auto max-w-2xl text-center">
            <span class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Відгуки</span>
            <h2 class="mt-3 text-4xl font-black tracking-tight text-slate-900">Що кажуть наші користувачі</h2>
        </div>
        <div class="mt-14 grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach ([
                ['Ірина К.','Вчителька математики','Створила курс за вечір, а інтерактивна дошка зробила уроки наживо неймовірно зручними.','from-indigo-400 to-indigo-600'],
                ['Андрій Ш.','Викладач','Тести з автоперевіркою заощаджують мені години роботи щотижня.','from-emerald-400 to-emerald-600'],
                ['Олена П.','Мама учня','Нарешті я бачу реальний прогрес сина без зайвих дзвінків учителям.','from-amber-400 to-amber-600'],
            ] as $r)
                <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                    <div class="flex text-amber-400">★★★★★</div>
                    <p class="mt-4 text-slate-600">«{{ $r[2] }}»</p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br {{ $r[3] }} font-bold text-white">{{ mb_substr($r[0],0,1) }}</div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $r[0] }}</p>
                            <p class="text-xs text-slate-400">{{ $r[1] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== CTA ===================== --}}
<section class="mx-auto max-w-7xl px-6 py-20">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 px-8 py-16 text-center shadow-2xl">
        <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-10 h-72 w-72 rounded-full bg-white/10 blur-2xl"></div>
        <h2 class="relative text-4xl font-black tracking-tight text-white sm:text-5xl">Готові почати навчання?</h2>
        <p class="relative mx-auto mt-4 max-w-xl text-lg text-indigo-100">Приєднуйтесь до HClass сьогодні — це безкоштовно. Створіть перший курс уже за кілька хвилин.</p>
        <div class="relative mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('register') }}" class="rounded-xl bg-white px-7 py-3.5 text-base font-bold text-indigo-700 shadow-xl transition hover:bg-indigo-50">Створити акаунт</a>
            <a href="{{ route('login') }}" class="rounded-xl border border-white/40 px-7 py-3.5 text-base font-semibold text-white transition hover:bg-white/10">Увійти</a>
        </div>
    </div>
</section>

{{-- ===================== Footer ===================== --}}
<footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 py-10 sm:flex-row">
        <div class="flex items-center gap-2">
            <x-application-logo class="h-8 w-8" />
            <span class="text-lg font-extrabold text-slate-900">HClass</span>
        </div>
        <p class="text-sm text-slate-400">© {{ date('Y') }} HClass — платформа онлайн-навчання. Усі права захищені.</p>
        <div class="flex gap-4 text-sm text-slate-500">
            <a href="#features" class="hover:text-slate-900">Можливості</a>
            <a href="{{ route('login') }}" class="hover:text-slate-900">Увійти</a>
        </div>
    </div>
</footer>

</body>
</html>
