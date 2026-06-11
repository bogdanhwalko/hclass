<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HClass') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans text-slate-800 antialiased">
        <div class="flex min-h-full">
            {{-- Left brand panel --}}
            <div class="relative hidden w-1/2 overflow-hidden bg-slate-900 lg:block">
                <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-indigo-600/30 blur-3xl"></div>
                <div class="absolute -bottom-32 -right-16 h-96 w-96 rounded-full bg-violet-600/30 blur-3xl"></div>
                <div class="relative flex h-full flex-col justify-between p-12">
                    <a href="/" class="flex items-center gap-3">
                        <x-application-logo class="h-11 w-11" />
                        <span class="text-2xl font-extrabold text-white">HClass</span>
                    </a>
                    <div>
                        <h2 class="max-w-md text-4xl font-extrabold leading-tight text-white">
                            Навчайся та викладай <span class="bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent">онлайн</span> — без меж.
                        </h2>
                        <p class="mt-4 max-w-md text-slate-300">
                            Інтерактивна дошка, власні курси, уроки в реальному часі. Усе для сучасної освіти в одному місці.
                        </p>
                        <div class="mt-8 flex items-center gap-6 text-slate-300">
                            <div><p class="text-2xl font-bold text-white">5 000+</p><p class="text-xs">учнів</p></div>
                            <div class="h-8 w-px bg-white/20"></div>
                            <div><p class="text-2xl font-bold text-white">300+</p><p class="text-xs">курсів</p></div>
                            <div class="h-8 w-px bg-white/20"></div>
                            <div><p class="text-2xl font-bold text-white">98%</p><p class="text-xs">задоволених</p></div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">© {{ date('Y') }} HClass — освітня платформа.</p>
                </div>
            </div>

            {{-- Right form panel --}}
            <div class="flex w-full items-center justify-center bg-slate-50 px-6 py-12 lg:w-1/2">
                <div class="w-full max-w-md">
                    <a href="/" class="mb-8 flex items-center gap-2 lg:hidden">
                        <x-application-logo class="h-10 w-10" />
                        <span class="text-xl font-extrabold text-slate-900">HClass</span>
                    </a>
                    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/50">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
