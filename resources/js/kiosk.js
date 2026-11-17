export default function kiosk(initial = {}) {
    return {
        products: initial.products ?? [],
        categories: initial.categories ?? [],
        payment: initial.payment ?? {},
        app: initial.app ?? { name: 'KlikKluk' },

        started: false,
        orderType: null,
        showTypeModal: false,

        activeCategory: 'all',
        search: '',

        cart: [],
        showCart: false,

        selectedProduct: null,
        selectedQty: 1,
        selectedOptions: {},
        itemNote: '',
        showProductModal: false,

        showCheckout: false,
        customerName: '',
        paymentMethod: 'qris',
        submitting: false,
        error: '',

        order: null,
        paymentState: 'unpaid',
        showConfirmation: false,
        showEdc: false,
        edcSubmitting: false,

        toast: {
            visible: false,
            message: '',
            type: 'success',
        },
        toastTimer: null,

        init() {
            this.paymentMethod = this.payment.qris_enabled ? 'qris' : 'cash';
        },

        start(type) {
            this.orderType = type;
            this.started = true;
            this.showTypeModal = false;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        setOrderType(type) {
            this.orderType = type;
            this.showTypeModal = false;
        },

        categoryProducts(categoryId) {
            return this.products.filter((product) => String(product.category_id) === String(categoryId));
        },

        filteredProducts() {
            const query = this.search.trim().toLowerCase();

            return this.products.filter((product) => {
                if (this.activeCategory !== 'all' && String(product.category_id) !== String(this.activeCategory)) {
                    return false;
                }

                if (query === '') {
                    return true;
                }

                const haystack = `${product.name} ${product.description ?? ''} ${product.category_name ?? ''}`.toLowerCase();

                return haystack.includes(query);
            });
        },

        openProduct(product) {
            if (!product.is_available) {
                return;
            }

            this.selectedProduct = product;
            this.selectedQty = 1;
            this.itemNote = '';

            const selections = {};
            (product.options ?? []).forEach((group) => {
                selections[group.groupName] = group.type === 'radio' && group.options.length > 0
                    ? [group.options[0].name]
                    : [];
            });

            this.selectedOptions = selections;
            this.showProductModal = true;
        },

        optionSelected(groupName, optionName) {
            return (this.selectedOptions[groupName] ?? []).includes(optionName);
        },

        toggleOption(group, option) {
            const current = this.selectedOptions[group.groupName] ?? [];

            if (group.type === 'radio') {
                this.selectedOptions[group.groupName] = [option.name];
                return;
            }

            this.selectedOptions[group.groupName] = current.includes(option.name)
                ? current.filter((name) => name !== option.name)
                : [...current, option.name];
        },

        selectedOptionObjects() {
            if (!this.selectedProduct) {
                return [];
            }

            const selected = [];

            (this.selectedProduct.options ?? []).forEach((group) => {
                (this.selectedOptions[group.groupName] ?? []).forEach((name) => {
                    const option = (group.options ?? []).find((candidate) => candidate.name === name);

                    if (option) {
                        selected.push({
                            group: group.groupName,
                            name: option.name,
                            price: Number(option.price) || 0,
                        });
                    }
                });
            });

            return selected;
        },

        get selectedOptionsTotal() {
            return this.selectedOptionObjects().reduce((total, option) => total + Number(option.price || 0), 0);
        },

        get selectedUnitPrice() {
            return Number(this.selectedProduct?.price ?? 0) + this.selectedOptionsTotal;
        },

        get selectedLineTotal() {
            return this.selectedUnitPrice * this.selectedQty;
        },

        addToCart() {
            if (!this.selectedProduct) {
                return;
            }

            const options = this.selectedOptionObjects();
            const note = this.itemNote.trim();
            const signature = JSON.stringify({
                product_id: this.selectedProduct.id,
                options: options.map((option) => `${option.group}::${option.name}`).sort(),
                note,
            });

            const existing = this.cart.find((item) => item.signature === signature);

            if (existing) {
                existing.qty = Math.min(99, existing.qty + this.selectedQty);
            } else {
                this.cart.push({
                    signature,
                    product_id: this.selectedProduct.id,
                    name: this.selectedProduct.name,
                    image_url: this.selectedProduct.image_url,
                    base_price: this.selectedProduct.price,
                    unit_price: this.selectedUnitPrice,
                    qty: this.selectedQty,
                    options,
                    note,
                });
            }

            this.showToast(`${this.selectedQty} × ${this.selectedProduct.name} ditambahkan ke pesanan.`);
            this.showProductModal = false;
        },

        changeQty(item, delta) {
            item.qty = Math.max(0, Math.min(99, item.qty + delta));

            if (item.qty === 0) {
                this.removeItem(item);
            }
        },

        removeItem(item) {
            this.cart = this.cart.filter((candidate) => candidate.signature !== item.signature);
        },

        clearCart() {
            this.cart = [];
        },

        get cartCount() {
            return this.cart.reduce((total, item) => total + item.qty, 0);
        },

        get cartTotal() {
            return this.cart.reduce((total, item) => total + (Number(item.unit_price) * item.qty), 0);
        },

        openCart() {
            if (this.cart.length === 0) {
                return;
            }

            this.showCart = true;
        },

        proceedToCheckout() {
            if (this.cart.length === 0) {
                return;
            }

            this.showCart = false;
            this.showCheckout = true;
            this.error = '';
        },

        optionSummary(item) {
            return (item.options ?? []).map((option) => option.name).join(' · ');
        },

        async submitOrder() {
            if (this.submitting) {
                return;
            }

            this.error = '';

            if (this.customerName.trim() === '') {
                this.error = 'Nama pelanggan wajib diisi agar pesanan mudah dipanggil.';
                return;
            }

            if (this.cart.length === 0) {
                this.error = 'Keranjang masih kosong.';
                return;
            }

            if (this.paymentMethod === 'qris' && !this.payment.qris_enabled) {
                this.error = 'Pembayaran QRIS belum tersedia. Silakan pilih kartu EDC atau tunai di kasir.';
                return;
            }

            this.submitting = true;

            try {
                const response = await fetch(this.payment.checkout_url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        customer_name: this.customerName.trim(),
                        order_type: this.orderType,
                        payment_method: this.paymentMethod,
                        items: this.cart.map((item) => ({
                            product_id: item.product_id,
                            quantity: item.qty,
                            note: item.note || null,
                            options: item.options.map((option) => ({
                                group: option.group,
                                name: option.name,
                            })),
                        })),
                    }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstError = data.errors
                        ? Object.values(data.errors).flat()[0]
                        : null;

                    throw new Error(firstError || data.message || 'Gagal membuat pesanan.');
                }

                this.order = data.order;

                if (data.payment === 'cash') {
                    this.paymentState = 'cash';
                    this.cart = [];
                    this.showCheckout = false;
                    this.showConfirmation = true;
                    return;
                }

                if (data.payment === 'edc') {
                    // Tampilkan instruksi tap kartu pada terminal EDC kiosk.
                    this.paymentState = 'edc';
                    this.cart = [];
                    this.showCheckout = false;
                    this.showEdc = true;
                    return;
                }

                if (data.payment === 'qris' && data.payment_url) {
                    // Redirect ke halaman DOKU Checkout
                    window.location.href = data.payment_url;
                    return;
                }

                throw new Error('Respon pembayaran tidak valid.');
            } catch (error) {
                this.error = error.message || 'Terjadi kesalahan saat membuat pesanan.';
                this.showToast(this.error, 'error');
            } finally {
                this.submitting = false;
            }
        },

        finishQris() {
            this.cart = [];
            this.showCheckout = false;
            this.showConfirmation = true;
        },

        async simulateEdc() {
            if (this.edcSubmitting || !this.order?.edc_simulate_url) {
                return;
            }

            this.edcSubmitting = true;

            try {
                const response = await fetch(this.order.edc_simulate_url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message || 'Gagal mengonfirmasi pembayaran EDC.');
                }

                this.order = data.order;
                this.paymentState = 'paid';
                this.showEdc = false;
                this.showConfirmation = true;
            } catch (error) {
                this.showToast(error.message || 'Gagal mengonfirmasi pembayaran EDC.', 'error');
            } finally {
                this.edcSubmitting = false;
            }
        },

        async cancelEdc() {
            if (this.edcSubmitting) {
                return;
            }

            this.edcSubmitting = true;

            try {
                if (this.order?.cancel_url) {
                    await fetch(this.order.cancel_url, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        },
                    });
                }
            } catch (error) {
                // Batalkan tetap berjalan walau permintaan gagal; sesi kiosk
                // akan mengabaikan pesanan pending tersebut.
            } finally {
                this.edcSubmitting = false;
                this.showEdc = false;
                this.newOrder();
            }
        },

        newOrder() {
            this.started = false;
            this.orderType = null;
            this.showTypeModal = false;
            this.activeCategory = 'all';
            this.search = '';
            this.cart = [];
            this.showCart = false;
            this.selectedProduct = null;
            this.selectedQty = 1;
            this.selectedOptions = {};
            this.itemNote = '';
            this.showProductModal = false;
            this.showCheckout = false;
            this.customerName = '';
            this.paymentMethod = this.payment.qris_enabled ? 'qris' : 'cash';
            this.submitting = false;
            this.error = '';
            this.order = null;
            this.paymentState = 'unpaid';
            this.showConfirmation = false;
            this.showEdc = false;
            this.edcSubmitting = false;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        formatRupiah(value) {
            return `Rp ${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(value) || 0)}`;
        },

        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.visible = true;

            if (this.toastTimer) {
                clearTimeout(this.toastTimer);
            }

            this.toastTimer = setTimeout(() => {
                this.toast.visible = false;
            }, 3200);
        },
    };
}