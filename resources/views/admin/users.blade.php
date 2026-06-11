<x-app-shell title="Користувачі">
    <div x-data="{ showForm: false, editId: null }" class="space-y-6">

        {{-- Toolbar: search + create --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" class="flex flex-1 gap-2">
                <input name="search" value="{{ request('search') }}" placeholder="Пошук за іменем або email…"
                       class="w-full max-w-xs rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <select name="role" class="rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <option value="">Усі ролі</option>
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-slate-800 px-4 text-sm font-medium text-white hover:bg-slate-700">Пошук</button>
            </form>
            <button @click="showForm = !showForm" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                + Новий користувач
            </button>
        </div>

        {{-- Validation summary (so server errors are visible even after redirect) --}}
        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc space-y-0.5 pl-5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- ===== Create form ===== --}}
        <div x-show="showForm" x-cloak class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-bold text-slate-800">Новий користувач</h3>
            <form method="POST" action="{{ route('admin.users.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Ім'я</label>
                    <input name="name" value="{{ old('name') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Email (логін)</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Телефон</label>
                    <input name="phone" value="{{ old('phone') }}" placeholder="+380…" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Роль</label>
                    <select name="role" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Пароль</label>
                    <input type="password" name="password" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Підтвердження пароля</label>
                    <input type="password" name="password_confirmation" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600 sm:col-span-2">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                    Активний (може входити в систему)
                </label>
                <div class="sm:col-span-2">
                    <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Створити користувача</button>
                </div>
            </form>
        </div>

        {{-- ===== Users table ===== --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Користувач</th>
                        <th class="px-5 py-3">Телефон</th>
                        <th class="px-5 py-3">Роль</th>
                        <th class="px-5 py-3">Статус</th>
                        <th class="px-5 py-3 text-right">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $u)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-600">{{ $u->initials() }}</div>
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $u->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $u->phone ?: '—' }}</td>
                            <td class="px-5 py-3"><span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $u->role->color() }}">{{ $u->role->label() }}</span></td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1.5 text-xs {{ $u->is_active ? 'text-emerald-600' : 'text-slate-400' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $u->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                    {{ $u->is_active ? 'Активний' : 'Вимкнено' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <button @click="editId = (editId === {{ $u->id }} ? null : {{ $u->id }})" class="text-xs font-medium text-indigo-600 hover:underline">Редагувати</button>
                                @if ($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Видалити користувача?')" class="ml-2 inline">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-medium text-rose-500 hover:underline">Видалити</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        {{-- Inline edit row --}}
                        <tr x-show="editId === {{ $u->id }}" x-cloak>
                            <td colspan="5" class="bg-slate-50 px-5 py-4">
                                <form method="POST" action="{{ route('admin.users.update', $u) }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    @csrf @method('PATCH')
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-500">Ім'я</label>
                                        <input name="name" value="{{ $u->name }}" class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-500">Email (логін)</label>
                                        <input type="email" name="email" value="{{ $u->email }}" class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400" required>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-500">Телефон</label>
                                        <input name="phone" value="{{ $u->phone }}" class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-500">Роль</label>
                                        <select name="role" @disabled($u->id === auth()->id()) class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400 disabled:bg-slate-100">
                                            @foreach ($roles as $value => $label)
                                                <option value="{{ $value }}" @selected($u->role->value === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-500">Новий пароль <span class="text-slate-400">(лишіть порожнім, щоб не міняти)</span></label>
                                        <input type="password" name="password" autocomplete="new-password" class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-500">Підтвердження пароля</label>
                                        <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                                    </div>
                                    <label class="flex items-center gap-2 text-sm text-slate-600 sm:col-span-2">
                                        <input type="checkbox" name="is_active" value="1" @checked($u->is_active) @disabled($u->id === auth()->id()) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                                        Активний
                                        @if ($u->id === auth()->id())<span class="text-xs text-slate-400">(власний акаунт не можна вимкнути або змінити роль)</span>@endif
                                    </label>
                                    <div class="flex gap-2 sm:col-span-2">
                                        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Зберегти зміни</button>
                                        <button type="button" @click="editId = null" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">Скасувати</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>{{ $users->links() }}</div>
    </div>
</x-app-shell>
