@props(['name' => 'icon', 'selected' => null, 'model' => null])

<div {{ $attributes->merge(['class' => 'grid grid-cols-3 gap-2 sm:grid-cols-5']) }}>
    @foreach (\App\Support\MenuIcon::options() as $key => $label)
        <label class="cursor-pointer">
            <input
                type="radio"
                name="{{ $name }}"
                value="{{ $key }}"
                class="peer sr-only"
                @if ($model)
                    x-model="{{ $model }}"
                @else
                    @checked((string) $selected === $key)
                @endif
            >
            <span class="flex h-full flex-col items-center justify-center gap-1 rounded-xl border border-stone-200 bg-white px-1.5 py-2 text-stone-500 transition hover:border-brand-300 hover:text-brand-600 peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400">
                <x-icon :name="$key" class="h-5 w-5" />
                <span class="text-center text-[9px] font-bold leading-tight">{{ $label }}</span>
            </span>
        </label>
    @endforeach
</div>
