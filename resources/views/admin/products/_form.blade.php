@php
    $groups = $product?->additional_options ?? [];

    if (old('options_json')) {
        $decoded = json_decode((string) old('options_json'), true);

        if (is_array($decoded)) {
            $groups = $decoded;
        }
    }

    $image = $product?->image_url;
@endphp

<form
    method="POST"
    action="{{ $action }}"
    enctype="multipart/form-data"
    class="grid gap-5 xl:grid-cols-[1fr_360px]"
    x-data="productForm({{ Js::from([
        'groups' => $groups,
        'image' => $image,
        'categoryStoreUrl' => route('admin.categories.store'),
    ]) }})"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-5">
        <section class="admin-card p-5">
            <div class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <x-icon name="cube" class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="font-display text-base font-extrabold text-stone-900">Informasi Menu</h2>
                    <p class="text-[11px] font-medium text-stone-400">Data utama yang tampil di kiosk pelanggan.</p>
                </div>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="text-xs font-bold text-stone-600">Nama Menu <span class="text-brand-600">*</span></label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $product?->name) }}"
                        required
                        maxlength="120"
                        placeholder="Contoh: Nasi Goreng Spesial"
                        class="input-field mt-1.5"
                    >
                    @error('name')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between gap-2">
                        <label for="category_id" class="text-xs font-bold text-stone-600">Kategori</label>
                        <button
                            type="button"
                            @click="openCategoryForm()"
                            class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[11px] font-bold text-brand-600 transition hover:bg-brand-50 hover:text-brand-700"
                        >
                            <x-icon name="plus" class="h-3.5 w-3.5" />
                            Tambah Kategori
                        </button>
                    </div>
                    <select x-ref="categorySelect" id="category_id" name="category_id" class="input-field mt-1.5">
                        <option value="">Tanpa kategori</option>
                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected((string) old('category_id', $product?->category_id) === (string) $category->id)
                            >{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <p x-show="categoryFeedback" x-cloak class="mt-1.5 text-[11px] font-medium text-emerald-600" x-text="categoryFeedback"></p>
                    @error('category_id')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="text-xs font-bold text-stone-600">Harga (Rp) <span class="text-brand-600">*</span></label>
                    <div class="relative mt-1.5">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm font-bold text-stone-400">Rp</span>
                        <input
                            id="price"
                            name="price"
                            type="number"
                            min="0"
                            max="100000000"
                            step="1"
                            value="{{ old('price', $product?->price) }}"
                            required
                            class="input-field !pl-11"
                        >
                    </div>
                    @error('price')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="text-xs font-bold text-stone-600">Deskripsi</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        maxlength="1000"
                        placeholder="Deskripsi singkat menu, bahan utama, atau porsi."
                        class="input-field mt-1.5 resize-y"
                    >{{ old('description', $product?->description) }}</textarea>
                    @error('description')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <input
                            type="checkbox"
                            name="is_available"
                            value="1"
                            @checked(old('is_available', $product?->is_available ?? true))
                            class="mt-0.5 h-5 w-5 rounded border-stone-300 text-brand-600 focus:ring-brand-500"
                        >
                        <span>
                            <span class="block text-sm font-bold text-stone-800">Tersedia untuk dipesan</span>
                            <span class="mt-0.5 block text-[11px] font-medium leading-5 text-stone-500">
                                Jika dimatikan, menu tetap tampil di kiosk dengan label “Habis” dan tidak bisa dipesan.
                            </span>
                        </span>
                    </label>
                </div>
            </div>
        </section>

        <section class="admin-card p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sun-100 text-amber-600">
                        <x-icon name="sparkles" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="font-display text-base font-extrabold text-stone-900">Opsi Tambahan</h2>
                        <p class="text-[11px] font-medium text-stone-400">Topping, level pedas, ukuran, atau tambahan lainnya.</p>
                    </div>
                </div>

                <button type="button" @click="addGroup" class="btn-secondary !px-4 !py-2.5 text-xs">
                    <x-icon name="plus" class="h-4 w-4" />
                    Tambah Grup
                </button>
            </div>

            <p x-show="groups.length === 0" class="mt-4 rounded-2xl border border-dashed border-stone-300 bg-stone-50 px-4 py-6 text-center text-xs font-medium text-stone-500">
                Belum ada opsi tambahan. Menu ini akan langsung bisa ditambahkan ke pesanan.
            </p>

            <div class="mt-4 space-y-4">
                <template x-for="(group, groupIndex) in groups" :key="groupIndex">
                    <div class="rounded-2xl border border-stone-200 bg-stone-50/60 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">
                                Grup <span x-text="groupIndex + 1"></span>
                            </p>
                            <button type="button" @click="removeGroup(groupIndex)" class="flex items-center gap-1 rounded-lg px-2 py-1 text-[11px] font-bold text-stone-400 transition hover:bg-red-50 hover:text-red-600">
                                <x-icon name="trash" class="h-3.5 w-3.5" />
                                Hapus
                            </button>
                        </div>

                        <div class="mt-3 grid gap-3 sm:grid-cols-[1fr_200px]">
                            <div>
                                <label class="text-[11px] font-bold text-stone-500">Nama Grup</label>
                                <input
                                    type="text"
                                    x-model="group.groupName"
                                    maxlength="80"
                                    placeholder="Contoh: Level Pedas"
                                    class="input-field mt-1 !py-2.5 text-sm"
                                >
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-stone-500">Tipe Pilihan</label>
                                <select x-model="group.type" class="input-field mt-1 !py-2.5 text-sm">
                                    <option value="radio">Pilih satu</option>
                                    <option value="checkbox">Boleh lebih dari satu</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 space-y-2">
                            <template x-for="(option, optionIndex) in group.options" :key="optionIndex">
                                <div class="grid items-center gap-2 sm:grid-cols-[1fr_150px_40px]">
                                    <input
                                        type="text"
                                        x-model="option.name"
                                        maxlength="80"
                                        placeholder="Nama pilihan"
                                        class="input-field !py-2.5 text-sm"
                                    >
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-xs font-bold text-stone-400">Rp</span>
                                        <input
                                            type="number"
                                            min="0"
                                            max="10000000"
                                            step="1"
                                            x-model.number="option.price"
                                            class="input-field !py-2.5 !pl-9 text-sm"
                                        >
                                    </div>
                                    <button type="button" @click="removeOption(groupIndex, optionIndex)" class="flex h-10 w-10 items-center justify-center justify-self-end rounded-xl text-stone-400 transition hover:bg-red-50 hover:text-red-600" aria-label="Hapus pilihan">
                                        <x-icon name="x" class="h-4 w-4" />
                                    </button>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="addOption(groupIndex)" class="mt-2.5 flex items-center gap-1.5 rounded-xl px-2.5 py-1.5 text-[11px] font-bold text-brand-600 transition hover:bg-brand-50">
                            <x-icon name="plus" class="h-3.5 w-3.5" />
                            Tambah pilihan
                        </button>
                    </div>
                </template>
            </div>

            <input type="hidden" name="options_json" :value="optionsJson">

            @error('options_json')
                <p class="mt-3 rounded-xl bg-red-50 px-3 py-2 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </section>
    </div>

    <aside class="space-y-5">
        <section class="admin-card p-5">
            <h2 class="font-display text-base font-extrabold text-stone-900">Foto Menu</h2>
            <p class="mt-1 text-[11px] font-medium leading-5 text-stone-400">Rasio 4:3, maksimal 4 MB (JPG, PNG, WEBP).</p>

            <div class="mt-3 aspect-[4/3] overflow-hidden rounded-2xl border border-dashed border-stone-300 bg-stone-50">
                <img
                    x-show="preview || existingImage"
                    :src="preview ?? existingImage"
                    alt="Pratinjau foto menu"
                    class="h-full w-full object-cover"
                >
                <div x-show="!preview && !existingImage" class="flex h-full w-full flex-col items-center justify-center gap-2 text-stone-400">
                    <x-icon name="photo" class="h-8 w-8" />
                    <p class="text-[11px] font-semibold">Belum ada foto</p>
                </div>
            </div>

            <label for="image" class="btn-secondary mt-3 w-full cursor-pointer">
                <x-icon name="photo" class="h-4 w-4" />
                Pilih Foto
            </label>
            <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" @change="previewImage($event)">
            <p class="mt-2 text-[10px] leading-4 text-stone-400">
                Tanpa foto, kiosk akan menampilkan ilustrasi bawaan.
            </p>

            @error('image')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </section>

        <section class="admin-card p-5">
            <h2 class="font-display text-base font-extrabold text-stone-900">Simpan</h2>
            <p class="mt-1 text-[11px] font-medium leading-5 text-stone-400">
                Semua harga dikunci di server, jadi perubahan langsung berlaku di kiosk.
            </p>

            <button type="submit" class="btn-primary mt-3 w-full">
                <x-icon name="check" class="h-4 w-4" />
                {{ $submitLabel }}
            </button>

            <a href="{{ route('admin.products.index') }}" class="btn-ghost mt-2 w-full">
                Batal
            </a>
        </section>
    </aside>

    {{-- Modal tambah kategori cepat --}}
    <div
        x-show="showCategoryForm"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[80] flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="new-category-title"
        @keydown.escape.window="closeCategoryForm()"
    >
        <div class="absolute inset-0 bg-stone-950/50 backdrop-blur-sm" @click="closeCategoryForm()"></div>

        <div class="relative w-full max-w-sm rounded-3xl bg-white p-5 shadow-kiosk">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <x-icon name="tag" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 id="new-category-title" class="font-display text-base font-extrabold text-stone-900">Kategori Baru</h2>
                        <p class="text-[11px] font-medium text-stone-400">Langsung tersimpan dan dipilih.</p>
                    </div>
                </div>
                <button type="button" @click="closeCategoryForm()" class="flex h-9 w-9 items-center justify-center rounded-xl text-stone-400 transition hover:bg-stone-100" aria-label="Tutup">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <div class="mt-4">
                <label for="new-category-name" class="text-xs font-bold text-stone-600">Nama Kategori</label>
                <input
                    x-ref="newCategoryInput"
                    id="new-category-name"
                    type="text"
                    x-model="newCategoryName"
                    @keydown.enter.prevent="saveCategory()"
                    maxlength="80"
                    placeholder="Contoh: Promo Hari Ini"
                    class="input-field mt-1.5"
                >
                <p x-show="categoryError" x-cloak class="mt-1.5 text-xs font-medium text-red-600" x-text="categoryError"></p>
            </div>

            <div class="mt-4">
                <p class="text-xs font-bold text-stone-600">Ikon</p>
                <p class="mt-0.5 text-[11px] text-stone-400">Kosongkan untuk otomatis dari nama.</p>
                <x-icon-picker name="quick_category_icon" model="newCategoryIcon" class="mt-1.5" />
            </div>

            <div class="mt-4">
                <label for="quick-category-image" class="text-xs font-bold text-stone-600">Atau Upload Gambar</label>
                <input
                    x-ref="newCategoryImage"
                    id="quick-category-image"
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    class="mt-1.5 block w-full cursor-pointer rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-[11px] font-medium text-stone-500 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-[11px] file:font-bold file:text-white"
                >
                <p class="mt-1 text-[10px] leading-4 text-stone-400">PNG/JPG/WEBP, maks 1 MB.</p>
            </div>

            <div class="mt-4 flex gap-2">
                <button type="button" @click="closeCategoryForm()" class="btn-secondary flex-1 justify-center">
                    Batal
                </button>
                <button type="button" @click="saveCategory()" :disabled="categorySaving" class="btn-primary flex-1 justify-center">
                    <svg x-show="categorySaving" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M21 12a9 9 0 0 1-9 9v-3a6 6 0 0 0 6-6h3Z"></path></svg>
                    <x-icon x-show="!categorySaving" name="plus" class="h-4 w-4" />
                    <span x-text="categorySaving ? 'Menyimpan...' : 'Tambah Kategori'"></span>
                </button>
            </div>
        </div>
    </div>
</form>
