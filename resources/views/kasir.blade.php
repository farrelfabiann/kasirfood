<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir & Pemesanan Makanan - Point of Sale</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Midtrans Snap JS -->
    <script type="text/javascript"
            src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-brand { font-weight: 700; letter-spacing: 0.5px; }
        .product-card { transition: transform 0.2s, box-shadow 0.2s; border: none; border-radius: 10px; overflow: hidden; }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.1) !important; }
        .badge-category { position: absolute; top: 10px; left: 10px; font-size: 0.75rem; padding: 5px 10px; border-radius: 20px; }
        .cart-card { border: none; border-radius: 12px; }
        .cart-header { border-bottom: 2px solid #f0f0f0; background: #ffffff; border-radius: 12px 12px 0 0 !important; }
        .btn-checkout { background-color: #0d6efd; border: none; font-weight: 600; padding: 12px; border-radius: 8px; }
        .btn-checkout:hover { background-color: #0b5ed7; }
        .cart-item-note { font-size: 0.75rem; color: #6c757d; }
        .nav-pills .nav-link.active { background-color: #0d6efd; }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <i class="bi bi-shop fs-4 text-primary"></i>
                <span>FOOD ORDER SYSTEM</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-warning btn-sm fw-semibold" onclick="openHistoryModal()">
                    <i class="bi bi-clock-history me-1"></i> Riwayat Penjualan
                </button>
                <a href="/admin/products" class="btn btn-outline-light btn-sm text-decoration-none">
                    <i class="bi bi-speedometer2 me-1"></i> Admin Panel
                </a>
                <span class="badge bg-success px-3 py-2"><i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> System Online</span>
                <span class="text-light small"><i class="bi bi-person-circle me-1"></i> Kasir Utama</span>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">
        <div class="row g-4">
            <!-- Kolom Kiri: Katalog Menu Makanan -->
            <div class="col-lg-8">
                <!-- Top Filter & Search Bar -->
                <div class="card border-0 shadow-sm mb-4 p-3 rounded-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <ul class="nav nav-pills small" id="categoryTabs">
                                <li class="nav-item">
                                    <button class="nav-link active py-1 px-3 fw-semibold" onclick="filterCategory('all', this)">Semua Menu</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link py-1 px-3 fw-semibold" onclick="filterCategory('makanan', this)">Makanan</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link py-1 px-3 fw-semibold" onclick="filterCategory('minuman', this)">Minuman</button>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="searchMenu" class="form-control border-start-0" placeholder="Cari nama makanan / minuman..." onkeyup="searchProduct()">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0 text-secondary"><i class="bi bi-grid-fill me-2"></i>Katalog Menu</h5>
                    <span class="text-muted small">Menampilkan <?php echo count($products); ?> item tersedia</span>
                </div>

                <div class="row g-3" id="productGrid">
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <?php
                                $catName = isset($product->category) ? $product->category->name : '';
                                $pName = strtolower($product->name);
                                $isDrink = (
                                    str_contains(strtolower($catName), 'minuman')
                                    || preg_match('/\bes\b/', $pName)
                                    || str_contains($pName, 'teh')
                                    || str_contains($pName, 'kopi')
                                    || str_contains($pName, 'jus')
                                );
                                $itemType = $isDrink ? 'minuman' : 'makanan';
                            ?>
                            <div class="col-sm-6 col-md-4 product-item" data-category="<?php echo $itemType; ?>" data-name="<?php echo strtolower($product->name); ?>">
                                <div class="card h-100 shadow-sm product-card position-relative">
                                    <span class="badge bg-dark badge-category opacity-75">
                                        <?php echo $catName ? $catName : ucfirst($itemType); ?>
                                    </span>
                                    <img src="<?php echo $product->image; ?>" class="card-img-top" style="height: 140px; object-fit: cover;" alt="<?php echo $product->name; ?>">
                                    <div class="card-body d-flex flex-column justify-content-between p-3">
                                        <div>
                                            <h6 class="fw-bold mb-1 text-dark"><?php echo $product->name; ?></h6>
                                            <p class="text-muted small mb-2 text-truncate"><?php echo $product->description; ?></p>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-primary fs-6 mb-2">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></div>
                                            <button class="btn btn-outline-primary btn-sm w-100 fw-semibold"
                                                    data-name="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-price="<?php echo $product->price; ?>"
                                                    data-options='<?php echo htmlspecialchars(json_encode($product->additional_options ?? []), ENT_QUOTES, "UTF-8"); ?>'
                                                    onclick="openOptionModal(this)">
                                                <i class="bi bi-plus-lg"></i> Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning text-center py-4" role="alert">
                                <i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>
                                Data produk belum dimasukkan. Silakan tambahkan produk di Admin Panel.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kolom Kanan: Rincian Kasir / Checkout -->
            <div class="col-lg-4">
                <div class="card shadow-sm cart-card">
                    <div class="card-header cart-header py-3 px-4">
                        <h5 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-receipt text-primary"></i> Detail Transaksi
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="checkout-form">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Nama Pemesan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" id="customer_name" class="form-control" placeholder="Input nama pelanggan" required>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Tipe Pesanan</label>
                                    <select id="order_type" class="form-select" onchange="toggleTableInput()" required>
                                        <option value="dine_in">Dine-In</option>
                                        <option value="online">Takeaway / Online</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="table_container">
                                    <label class="form-label small fw-bold text-secondary">No. Meja</label>
                                    <input type="text" id="table_number" class="form-control" placeholder="Contoh: 05">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Metode Pembayaran</label>
                                <select id="payment_method" class="form-select">
                                    <option value="qris">QRIS / Online (Midtrans)</option>
                                    <option value="cash">Tunai / Cash</option>
                                </select>
                            </div>

                            <hr class="my-3 text-muted">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small text-secondary">Item Pesanan</span>
                                <button type="button" class="btn btn-link text-danger btn-sm p-0 text-decoration-none small" onclick="clearCart()">Clear All</button>
                            </div>

                            <div style="max-height: 250px; overflow-y: auto;" class="mb-3">
                                <ul id="cart-list" class="list-group list-group-flush border-top border-bottom">
                                    <li class="list-group-item text-muted text-center py-3 small">
                                        <i class="bi bi-cart-x d-block fs-4 text-secondary mb-1"></i>
                                        Belum ada menu yang dipilih
                                    </li>
                                </ul>
                            </div>

                            <div class="bg-light p-3 rounded-3 mb-3">
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>Subtotal</span>
                                    <span id="subtotal-text">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center fw-bold fs-5 text-dark mt-2 border-top pt-2">
                                    <span>Total Bayar</span>
                                    <span class="text-primary" id="total-price-text">Rp 0</span>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary btn-checkout w-100 d-flex align-items-center justify-content-center gap-2" id="pay-button" onclick="payNow()">
                                <i class="bi bi-qr-code-scan fs-5"></i>
                                <span>Proses Pembayaran</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Kustomisasi Additional -->
    <div class="modal fade" id="optionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalProductName">Kustomisasi Additional</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modalProductPrice">
                    
                    <div id="additionalGroupsContainer"></div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold text-secondary">Catatan Khusus (Optional)</label>
                        <input type="text" id="specialNote" class="form-control form-control-sm" placeholder="Contoh: Esnya dikit aja">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm px-4 fw-bold" onclick="confirmAddToCart()">Tambahkan ke Pesanan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Riwayat Penjualan & Pemasukan -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-clock-history me-2 text-warning"></i>Riwayat Penjualan & Pemasukan Harian</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Cards Summary -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3">
                                <span class="text-muted small fw-semibold d-block">Pemasukan Hari Ini</span>
                                <h4 class="fw-bold text-success m-0 mt-1" id="summary-today-income">Rp 0</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3">
                                <span class="text-muted small fw-semibold d-block">Transaksi Lunas (Paid)</span>
                                <h4 class="fw-bold text-primary m-0 mt-1" id="summary-paid-count">0 Transaksi</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3">
                                <span class="text-muted small fw-semibold d-block">Transaksi Pending</span>
                                <h4 class="fw-bold text-warning m-0 mt-1" id="summary-pending-count">0 Transaksi</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Table Order History -->
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-hover align-middle">
                            <thead class="table-light sticky-top">
                                <tr class="small text-secondary">
                                    <th>No. Order</th>
                                    <th>Pelanggan</th>
                                    <th>Tipe</th>
                                    <th>Total Bayar</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="history-table-body">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Memuat data riwayat...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let cart = [];
        let totalAmount = 0;
        let activeModal = null;
        let isProcessingPayment = false;

        function setPayButtonBusy(busy) {
            const payBtn = document.getElementById('pay-button');
            if (!payBtn) return;
            payBtn.disabled = busy;
            payBtn.style.pointerEvents = busy ? 'none' : '';
        }

        function resetSnapUi() {
            try {
                if (typeof snap !== 'undefined' && typeof snap.hide === 'function') {
                    snap.hide();
                }
            } catch (e) {}

            document.querySelectorAll('#snap-midtrans, iframe[src*="midtrans"], .snap-overlay').forEach((el) => {
                try { el.remove(); } catch (e) {}
            });
        }

        function releasePaymentLock() {
            isProcessingPayment = false;
            setPayButtonBusy(false);
            resetSnapUi();
        }

        function filterCategory(cat, btn) {
            document.querySelectorAll('#categoryTabs .nav-link').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const items = document.querySelectorAll('.product-item');
            items.forEach(item => {
                if (cat === 'all' || item.getAttribute('data-category') === cat) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function searchProduct() {
            const query = document.getElementById('searchMenu').value.toLowerCase();
            const items = document.querySelectorAll('.product-item');

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                if (name.includes(query)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function openOptionModal(btn) {
            const name = btn.getAttribute('data-name');
            const price = parseInt(btn.getAttribute('data-price'));
            let customOptions = [];

            try {
                const rawOptions = btn.getAttribute('data-options');
                if (rawOptions) {
                    customOptions = JSON.parse(rawOptions);
                }
            } catch (e) {
                customOptions = [];
            }

            document.getElementById('modalProductName').innerText = name;
            document.getElementById('modalProductPrice').value = price;
            document.getElementById('specialNote').value = '';

            const container = document.getElementById('additionalGroupsContainer');
            container.innerHTML = '';

            const groups = (Array.isArray(customOptions) && customOptions.length > 0) ? customOptions : [];

            if (groups.length === 0) {
                container.innerHTML = '<p class="text-muted small text-center py-2 mb-0"><i class="bi bi-info-circle me-1"></i> Tidak ada opsi tambahan untuk menu ini.</p>';
            } else {
                groups.forEach((group, gIdx) => {
                    let groupHTML = `
                        <div class="mb-3 p-3 bg-light rounded border">
                            <label class="form-label small fw-bold text-dark mb-2">${group.groupName}</label>`;
                    
                    group.options.forEach((opt, oIdx) => {
                        const inputId = `opt_${gIdx}_${oIdx}`;
                        const inputType = group.type === 'radio' ? 'radio' : 'checkbox';
                        const inputName = `group_${gIdx}`;
                        const isChecked = (group.type === 'radio' && oIdx === 0) ? 'checked' : '';

                        groupHTML += `
                            <div class="form-check me-3 mb-1">
                                <input class="form-check-input additional-option" 
                                       type="${inputType}" 
                                       name="${inputName}" 
                                       id="${inputId}" 
                                       value="${opt.name}" 
                                       data-price="${opt.price}" ${isChecked}>
                                <label class="form-check-label small" for="${inputId}">
                                    ${opt.name} ${opt.price > 0 ? `(+Rp ${opt.price.toLocaleString('id-ID')})` : ''}
                                </label>
                            </div>`;
                    });

                    groupHTML += `</div>`;
                    container.innerHTML += groupHTML;
                });
            }

            activeModal = new bootstrap.Modal(document.getElementById('optionModal'));
            activeModal.show();
        }

        function confirmAddToCart() {
            const name = document.getElementById('modalProductName').innerText;
            let basePrice = parseInt(document.getElementById('modalProductPrice').value);
            const note = document.getElementById('specialNote').value;

            let selectedOptions = [];
            let additionalPrice = 0;

            const checkboxes = document.querySelectorAll('.additional-option:checked');
            checkboxes.forEach(cb => {
                const optName = cb.value;
                const optPrice = parseInt(cb.getAttribute('data-price'));
                selectedOptions.push(optName);
                additionalPrice += optPrice;
            });

            if (note) {
                selectedOptions.push(`Note: "${note}"`);
            }

            const detailString = selectedOptions.join(', ');
            const finalPrice = basePrice + additionalPrice;

            const existingIndex = cart.findIndex(item => item.name === name && item.details === detailString);

            if (existingIndex > -1) {
                cart[existingIndex].qty += 1;
            } else {
                cart.push({
                    name: name,
                    price: finalPrice,
                    details: detailString,
                    qty: 1
                });
            }

            activeModal.hide();
            calculateTotal();
            updateCartUI();
        }

        function changeQty(index, delta) {
            cart[index].qty += delta;
            if (cart[index].qty <= 0) {
                cart.splice(index, 1);
            }
            calculateTotal();
            updateCartUI();
        }

        function removeItem(index) {
            cart.splice(index, 1);
            calculateTotal();
            updateCartUI();
        }

        function clearCart() {
            cart = [];
            totalAmount = 0;
            updateCartUI();
        }

        function calculateTotal() {
            totalAmount = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        }

        function toggleTableInput() {
            const type = document.getElementById('order_type').value;
            const container = document.getElementById('table_container');
            if (type === 'online') {
                container.style.display = 'none';
            } else {
                container.style.display = 'block';
            }
        }

        function updateCartUI() {
            const cartList = document.getElementById('cart-list');
            const totalPriceText = document.getElementById('total-price-text');
            const subtotalText = document.getElementById('subtotal-text');
            cartList.innerHTML = '';

            if (cart.length === 0) {
                cartList.innerHTML = `
                    <li class="list-group-item text-muted text-center py-3 small">
                        <i class="bi bi-cart-x d-block fs-4 text-secondary mb-1"></i>
                        Belum ada menu yang dipilih
                    </li>`;
            } else {
                cart.forEach((item, index) => {
                    cartList.innerHTML += `
                    <li class="list-group-item px-0 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <div class="fw-semibold small text-dark">${item.name}</div>
                                <div class="cart-item-note">${item.details}</div>
                                <div class="text-primary small fw-bold mt-1">Rp ${(item.price * item.qty).toLocaleString('id-ID')}</div>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-0" onclick="changeQty(${index}, -1)">-</button>
                                <span class="small fw-bold px-1">${item.qty}</span>
                                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-0" onclick="changeQty(${index}, 1)">+</button>
                                <button type="button" class="btn btn-outline-danger btn-sm px-2 py-0 ms-1" onclick="removeItem(${index})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </li>`;
                });
            }
            totalPriceText.innerText = `Rp ${totalAmount.toLocaleString('id-ID')}`;
            subtotalText.innerText = `Rp ${totalAmount.toLocaleString('id-ID')}`;
        }

        function payNow() {
            if (isProcessingPayment) return;

            const nameInput = document.getElementById('customer_name');
            const name = nameInput ? nameInput.value.trim() : '';
            const type = document.getElementById('order_type').value;
            const table = document.getElementById('table_number').value;
            const paymentMethod = document.getElementById('payment_method').value;

            if (!name) {
                alert('Harap isi nama pelanggan terlebih dahulu!');
                if (nameInput) nameInput.focus();
                return;
            }

            if (cart.length === 0 || totalAmount <= 0) {
                alert('Keranjang masih kosong! Silakan pilih minimal 1 menu makanan/minuman.');
                return;
            }

            isProcessingPayment = true;
            setPayButtonBusy(true);

            fetch("{{ route('checkout') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    customer_name: name,
                    order_type: type,
                    table_number: table,
                    total_amount: totalAmount,
                    payment_method: paymentMethod,
                    cart_items: cart
                })
            })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(data.message || ('Gagal checkout (HTTP ' + res.status + ')'));
                }
                return data;
            })
            .then(data => {
                if (data.payment === 'cash') {
                    alert("Pembayaran Tunai Berhasil Diproses!");
                    clearCart();
                    if (nameInput) nameInput.value = '';
                    releasePaymentLock();
                    return;
                }

                if (!data.snap_token) {
                    throw new Error(data.message || 'Snap token tidak diterima dari server.');
                }

                resetSnapUi();

                try {
                    snap.pay(data.snap_token, {
                        onSuccess: function() {
                            alert("Pembayaran QRIS Berhasil!");
                            clearCart();
                            if (nameInput) nameInput.value = '';
                            releasePaymentLock();
                        },
                        onPending: function() {
                            alert("Transaksi dicatat. Menunggu pembayaran QRIS.");
                            clearCart();
                            if (nameInput) nameInput.value = '';
                            releasePaymentLock();
                        },
                        onError: function(result) {
                            releasePaymentLock();
                            alert("Pembayaran QRIS gagal. Silakan coba lagi.");
                        },
                        onClose: function() {
                            releasePaymentLock();

                            if (!data.order || !data.order.order_number) return;

                            fetch("{{ route('cancel.order') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    order_number: data.order.order_number
                                })
                            })
                            .then(res => res.json())
                            .then(resData => {
                                console.log("Pembatalan diproses:", resData);
                            })
                            .catch(err => {
                                console.error("Gagal membatalkan:", err);
                            });
                        }
                    });
                } catch (snapErr) {
                    releasePaymentLock();
                    alert("Gagal membuka jendela Midtrans.");
                }
            })
            .catch(err => {
                releasePaymentLock();
                alert(err.message || "Terjadi kesalahan saat memproses pembayaran.");
            });
        }

        function openHistoryModal() {
            const modal = new bootstrap.Modal(document.getElementById('historyModal'));
            modal.show();

            const tableBody = document.getElementById('history-table-body');
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Memuat data riwayat...</td></tr>';

            fetch("{{ route('orders.history') }}")
                .then(res => res.json())
                .then(data => {
                    document.getElementById('summary-today-income').innerText = `Rp ${parseInt(data.today_income).toLocaleString('id-ID')}`;
                    document.getElementById('summary-paid-count').innerText = `${data.paid_count} Transaksi`;
                    document.getElementById('summary-pending-count').innerText = `${data.pending_count} Transaksi`;

                    tableBody.innerHTML = '';
                    if (!data.orders || data.orders.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat transaksi.</td></tr>';
                        return;
                    }

                    data.orders.forEach(order => {
                        let statusBadge = '';
                        if (order.status === 'paid') {
                            statusBadge = '<span class="badge bg-success">Paid (Lunas)</span>';
                        } else if (order.status === 'pending') {
                            statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
                        } else {
                            statusBadge = '<span class="badge bg-danger">Cancelled</span>';
                        }

                        const date = new Date(order.created_at);
                        const formattedDate = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                        tableBody.innerHTML += `
                            <tr>
                                <td class="fw-bold small text-primary">${order.order_number}</td>
                                <td class="fw-semibold small">${order.customer_name}</td>
                                <td class="small text-capitalize">${order.order_type.replace('_', ' ')} ${order.table_number ? `(Meja ${order.table_number})` : ''}</td>
                                <td class="fw-bold small">Rp ${parseInt(order.total_amount).toLocaleString('id-ID')}</td>
                                <td>${statusBadge}</td>
                                <td class="small text-muted">${formattedDate}</td>
                            </tr>
                        `;
                    });
                })
                .catch(err => {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Gagal mengambil data riwayat.</td></tr>';
                });
        }
    </script>

</body>
</html>