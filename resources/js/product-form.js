export default function productForm(initial = {}) {
    const normalizeGroups = (groups) => {
        if (!Array.isArray(groups)) {
            return [];
        }

        return groups.map((group) => ({
            groupName: group?.groupName ?? '',
            type: group?.type === 'checkbox' ? 'checkbox' : 'radio',
            options: Array.isArray(group?.options) && group.options.length > 0
                ? group.options.map((option) => ({
                    name: option?.name ?? '',
                    price: Number(option?.price ?? 0),
                }))
                : [{ name: '', price: 0 }],
        }));
    };

    return {
        groups: normalizeGroups(initial.groups),
        preview: null,
        existingImage: initial.image ?? null,

        categoryStoreUrl: initial.categoryStoreUrl ?? '',
        showCategoryForm: false,
        newCategoryName: '',
        newCategoryIcon: '',
        categoryError: '',
        categorySaving: false,
        categoryFeedback: '',

        get optionsJson() {
            return JSON.stringify(this.groups);
        },

        openCategoryForm() {
            this.newCategoryName = '';
            this.newCategoryIcon = '';
            this.categoryError = '';
            this.showCategoryForm = true;

            this.$nextTick(() => {
                this.$refs.newCategoryInput?.focus();

                if (this.$refs.newCategoryImage) {
                    this.$refs.newCategoryImage.value = '';
                }
            });
        },

        closeCategoryForm() {
            if (this.categorySaving) {
                return;
            }

            this.showCategoryForm = false;
            this.categoryError = '';
        },

        async saveCategory() {
            if (this.categorySaving) {
                return;
            }

            const name = this.newCategoryName.trim();

            if (name === '') {
                this.categoryError = 'Nama kategori wajib diisi.';
                return;
            }

            this.categorySaving = true;
            this.categoryError = '';

            try {
                const formData = new FormData();
                formData.append('name', name);

                if (this.newCategoryIcon) {
                    formData.append('icon', this.newCategoryIcon);
                }

                const file = this.$refs.newCategoryImage?.files?.[0];

                if (file) {
                    formData.append('icon_image', file);
                }

                const response = await fetch(this.categoryStoreUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: formData,
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                    throw new Error(firstError || data.message || 'Gagal menambah kategori.');
                }

                this.addCategoryOption(data.category);
                this.showCategoryForm = false;
                this.categoryFeedback = `Kategori "${data.category.name}" ditambahkan.`;
            } catch (error) {
                this.categoryError = error.message;
            } finally {
                this.categorySaving = false;
            }
        },

        addCategoryOption(category) {
            const select = this.$refs.categorySelect;

            if (!select || !category) {
                return;
            }

            let option = Array.from(select.options).find((item) => item.value === String(category.id));

            if (!option) {
                option = new Option(category.name, category.id);
                select.add(option);

                const sorted = Array.from(select.options).sort((a, b) => {
                    if (a.value === '') return -1;
                    if (b.value === '') return 1;
                    return a.text.localeCompare(b.text, 'id');
                });

                sorted.forEach((item) => select.add(item));
            }

            select.value = String(category.id);
        },

        addGroup() {
            this.groups.push({
                groupName: '',
                type: 'radio',
                options: [{ name: '', price: 0 }],
            });
        },

        removeGroup(index) {
            this.groups.splice(index, 1);
        },

        addOption(groupIndex) {
            this.groups[groupIndex].options.push({ name: '', price: 0 });
        },

        removeOption(groupIndex, optionIndex) {
            this.groups[groupIndex].options.splice(optionIndex, 1);

            if (this.groups[groupIndex].options.length === 0) {
                this.groups[groupIndex].options.push({ name: '', price: 0 });
            }
        },

        previewImage(event) {
            const file = event.target.files?.[0];

            this.preview = file ? URL.createObjectURL(file) : null;
        },
    };
}
