<x-app-shell title="Предмети">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
            <h3 class="mb-4 font-semibold text-slate-800">Новий предмет</h3>
            <form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Назва</label>
                    <input name="name" value="{{ old('name') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                    @error('name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Код</label>
                    <input name="code" value="{{ old('code') }}" placeholder="MATH" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('code') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Опис</label>
                    <textarea name="description" rows="3" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">{{ old('description') }}</textarea>
                </div>
                <button class="w-full rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Додати</button>
            </form>
        </div>

        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @forelse ($subjects as $subject)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-semibold text-slate-900">{{ $subject->name }}</h4>
                                <span class="text-xs text-slate-400">{{ $subject->code ?: '—' }} · {{ $subject->classes_count }} класів</span>
                            </div>
                            <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Видалити предмет?')">
                                @csrf @method('DELETE')
                                <button class="text-xs font-medium text-rose-500 hover:underline">✕</button>
                            </form>
                        </div>
                        @if ($subject->description)
                            <p class="mt-2 text-sm text-slate-500">{{ $subject->description }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Предметів ще немає.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-shell>
