@props([
    'name',                 // hidden input name
    'options' => [],        // array of ['value'=>..,'label'=>..]
    'selected' => '',       // currently selected value
    'placeholder' => 'Оберіть…',
    'emptyLabel' => null,   // optional first option (value '')
    'submitOnChange' => false,
])

@php
    $opts = collect($options)->map(fn ($o) => ['value' => (string) $o['value'], 'label' => $o['label']])->values();
    if ($emptyLabel !== null) {
        $opts->prepend(['value' => '', 'label' => $emptyLabel]);
    }
    $selected = (string) $selected;
    $current = $opts->firstWhere('value', $selected);
@endphp

<div x-data="searchSelect({
        options: {{ $opts->toJson() }},
        selected: @js($selected),
        submitOnChange: {{ $submitOnChange ? 'true' : 'false' }},
     })" class="relative" @click.outside="open = false">
    <input type="hidden" name="{{ $name }}" :value="value" x-ref="field">

    <button type="button" @click="toggle()"
            class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-sm focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400">
        <span class="truncate" :class="label ? 'text-slate-700' : 'text-slate-400'"
              x-text="label || @js($placeholder)"></span>
        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div x-show="open" x-cloak x-transition.opacity
         class="absolute z-50 mt-1 max-h-64 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
        <div class="border-b border-slate-100 p-2">
            <input x-ref="search" x-model="query" @keydown.escape="open=false" placeholder="Пошук…"
                   class="w-full rounded-lg border-slate-200 px-2 py-1.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
        </div>
        <ul class="max-h-48 overflow-y-auto py-1">
            <template x-for="opt in filtered()" :key="opt.value">
                <li>
                    <button type="button" @click="pick(opt)"
                            class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-indigo-50"
                            :class="opt.value === value ? 'font-semibold text-indigo-600' : 'text-slate-700'">
                        <span x-text="opt.label"></span>
                        <span x-show="opt.value === value" class="text-indigo-500">✓</span>
                    </button>
                </li>
            </template>
            <li x-show="filtered().length === 0" class="px-3 py-2 text-sm text-slate-400">Нічого не знайдено</li>
        </ul>
    </div>
</div>

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('searchSelect', (cfg) => ({
        options: cfg.options || [],
        value: cfg.selected || '',
        submitOnChange: cfg.submitOnChange || false,
        open: false,
        query: '',
        get label() {
            const o = this.options.find(x => x.value === this.value);
            return o ? o.label : '';
        },
        toggle() {
            this.open = !this.open;
            if (this.open) { this.query = ''; this.$nextTick(() => this.$refs.search && this.$refs.search.focus()); }
        },
        filtered() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.options;
            return this.options.filter(o => o.label.toLowerCase().includes(q));
        },
        pick(opt) {
            this.value = opt.value;
            this.open = false;
            if (this.submitOnChange && this.$refs.field.form) {
                this.$refs.field.form.submit();
            }
        },
    }));
});
</script>
@endonce
