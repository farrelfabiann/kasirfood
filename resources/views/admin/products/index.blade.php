<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Kelola Produk Makanan/Minuman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .option-group-box { background: #f8f9fa; border: 1px dashed #ced4da; border-radius: 8px; padding: 12px; margin-bottom: 10px; }
    </style>
</head>
<body class="bg-light">

    <!-- Header Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-speedometer2 text-primary me-2"></i>ADMIN PANEL - FOOD POS</a>
            <div class="d-flex align-items-center gap-3">
                <a href="/" class="btn btn-outline-warning btn-sm"><i class="bi bi-shop me-1"></i> Ke Halaman Kasir</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if (session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo session('success'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0"><i class="bi bi-box-seam me-2 text-primary"></i>Kelola Menu Makanan & Minuman</h4>
            <button class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Menu Baru
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">Gambar</th>
                                <th>Nama Menu</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Opsi Additional</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($products) && count($products) > 0): ?>
                                <?php foreach ($products as$product): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <img src="<?php echo $product->image; ?>" alt="<?php echo $product->name; ?>" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo $product->name; ?></div>
                                            <small class="text-muted"><?php echo $product->description; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo isset($product->category) ?$product->category->name : 'Uncategorized'; ?></span>
                                        </td>
                                        <td class="fw-bold text-primary">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></td>
                                        <td>
                                            <?php 
                                                $opts = is_string($product->additional_options) ? json_decode($product->additional_options, true) :$product->additional_options;
                                            ?>
                                            <?php if (!empty($opts) && is_array($opts) && count($opts) > 0): ?>
                                                <span class="badge bg-info text-dark"><i class="bi bi-check-circle me-1"></i><?php echo count($opts); ?> Grup Opsi</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border">Tanpa Opsi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($product->is_available): ?>
                                                <span class="badge bg-success">Tersedia</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Habis</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $product->id; ?>">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <form action="<?php echo route('admin.products.destroy', $product->id); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Hapus</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editModal<?php echo $product->id; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form action="<?php echo route('admin.products.update', $product->id); ?>" method="POST" enctype="multipart/form-data" class="edit-form" data-id="<?php echo $product->id; ?>">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="options_json" id="options_json_edit_<?php echo $product->id; ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Edit Menu: <?php echo $product->name; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold">Nama Menu</label>
                                                                <input type="text" name="name" class="form-control" value="<?php echo $product->name; ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold">Kategori</label>
                                                                <select name="category_id" class="form-select">
                                                                    <option value="">-- Pilih Kategori --</option>
                                                                    <?php if (isset($categories)): ?>
                                                                        <?php foreach ($categories as$cat): ?>
                                                                            <option value="<?php echo $cat->id; ?>" <?php echo $product->category_id ==$cat->id ? 'selected' : ''; ?>><?php echo $cat->name; ?></option>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold">Harga (Rp)</label>
                                                                <input type="number" name="price" class="form-control" value="<?php echo $product->price; ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold">Ganti Foto (Opsional)</label>
                                                                <input type="file" name="image" class="form-control" accept="image/*">
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label small fw-bold">Deskripsi</label>
                                                                <textarea name="description" class="form-control" rows="2"><?php echo $product->description; ?></textarea>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox" name="is_available" value="1" <?php echo $product->is_available ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label fw-bold small">Status Tersedia</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <hr class="my-4">

                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label fw-bold m-0 text-dark"><i class="bi bi-sliders me-1"></i> Kustomisasi Opsi Additional</label>
                                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addGroup('edit', <?php echo $product->id; ?>)">
                                                                <i class="bi bi-plus-circle me-1"></i> Tambah Grup Opsi
                                                            </button>
                                                        </div>
                                                        <p class="text-muted small mb-3">Kosongkan jika menu ini tidak memiliki pilihan topping / kustomisasi tambahan.</p>

                                                        <div id="groups_container_edit_<?php echo $product->id; ?>"></div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada produk. Silakan tambahkan produk baru.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Produk Baru -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="<?php echo route('admin.products.store'); ?>" method="POST" enctype="multipart/form-data" id="createProductForm">
                    @csrf
                    <input type="hidden" name="options_json" id="options_json_create_">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Tambah Menu Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nama Menu</label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: Es Kopi Kenangan" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Kategori</label>
                                <select name="category_id" class="form-select">
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php if (isset($categories)): ?>
                                        <?php foreach ($categories as$cat): ?>
                                            <option value="<?php echo $cat->id; ?>"><?php echo $cat->name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Harga (Rp)</label>
                                <input type="number" name="price" class="form-control" placeholder="20000" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Upload Foto Produk</label>
                                <input type="file" name="image" class="form-control" accept="image/*" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi singkat produk..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_available" value="1" checked>
                                    <label class="form-check-label fw-bold small">Status Tersedia</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold m-0 text-dark"><i class="bi bi-sliders me-1"></i> Kustomisasi Opsi Additional</label>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addGroup('create', '')">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Grup Opsi
                            </button>
                        </div>
                        <p class="text-muted small mb-3">Kosongkan jika menu ini tidak memiliki pilihan topping / kustomisasi tambahan.</p>

                        <div id="groups_container_create_"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold">Tambah Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const existingProductsData = <?php echo json_encode($products ?? []); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            const createForm = document.getElementById('createProductForm');
            if (createForm) {
                createForm.addEventListener('submit', function() {
                    buildJsonOptions('create', '');
                });
            }

            document.querySelectorAll('.edit-form').forEach(form => {
                form.addEventListener('submit', function() {
                    const id = this.getAttribute('data-id');
                    buildJsonOptions('edit', id);
                });
            });

            // Populate data opsi saat modal edit dimuat
            existingProductsData.forEach(product => {
                const container = document.getElementById(`groups_container_edit_${product.id}`);
                if (container) {
                    let groups = product.additional_options;
                    if (typeof groups === 'string') {
                        try { groups = JSON.parse(groups); } catch(e) { groups = []; }
                    }
                    if (Array.isArray(groups)) {
                        groups.forEach(group => {
                            addGroup('edit', product.id, group);
                        });
                    }
                }
            });
        });

        function addGroup(mode, id, existingGroupData = null) {
            const container = document.getElementById(`groups_container_${mode}_${id}`);
            if (!container) return;
            const gIdx = container.children.length;

            const groupDiv = document.createElement('div');
            groupDiv.className = 'option-group-box group-item';

            const gName = existingGroupData ? (existingGroupData.groupName || '') : '';
            const gType = existingGroupData ? (existingGroupData.type || 'radio') : 'radio';

            // PERBAIKAN MASALAH 1: Struktur Judul & Tipe ditaruh di ATAS sebelum daftar item pilihan
            groupDiv.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold small text-primary"><i class="bi bi-folder2-open me-1"></i> Grup Opsi #${gIdx + 1}</span>
                    <button type="button" class="btn btn-link text-danger p-0 btn-sm text-decoration-none fw-semibold" onclick="this.closest('.option-group-box').remove()">
                        <i class="bi bi-trash me-1"></i> Hapus Grup
                    </button>
                </div>
                <!-- 1. JUDUL GRUP & TIPE DI ATAS -->
                <div class="row g-2 mb-2">
                    <div class="col-md-7">
                        <label class="form-label small text-muted mb-1">Nama Grup Opsi</label>
                        <input type="text" class="form-control form-control-sm group-name-input" placeholder="Nama Grup (misal: Ice / Topping)" value="${gName}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small text-muted mb-1">Tipe Pilihan</label>
                        <select class="form-select form-select-sm group-type-input">
                            <option value="radio" ${gType === 'radio' ? 'selected' : ''}>Pilihan Tunggal (Radio)</option>
                            <option value="checkbox" ${gType === 'checkbox' ? 'selected' : ''}>Centang Banyak (Checkbox)</option>
                        </select>
                    </div>
                </div>
                <!-- 2. DAFTAR PILIHAN ITEM DI BAWAH JUDUL GRUP -->
                <div class="ps-2 border-start border-3 border-primary ms-1 my-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Daftar Pilihan / Items:</label>
                    <div class="options-item-container"></div>
                    <!-- 3. TOMBOL TAMBAH ITEM PADA BAGIAN BAWAH -->
                    <button type="button" class="btn btn-light btn-sm text-primary fw-semibold mt-1 py-0 px-2" style="font-size: 0.8rem;" onclick="addOptionItem(this)">
                        <i class="bi bi-plus me-1"></i> Tambah Item Pilihan
                    </button>
                </div>
            `;

            // PERBAIKAN MASALAH 2: Masukkan elemen groupDiv ke DOM DULUAN baru panggil addOptionItem()
            container.appendChild(groupDiv);

            const addBtn = groupDiv.querySelector('.ps-2 button');
            if (existingGroupData && Array.isArray(existingGroupData.options) && existingGroupData.options.length > 0) {
                existingGroupData.options.forEach(opt => {
                    addOptionItem(addBtn, opt.name, opt.price);
                });
            } else {
                addOptionItem(addBtn);
            }
        }

        function addOptionItem(btn, name = '', price = 0) {
            const container = btn.previousElementSibling;
            if (!container) return;

            const itemRow = document.createElement('div');
            itemRow.className = 'row g-2 mb-1 align-items-center option-row';
            itemRow.innerHTML = `
                <div class="col-7">
                    <input type="text" class="form-control form-control-sm opt-name-input" placeholder="Nama Opsi" value="${name}" required>
                </div>
                <div class="col-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text px-1">Rp</span>
                        <input type="number" class="form-control form-control-sm opt-price-input" placeholder="Harga" value="${price}" min="0" required>
                    </div>
                </div>
                <div class="col-1 text-end p-0">
                    <button type="button" class="btn btn-link text-danger p-0 btn-sm" onclick="this.closest('.option-row').remove()">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            `;
            container.appendChild(itemRow);
        }

        function buildJsonOptions(mode, id) {
            const container = document.getElementById(`groups_container_${mode}_${id}`);
            if (!container) return;
            const groupElements = container.querySelectorAll('.group-item');
            const result = [];

            groupElements.forEach(groupEl => {
                const groupName = groupEl.querySelector('.group-name-input').value.trim();
                const type = groupEl.querySelector('.group-type-input').value;
                const optionRows = groupEl.querySelectorAll('.option-row');
                const options = [];

                optionRows.forEach(row => {
                    const optName = row.querySelector('.opt-name-input').value.trim();
                    const price = parseInt(row.querySelector('.opt-price-input').value) || 0;
                    if (optName) {
                        options.push({ name: optName, price: price });
                    }
                });

                if (groupName && options.length > 0) {
                    result.push({
                        groupName: groupName,
                        type: type,
                        options: options
                    });
                }
            });

            const targetInput = document.getElementById(`options_json_${mode}_${id}`);
            if (targetInput) {
                targetInput.value = JSON.stringify(result);
            }
        }
    </script>
</body>
</html>