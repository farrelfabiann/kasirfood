@extends('layouts.admin')

@section('title', 'Kategori')
@section('eyebrow', 'Manajemen Menu')
@section('heading', 'Kategori Menu')

@section('content')
    <div
        x-data="{
            open: false,
            action: '',
            name: '',
            icon: '',
            imageUrl: null,
            removeImage: false,
            edit(payload) {
                this.action = payload.action;
                this.name = payload.name;
                this.icon = payload.icon;
                this.imageUrl = payload.imageUrl;
                this.removeImage = false;
                this.open = true;
            },
            close() {
                this.open = false;
            },
        }"
    >
        <div class="grid gap-5 xl:grid-cols-[1fr_340px]">
            <section class="admin-card overflow-hidden">
                <div class="border-b border-stone-100 px-5 py-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Daftar</p>
                    <h2 class="font-display text-base font-extrabold text-stone-900">Kategori Tersimpan</h2>
                    <p class="mt-0.5 text-[11px] font-medium text-stone-400">Ikon tampil di sidebar kiosk. Urutan mengikuti abjad.</p>
                </div>

                @if ($categories->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-stone-100 text-stone-400">
                            <x-icon name="tag" class="h-7 w-7" />
                        </span>
                        <p class="mt-4 font-display text-base font-extrabold text-stone-800">Belum ada kategori</p>
                        <p class="mt-1 text-sm text-stone-500">Tambahkan kategori di panel kanan.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[680px] text-left">
                            <thead>
                                <tr class="border-b border-stone-100 bg-stone-50/70 text-[10px] font-bold uppercase tracking-[0.14em] text-stone-400">
                                    <th class="px-5 py-3">Nama Kategori</th>
                                    <th class="px-3 py-3">Ikon</th>
                                    <th class="px-3 py-3">Jumlah Menu</th>
                                    <th class="px-5 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($categories as $category)
                                    <tr class="transition hover:bg-stone-50/60">
                                        <td class="px-5 py-3">
                                            <form id="category-update-{{ $category->id }}" method="POST" action="{{ route('admin.categories.update', $category) }}">
                                                @csrf
                                                @method('PUT')
                                                <input
                                                    type="text"
                                                    name="name"
                                                    value="{{ $category->name }}"
                                                    required
                                                    maxlength="80"
                                                    class="input-field !py-2.5 text-sm"
                                                >
                                            </form>
                                        </td>

                                        <td class="px-3 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-stone-100 text-stone-600">
                                                    @if ($category->icon_url)
                                                        <img src="{{ $category->icon_url }}" alt="" class="h-full w-full object-cover">
                                                    @else
                                                        <x-icon :name="$category->icon_key" class="h-5 w-5" />
                                                    @endif
                                                </span>
                                                <button
                                                    type="button"
                                                    @click="edit({
                                                        action: @js(route('admin.categories.update', $category)),
                                                        name: @js($category->name),
                                                        icon: @js($category->icon_key),
                                                        imageUrl: @js($category->icon_url),
                                                    })"
                                                    class="rounded-lg px-2 py-1 text-[11px] font-bold text-brand-600 transition hover:bg-brand-50"
                                                >
                                                    Ubah ikon
                                                </button>
                                            </div>
                                        </td>

                                        <td class="px-3 py-3">
                                            <span @class([
                                                'badge',
                                                'bg-brand-50 text-brand-700' => $category->products_count > 0,
                                                'bg-stone-100 text-stone-400' => $category->products_count === 0,
                                            ])>
                                                {{ $category->products_count }} menu
                                            </span>
                                        </td>

                                        <td class="px-5 py-3">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="submit" form="category-update-{{ $category->id }}" class="flex h-9 items-center gap-1.5 rounded-xl border border-stone-200 bg-white px-3 text-[11px] font-bold text-stone-600 transition hover:border-brand-200 hover:text-brand-700">
                                                    <x-icon name="check" class="h-3.5 w-3.5" />
                                                    Simpan
                                                </button>

                                                <form
                                                    id="category-delete-{{ $category->id }}"
                                                    method="POST"
                                                    action="{{ route('admin.categories.destroy', $category) }}"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        title="Hapus kategori"
                                                        onclick="return confirm({{ Js::from('Hapus kategori '.$category->name.'?'.($category->products_count > 0 ? ' '.$category->products_count.' menu akan menjadi Tanpa kategori.' : '')) }});"
                                                        class="flex h-9 w-9 items-center justify-center rounded-xl text-stone-400 transition hover:bg-red-50 hover:text-red-600"
                                                        aria-label="Hapus {{ $category->name }}"
                                                    >
                                                        <x-icon name="trash" class="h-4 w-4" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="admin-card h-fit p-5">
                <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <x-icon name="plus" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="font-display text-base font-extrabold text-stone-900">Kategori Baru</h2>
                        <p class="text-[11px] font-medium text-stone-400">Nama harus unik.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="new-category-name" class="text-xs font-bold text-stone-600">Nama Kategori</label>
                        <input
                            id="new-category-name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            maxlength="80"
                            placeholder="Contoh: Promo Hari Ini"
                            class="input-field mt-1.5"
                        >
                        @error('name')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <p class="text-xs font-bold text-stone-600">Ikon Bawaan</p>
                        <p class="mt-0.5 text-[11px] text-stone-400">Kosongkan untuk otomatis dari nama kategori.</p>
                        <x-icon-picker name="icon" :selected="old('icon')" class="mt-1.5" />
                        @error('icon')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="new-category-image" class="text-xs font-bold text-stone-600">Atau Upload Gambar</label>
                        <input
                            id="new-category-image"
                            type="file"
                            name="icon_image"
                            accept="image/png,image/jpeg,image/webp"
                            class="mt-1.5 block w-full cursor-pointer rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-[11px] font-medium text-stone-500 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-[11px] file:font-bold file:text-white"
                        >
                        <p class="mt-1 text-[10px] leading-4 text-stone-400">PNG/JPG/WEBP, maks 1 MB. Gambar dipakai untuk ikon promo.</p>
                        @error('icon_image')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary w-full">
                        <x-icon name="plus" class="h-4 w-4" />
                        Tambah Kategori
                    </button>
                </form>

                <p class="mt-4 flex items-start gap-2 rounded-2xl bg-stone-50 px-3.5 py-3 text-[11px] font-medium leading-5 text-stone-500">
                    <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0 text-brand-500" />
                    Menghapus kategori tidak menghapus menu. Menu di dalamnya otomatis menjadi Tanpa kategori dan tetap tampil di kiosk.
                </p>
            </section>
        </div>

        {{-- Modal ubah ikon --}}
        <div
            x-show="open"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[80] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="icon-modal-title"
            @keydown.escape.window="close()"
        >
            <div class="absolute inset-0 bg-stone-950/50 backdrop-blur-sm" @click="close()"></div>

            <form
                method="POST"
                :action="action"
                enctype="multipart/form-data"
                class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-5 shadow-kiosk"
            >
                @csrf
                @method('PUT')
                <input type="hidden" name="name" :value="name">

                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-icon name="photo" class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 id="icon-modal-title" class="font-display text-base font-extrabold text-stone-900">Ikon Kategori</h2>
                            <p class="text-[11px] font-medium text-stone-400" x-text="name"></p>
                        </div>
                    </div>
                    <button type="button" @click="close()" class="flex h-9 w-9 items-center justify-center rounded-xl text-stone-400 transition hover:bg-stone-100" aria-label="Tutup">
                        <x-icon name="x" class="h-4 w-4" />
                    </button>
                </div>

                <div x-show="imageUrl" x-cloak class="mt-4 flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-3">
                    <img :src="imageUrl" alt="" class="h-14 w-14 rounded-xl object-cover">
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-bold text-stone-700">Gambar ikon aktif</p>
                        <p class="text-[10px] text-stone-400">Gambar menimpa ikon bawaan.</p>
                    </div>
                    <label class="flex items-center gap-1.5 text-[11px] font-bold text-red-600">
                        <input type="checkbox" name="remove_icon_image" value="1" x-model="removeImage" class="h-4 w-4 rounded border-stone-300 text-red-600 focus:ring-red-500">
                        Hapus
                    </label>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-bold text-stone-600">Ikon Bawaan</p>
                    <x-icon-picker name="icon" model="icon" class="mt-1.5" />
                    @error('icon')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="icon-modal-image" class="text-xs font-bold text-stone-600">Upload Gambar Baru</label>
                    <input
                        id="icon-modal-image"
                        type="file"
                        name="icon_image"
                        accept="image/png,image/jpeg,image/webp"
                        class="mt-1.5 block w-full cursor-pointer rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-[11px] font-medium text-stone-500 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-[11px] file:font-bold file:text-white"
                    >
                    <p class="mt-1 text-[10px] leading-4 text-stone-400">PNG/JPG/WEBP, maks 1 MB.</p>
                    @error('icon_image')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-5 flex gap-2">
                    <button type="button" @click="close()" class="btn-secondary flex-1 justify-center">Batal</button>
                    <button type="submit" class="btn-primary flex-1 justify-center">
                        <x-icon name="check" class="h-4 w-4" />
                        Simpan Ikon
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
