<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Menu (Versi Perbaikan)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .menu-container {
            max-width: 700px;
            margin: auto;
        }
        .menu-list {
            list-style-type: none;
            padding-left: 0;
        }
        /* Styling untuk list di dalam list (submenu) */
        .menu-list .menu-list {
            margin-left: 35px;
            margin-top: 5px;
            border-left: 2px dashed #ccc;
            padding-left: 10px;
        }
        .menu-item {
            display: block;
            padding: 10px 15px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            margin-bottom: -1px; /* Membuat border menyatu */
            cursor: grab;
            position: relative;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        .menu-item .actions {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0; /* Sembunyikan tombol secara default */
            transition: opacity 0.2s ease-in-out;
        }
        .menu-item:hover .actions {
            opacity: 1; /* Tampilkan saat hover */
        }
        .menu-item-placeholder {
            background-color: #e6f7ff;
            border: 2px dashed #91d5ff;
            height: 45px;
            margin-bottom: 5px;
            border-radius: 4px;
        }
        /* (INI BAGIAN PENTING) Beri area drop pada list submenu yang kosong */
        .menu-item > .menu-list:empty {
            min-height: 30px; 
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="menu-container">
        <h2>Manajemen Menu</h2>
        <p>Atur struktur menu website Anda. Drag & drop untuk mengubah urutan. Drag sedikit ke kanan untuk menjadikannya submenu.</p>
        
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#menuModal" id="addMenuBtn">
            Tambah Item Menu Baru
        </button>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Main Menu</h4>
                        <button class="btn btn-success btn-sm save-structure-btn" data-position="main">Simpan Urutan Main Menu</button>
                    </div>
                    <div class="card-body">
                        <ul class="menu-list" id="main-menu-list" data-position="main">
                            </ul>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Footer Menu</h4>
                        <button class="btn btn-success btn-sm save-structure-btn" data-position="footer">Simpan Urutan Footer Menu</button>
                    </div>
                    <div class="card-body">
                        <ul class="menu-list" id="footer-menu-list" data-position="footer">
                            </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="menuModal" tabindex="-1" aria-labelledby="menuModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menuModalLabel">Tambah Item Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="menuForm">
                    <input type="hidden" id="menu-id" name="id">
                    <div class="mb-3">
                        <label for="menu-title" class="form-label">Judul Tampilan</label>
                        <input type="text" class="form-control" id="menu-title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="menu-position" class="form-label">Posisi Menu</label>
                        <select class="form-select" id="menu-position" name="position" required>
                            <option value="main">Main Menu</option>
                            <option value="footer">Footer Menu</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="menu-type" class="form-label">Tipe Link</label>
                        <select class="form-select" id="menu-type" name="type">
                            <option value="internal">Link Internal (Halaman)</option>
                            <option value="external">Link Eksternal (URL)</option>
                        </select>
                    </div>
                    <div id="internal-link-fields" class="mb-3">
                        <label for="menu-page-id" class="form-label">Pilih Halaman</label>
                        <select class="form-select" id="menu-page-id" name="page_id">
                            <option value="1">Beranda</option>
                            <option value="2">Tentang Kami</option>
                            <option value="3">Visi & Misi</option>
                            <option value="4">Layanan</option>
                            <option value="5">Kontak</option>
                            <option value="6">Bantuan</option>
                            <option value="7">Kebijakan Privasi</option>
                        </select>
                    </div>
                    <div id="external-link-fields" class="mb-3" style="display: none;">
                        <label for="menu-url" class="form-label">URL</label>
                        <input type="url" class="form-control" id="menu-url" name="url" placeholder="https://contoh.com">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="menuForm" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
$(document).ready(function() {
    
    // Fungsi untuk membuat elemen HTML menu
    // PERBAIKAN: Selalu buat <ul> untuk potensi submenu. Ini penting!
    function createMenuItem(item) {
        // SELALU buat <ul>. CSS akan menanganinya jika kosong.
        const sublist = '<ul class="menu-list"></ul>'; 
        const menuItem = $(`
            <li class="menu-item" data-id="${item.id}" data-page-id="${item.page_id}" data-url="${item.url}" data-type="${item.type}" data-title="${item.title}" data-position="${item.position}">
                <div>
                    <strong class="title">${item.title}</strong>
                    <small class="text-muted ms-2">(${item.type})</small>
                </div>
                <div class="actions">
                    <button class="btn btn-sm btn-outline-primary edit-btn">Edit</button>
                    <button class="btn btn-sm btn-outline-danger delete-btn">Hapus</button>
                </div>
                ${sublist}
            </li>
        `);
        
        // Jika item punya anak, render mereka di dalam <ul> yang sudah kita buat
        if (item.children && item.children.length > 0) {
            const sublistContainer = menuItem.find('.menu-list');
            item.children.forEach(child => sublistContainer.append(createMenuItem(child)));
        }
        return menuItem;
    }
    
    // Fungsi rekursif untuk menyusun data hierarki dari data flat
    function buildHierarchy(items) {
        const itemMap = {};
        items.forEach(item => itemMap[item.id] = { ...item, children: [] });
        
        const roots = [];
        items.forEach(item => {
            if (item.parent_id && itemMap[item.parent_id]) {
                // Pastikan anak diurutkan berdasarkan 'item_order'
                itemMap[item.parent_id].children.push(itemMap[item.id]);
                itemMap[item.parent_id].children.sort((a, b) => a.item_order - b.item_order);
            } else {
                roots.push(itemMap[item.id]);
            }
        });
        // Urutkan item root juga
        roots.sort((a,b) => a.item_order - b.item_order);
        return roots;
    }

    // Ambil dan Render Menu
    function loadAndRenderMenus() {
        $.ajax({
            url: 'api_menu.php',
            type: 'GET',
            data: { action: 'get_menus' },
            dataType: 'json',
            success: function(response) {
                $('#main-menu-list, #footer-menu-list').empty();

                const mainHierarchy = buildHierarchy(response.main);
                mainHierarchy.forEach(item => $('#main-menu-list').append(createMenuItem(item)));

                const footerHierarchy = buildHierarchy(response.footer);
                footerHierarchy.forEach(item => $('#footer-menu-list').append(createMenuItem(item)));

                initSortable();
            }
        });
    }

    // Inisialisasi Drag & Drop dengan SortableJS
    function initSortable() {
        document.querySelectorAll('.menu-list').forEach(function(list) {
            new Sortable(list, {
                group: 'nested', // Nama grup harus sama untuk bisa drag antar list
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                placeholderClass: 'menu-item-placeholder'
            });
        });
    }
    
    // Fungsi untuk mengubah struktur list menjadi data JSON
    function serializeList(list) {
        const serialized = [];
        $(list).children('li.menu-item').each(function() {
            const item = $(this);
            const childrenList = item.children('ul.menu-list');
            const data = { id: item.data('id') };
            if (childrenList.length > 0 && childrenList.children('li.menu-item').length > 0) {
                data.children = serializeList(childrenList);
            }
            serialized.push(data);
        });
        return serialized;
    }

    // --- EVENT HANDLERS (SEBAGIAN BESAR TETAP SAMA) ---

    $('#menu-type').on('change', function() {
        $('#internal-link-fields').toggle($(this).val() === 'internal');
        $('#external-link-fields').toggle($(this).val() === 'external');
    }).trigger('change');

    $('#addMenuBtn').on('click', function() {
        $('#menuModalLabel').text('Tambah Item Menu Baru');
        $('#menuForm')[0].reset();
        $('#menu-id').val('');
        $('#menu-type').trigger('change');
    });

    $('#menuForm').on('submit', function(e) {
        e.preventDefault();
        const data = $(this).serialize() + '&action=save_menu';
        $.ajax({
            url: 'api_menu.php', type: 'POST', data: data, dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $('#menuModal').modal('hide');
                    loadAndRenderMenus();
                }
            }
        });
    });

    $(document).on('click', '.edit-btn', function(e) {
        e.stopPropagation();
        const item = $(this).closest('.menu-item');
        $('#menuModalLabel').text('Edit Item Menu');
        $('#menu-id').val(item.data('id'));
        $('#menu-title').val(item.data('title'));
        $('#menu-position').val(item.data('position'));
        $('#menu-type').val(item.data('type')).trigger('change');
        $('#menu-page-id').val(item.data('page-id'));
        $('#menu-url').val(item.data('url'));
        $('#menuModal').modal('show');
    });

    $(document).on('click', '.delete-btn', function(e) {
        e.stopPropagation();
        if (confirm('Anda yakin ingin menghapus item menu ini? Semua submenu juga akan terhapus.')) {
            const id = $(this).closest('.menu-item').data('id');
            $.ajax({
                url: 'api_menu.php', type: 'POST', data: { action: 'delete_menu', id: id }, dataType: 'json',
                success: function(response) { if (response.success) loadAndRenderMenus(); }
            });
        }
    });
    
    $('.save-structure-btn').on('click', function() {
        const position = $(this).data('position');
        const listSelector = `#${position}-menu-list`;
        const structure = serializeList($(listSelector));

        $(this).prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: 'api_menu.php', type: 'POST',
            data: {
                action: 'save_structure', position: position,
                structure: JSON.stringify(structure)
            },
            dataType: 'json',
            success: (response) => {
                if (response.success) {
                    alert(response.message);
                    loadAndRenderMenus();
                } else {
                    alert('Gagal menyimpan struktur: ' + response.message);
                }
            },
            error: () => alert('Terjadi kesalahan saat berkomunikasi dengan server.'),
            complete: () => {
                $(this).prop('disabled', false).text(`Simpan Urutan ${position.charAt(0).toUpperCase() + position.slice(1)} Menu`);
            }
        });
    });

    // --- INISIALISASI ---
    loadAndRenderMenus();
});
</script>

</body>
</html>