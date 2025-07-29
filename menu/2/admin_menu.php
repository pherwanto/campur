<?php
// ### 1. SETUP DAN PROSES FORM ###
session_start();
require 'database.php';
require 'menu_functions.php'; // Pastikan file ini ada

$message = '';
$message_type = '';

// Ambil pesan dari session jika ada (setelah redirect)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Proses semua request POST di bagian atas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $location_id_redirect = $_POST['location_id'] ?? $_GET['location_id'] ?? '';

    try {
        switch ($action) {
            // == CRUD LOKASI ==
            case 'add_location':
                $slug = trim($_POST['slug']);
                if (empty($slug)) {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'])));
                }
                $stmt = $pdo->prepare("INSERT INTO menu_locations (name, slug) VALUES (:name, :slug)");
                $stmt->execute(['name' => $_POST['name'], 'slug' => $slug]);
                $_SESSION['message'] = 'Lokasi menu berhasil ditambahkan.';
                $_SESSION['message_type'] = 'success';
                break;

            case 'update_location':
                $stmt = $pdo->prepare("UPDATE menu_locations SET name = :name, slug = :slug WHERE id = :id");
                $stmt->execute(['name' => $_POST['name'], 'slug' => $_POST['slug'], 'id' => $_POST['id']]);
                $_SESSION['message'] = 'Lokasi menu berhasil diperbarui.';
                $_SESSION['message_type'] = 'success';
                break;

            case 'delete_location':
                // ON DELETE CASCADE akan menghapus semua item menu terkait
                $stmt = $pdo->prepare("DELETE FROM menu_locations WHERE id = :id");
                $stmt->execute(['id' => $_POST['id']]);
                $_SESSION['message'] = 'Lokasi menu dan semua item di dalamnya telah dihapus.';
                $_SESSION['message_type'] = 'success';
                $location_id_redirect = ''; // Reset pilihan
                break;

            // == CRUD ITEM MENU ==
            // GUNAKAN BLOK CASE YANG SUDAH DIPERBAIKI INI
			case 'add_item':
				$title = trim($_POST['title']);
				$type = $_POST['type'];
				$menu_location_id = $_POST['menu_location_id'];
				$link_id = NULL;
				$external_url = NULL;

				// Jika title kosong & tipenya adalah page atau post, ambil judul asli dari database
				if (empty($title) && ($type === 'page' || $type === 'post')) {
				// Tentukan tabel sumber berdasarkan tipe
				// PERHATIAN: Ganti 'pages' atau 'posts' jika nama tabel Anda berbeda
				$source_table = ($type === 'page') ? 'pages' : 'posts'; 
        
				$stmt_title = $pdo->prepare("SELECT title FROM `{$source_table}` WHERE id = :id");
				$stmt_title->execute(['id' => $_POST['link_id']]);
				$source_item = $stmt_title->fetch();
        
				// Gunakan judul dari sumber jika ditemukan
				$title = $source_item ? $source_item['title'] : 'Judul Tidak Ditemukan';
				}

				// Tetapkan link_id atau external_url
				if ($type === 'page' || $type === 'post') {
					$link_id = $_POST['link_id'];
				} else { // tipe adalah 'external'
					$external_url = $_POST['external_url'];
				}

				// Masukkan ke database dengan judul yang sudah benar
				$stmt = $pdo->prepare("INSERT INTO menu_items (menu_location_id, title, type, link_id, external_url) VALUES (:loc_id, :title, :type, :link_id, :url)");
				$stmt->execute([
					'loc_id' => $menu_location_id,
					'title' => $title, // Menggunakan variabel $title yang sudah diproses
					'type' => $type,
					'link_id' => $link_id,
					'url' => $external_url
				]);
				$_SESSION['message'] = 'Item menu berhasil ditambahkan.';
				$_SESSION['message_type'] = 'success';
			break;

            // GUNAKAN BLOK CASE BARU INI
			case 'update_item':
				$type = $_POST['type'];
				$link_id = NULL;
				$external_url = NULL;

				if ($type === 'page' || $type === 'post') {
					$link_id = $_POST['link_id'];
				} else {
					$external_url = $_POST['external_url'];
				}
    
				// Ambil data dari field-field baru
				$css_classes = $_POST['css_classes'] ?? '';
				$visibility = $_POST['visibility'] ?? 'all';
				// Jika checkbox dicentang, nilainya '_blank', jika tidak, kosong
				$target = isset($_POST['target']) ? '_blank' : '';

				$stmt = $pdo->prepare(
					"UPDATE menu_items SET 
					title = :title, 
					type = :type, 
					link_id = :link_id, 
					external_url = :url,
					css_classes = :css,
					target = :target,
					visibility = :visibility
					WHERE id = :id"
				);
				$stmt->execute([
					'title' => $_POST['title'],
					'type' => $type,
					'link_id' => $link_id,
					'url' => $external_url,
					'css' => $css_classes,
					'target' => $target,
					'visibility' => $visibility,
					'id' => $_POST['id']
				]);
				$_SESSION['message'] = 'Item menu berhasil diperbarui.';
				$_SESSION['message_type'] = 'success';
			break;

            // GUNAKAN BLOK CASE BARU YANG LEBIH BAIK INI
			case 'delete_item':
				$item_id_to_delete = $_POST['id'];
    
				// 1. Jadikan anak-anak dari item yang akan dihapus sebagai item root (induk)
				// Ini mencegah mereka menjadi "yatim piatu" di database.
				$stmt_update_children = $pdo->prepare("UPDATE menu_items SET parent_id = 0 WHERE parent_id = :parent_id_to_delete");
				$stmt_update_children->execute(['parent_id_to_delete' => $item_id_to_delete]);

				// 2. Sekarang, hapus item yang dimaksud
				$stmt_delete = $pdo->prepare("DELETE FROM menu_items WHERE id = :id");
				$stmt_delete->execute(['id' => $item_id_to_delete]);
    
				$_SESSION['message'] = 'Item menu berhasil dihapus.';
				$_SESSION['message_type'] = 'success';
			break;
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }

    // Redirect untuk mencegah re-submit form saat refresh
    header("Location: admin_menu.php" . ($location_id_redirect ? "?location_id=$location_id_redirect" : ""));
    exit;
}

// ### 2. PENGAMBILAN DATA UNTUK TAMPILAN ###
$locations = $pdo->query("SELECT * FROM menu_locations ORDER BY name ASC")->fetchAll();
$selected_location_id = $_GET['location_id'] ?? ($locations[0]['id'] ?? null);
$menu_items = [];
if ($selected_location_id) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE menu_location_id = :loc_id ORDER BY parent_id, menu_order ASC");
    $stmt->execute(['loc_id' => $selected_location_id]);
    $menu_items = $stmt->fetchAll();
}

// TODO: Ganti ini dengan query untuk mengambil data post dan page Anda
$sample_posts = [['id' => 101, 'title' => 'Judul Artikel Pertama'], ['id' => 102, 'title' => 'Tutorial PHP untuk Pemula']];
$sample_pages = [['id' => 1, 'title' => 'Beranda'], ['id' => 2, 'title' => 'Tentang Kami'], ['id' => 3, 'title' => 'Layanan']];


// GUNAKAN FUNGSI BARU YANG SUDAH DIPERBAIKI INI
function render_admin_menu_html_with_actions(array $items, int $parent_id = 0): string {
    $html = '<ol class="sortable">';
    foreach ($items as $item) {
        if ((int)$item['parent_id'] == $parent_id) {
            $html .= "<li id='menuItem_{$item['id']}'>";
            $html .= "<div class='d-flex justify-content-between align-items-center'>";
            $html .= "<span><i class='fas fa-arrows-alt-v mr-2' style='cursor: move;'></i><span class='menu-title'>{$item['title']}</span> <span class='menu-type'>({$item['type']})</span></span>";
            $html .= "<div class='item-actions'>";
            // TAMBAHKAN DATA BARU (css, target, visibility) KE TOMBOL EDIT
            $html .= "<button class='btn btn-sm btn-info mr-2 edit-item-btn' data-toggle='modal' data-target='#editItemModal' 
                        data-id='{$item['id']}' 
                        data-title='".htmlspecialchars($item['title'], ENT_QUOTES)."' 
                        data-type='{$item['type']}' 
                        data-linkid='{$item['link_id']}' 
                        data-url='".htmlspecialchars($item['external_url'], ENT_QUOTES)."'
                        data-css='".htmlspecialchars($item['css_classes'], ENT_QUOTES)."'
                        data-target='{$item['target']}'
                        data-visibility='{$item['visibility']}'>Edit</button>";
            $html .= "<form method='POST' action='admin_menu.php' class='d-inline' onsubmit='return confirm(\"Yakin ingin menghapus item ini?\");'>
                        <input type='hidden' name='action' value='delete_item'>
                        <input type='hidden' name='id' value='{$item['id']}'>
                        <input type='hidden' name='location_id' value='{$item['menu_location_id']}'>
                        <button type='submit' class='btn btn-sm btn-danger'>Hapus</button>
                      </form>";
            $html .= "</div>";
            $html .= "</div>";
            $html .= render_admin_menu_html_with_actions($items, (int)$item['id']);
            $html .= "</li>";
        }
    }
    $html .= '</ol>';
    return (strpos($html, '<li') === false) ? '<div class="alert alert-secondary">Belum ada item di menu ini.</div>' : $html;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manajemen Menu</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .sortable { list-style-type: none; margin: 0; padding: 0; }
        .sortable li { margin: 5px 0; padding: 10px; border: 1px solid #ddd; background: #fff; border-radius: 4px; }
        .sortable li > div { cursor: move; }
        .sortable ol { margin-top: 10px; padding-left: 30px; }
        .menu-type { font-weight: normal; color: #888; font-size: 0.9em; }
        .placeholder { height: 40px; background-color: #f0f9ff; border: 1px dashed #c4e4ff; }
        #response { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Manajemen Menu</h2>
        <hr>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Lokasi Menu yang Ada</div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tbody>
                                <?php foreach ($locations as $loc): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($loc['name']); ?></td>
                                    <td><code><?php echo htmlspecialchars($loc['slug']); ?></code></td>
                                    <td class="text-right">
                                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editLocationModal" 
                                                data-id="<?php echo $loc['id']; ?>" data-name="<?php echo htmlspecialchars($loc['name']); ?>" 
                                                data-slug="<?php echo htmlspecialchars($loc['slug']); ?>">Edit</button>
                                        <form method="POST" action="admin_menu.php" class="d-inline" onsubmit="return confirm('PERINGATAN: Menghapus lokasi akan menghapus SEMUA item menu di dalamnya. Lanjutkan?');">
                                            <input type="hidden" name="action" value="delete_location">
                                            <input type="hidden" name="id" value="<?php echo $loc['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                 <div class="card">
                    <div class="card-header">Tambah Lokasi Menu Baru</div>
                    <div class="card-body">
                        <form method="POST" action="admin_menu.php">
                            <input type="hidden" name="action" value="add_location">
                            <div class="form-group">
                                <label for="name">Nama Lokasi</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="slug">Slug (biarkan kosong untuk auto-generate)</label>
                                <input type="text" class="form-control" name="slug">
                            </div>
                            <button type="submit" class="btn btn-primary">Tambah Lokasi</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <hr>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Editor Menu</h4>
            <form method="GET" action="admin_menu.php" id="location_selector_form">
                <div class="form-inline">
                    <label for="location_id" class="mr-2">Pilih menu untuk diedit:</label>
                    <select name="location_id" id="location_id" class="form-control mr-2" onchange="this.form.submit()">
                        <?php foreach ($locations as $loc): ?>
                        <option value="<?php echo $loc['id']; ?>" <?php echo ($loc['id'] == $selected_location_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($loc['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($selected_location_id): ?>
        <div class="row">
            <div class="col-md-4">
                <h5>Tambah Item Menu</h5>
                <div class="accordion" id="addItemAccordion">
                    <div class="card">
                        <div class="card-header" id="headingPages">
                            <h2 class="mb-0"><button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapsePages">Halaman</button></h2>
                        </div>
                        <div id="collapsePages" class="collapse" data-parent="#addItemAccordion">
                            <div class="card-body">
                                <form method="POST" action="admin_menu.php">
                                    <input type="hidden" name="action" value="add_item">
                                    <input type="hidden" name="type" value="page">
                                    <input type="hidden" name="menu_location_id" value="<?php echo $selected_location_id; ?>">
                                    <div class="form-group">
                                        <label>Pilih Halaman</label>
                                        <select name="link_id" class="form-control" required>
                                            <?php foreach($sample_pages as $page): ?>
                                                <option value="<?php echo $page['id']; ?>"><?php echo $page['title']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                     <div class="form-group">
                                        <label>Teks Link (opsional)</label>
                                        <input type="text" name="title" class="form-control" placeholder="Gunakan judul halaman jika kosong">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">Tambah ke Menu</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                         <div class="card-header" id="headingPosts">
                            <h2 class="mb-0"><button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapsePosts">Post</button></h2>
                        </div>
                        <div id="collapsePosts" class="collapse" data-parent="#addItemAccordion">
                             <div class="card-body">
                                <form method="POST" action="admin_menu.php">
                                    <input type="hidden" name="action" value="add_item">
                                    <input type="hidden" name="type" value="post">
                                    <input type="hidden" name="menu_location_id" value="<?php echo $selected_location_id; ?>">
                                    <div class="form-group">
                                        <label>Pilih Post</label>
                                        <select name="link_id" class="form-control" required>
                                            <?php foreach($sample_posts as $post): ?>
                                                <option value="<?php echo $post['id']; ?>"><?php echo $post['title']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                     <div class="form-group">
                                        <label>Teks Link (opsional)</label>
                                        <input type="text" name="title" class="form-control" placeholder="Gunakan judul post jika kosong">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">Tambah ke Menu</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingCustom">
                            <h2 class="mb-0"><button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseCustom">Link Kustom</button></h2>
                        </div>
                        <div id="collapseCustom" class="collapse" data-parent="#addItemAccordion">
                            <div class="card-body">
                                <form method="POST" action="admin_menu.php">
                                    <input type="hidden" name="action" value="add_item">
                                    <input type="hidden" name="type" value="external">
                                    <input type="hidden" name="menu_location_id" value="<?php echo $selected_location_id; ?>">
                                    <div class="form-group">
                                        <label>URL</label>
                                        <input type="url" name="external_url" class="form-control" required placeholder="https://example.com">
                                    </div>
                                    <div class="form-group">
                                        <label>Teks Link</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">Tambah ke Menu</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <h5>Struktur Menu</h5>
                <div class="card">
                    <div class="card-body">
                        <?php echo render_admin_menu_html_with_actions($menu_items); ?>
                    </div>
                    <div class="card-footer">
                        <button id="saveMenu" class="btn btn-primary">Simpan Struktur Menu</button>
                        <div id="response" class="alert mt-3" style="display:none;"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">Pilih sebuah lokasi menu untuk mulai mengedit, atau buat lokasi baru.</div>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="editLocationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="admin_menu.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Lokasi Menu</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_location">
                        <input type="hidden" name="id" id="edit_location_id">
                        <div class="form-group">
                            <label for="name">Nama Lokasi</label>
                            <input type="text" class="form-control" name="name" id="edit_location_name" required>
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <input type="text" class="form-control" name="slug" id="edit_location_slug" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<div class="modal fade" id="editItemModal" tabindex="-1">
     <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="admin_menu.php">
                 <div class="modal-header">
                    <h5 class="modal-title">Edit Item Menu</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_item">
                    <input type="hidden" name="id" id="edit_item_id">
                    <input type="hidden" name="location_id" value="<?php echo $selected_location_id; ?>">
                    
                    <div class="form-group">
                        <label>Teks Link</label>
                        <input type="text" name="title" id="edit_item_title" class="form-control" required>
                    </div>

                    <input type="hidden" name="type" id="edit_item_type_hidden">

                    <div id="edit_fields_page_post" style="display:none;">
                         <div class="form-group">
                            <label>Link Ke</label>
                            <select name="link_id" id="edit_item_link_id" class="form-control"></select>
                        </div>
                    </div>
                     <div id="edit_fields_external" style="display:none;">
                        <div class="form-group">
                            <label>URL Kustom</label>
                            <input type="url" name="external_url" id="edit_item_external_url" class="form-control">
                        </div>
                    </div>

                    <hr>
                    <h5>Opsi Lanjutan</h5>

                    <div class="form-group">
                        <label for="edit_item_css">Kelas CSS (opsional)</label>
                        <input type="text" name="css_classes" id="edit_item_css" class="form-control" placeholder="cth: tombol-spesial">
                    </div>

                    <div class="form-group">
                        <label for="edit_item_visibility">Tampilkan untuk</label>
                        <select name="visibility" id="edit_item_visibility" class="form-control">
                            <option value="all">Semua Pengunjung</option>
                            <option value="logged_out">Hanya Pengunjung Tamu (Guest)</option>
                            <option value="logged_in">Hanya Pengguna yang Login</option>
                        </select>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="target" id="edit_item_target" class="form-check-input" value="_blank">
                        <label class="form-check-label" for="edit_item_target">
                            Buka link di tab baru
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nestedSortable/2.0.0/jquery.mjs.nestedSortable.min.js"></script>

    <script>
    $(document).ready(function(){
        // Inisialisasi Nested Sortable
        $('.sortable').nestedSortable({
            handle: 'div',
            items: 'li',
            toleranceElement: '> div',
            placeholder: 'placeholder',
            revert: 250,
            opacity: .6,
            maxLevels: 3 
        });

        // Simpan struktur menu via AJAX
        $('#saveMenu').click(function(){
            var serializedMenu = $('.sortable').nestedSortable('toArray', {startDepthCount: 0});
            
            $.ajax({
                url: 'save_menu_ajax.php',
                type: 'POST',
                data: { menu: serializedMenu },
                dataType: 'json',
                beforeSend: function() {
                    $('#response').removeClass('alert-success alert-danger').hide();
                    $('#saveMenu').text('Menyimpan...').prop('disabled', true);
                },
                success: function(response) {
                    $('#response').addClass(response.status === 'success' ? 'alert-success' : 'alert-danger')
                                 .text(response.message)
                                 .fadeIn();
                },
                error: function() {
                    $('#response').addClass('alert-danger')
                                 .text('Terjadi kesalahan saat menghubungi server.')
                                 .fadeIn();
                },
                complete: function() {
                    $('#saveMenu').text('Simpan Struktur Menu').prop('disabled', false);
                }
            });
        });

        // Modal Edit Lokasi: Isi data saat modal dibuka
        $('#editLocationModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var name = button.data('name');
            var slug = button.data('slug');

            var modal = $(this);
            modal.find('.modal-body #edit_location_id').val(id);
            modal.find('.modal-body #edit_location_name').val(name);
            modal.find('.modal-body #edit_location_slug').val(slug);
        });

        // Modal Edit Item: Isi data saat modal dibuka
	var allPages = <?php echo json_encode($sample_pages); ?>;
	var allPosts = <?php echo json_encode($sample_posts); ?>;

$('#editItemModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var modal = $(this);
    
    // Ambil semua data dari tombol, termasuk data baru
    var id = button.data('id');
    var title = button.data('title');
    var type = button.data('type');
    var linkid = button.data('linkid');
    var url = button.data('url');
    var css = button.data('css');
    var target = button.data('target');
    var visibility = button.data('visibility');
    
    // Isi field-field form
    modal.find('#edit_item_id').val(id);
    modal.find('#edit_item_title').val(title);
    modal.find('#edit_item_type_hidden').val(type);
    
    // Isi field-field baru
    modal.find('#edit_item_css').val(css);
    modal.find('#edit_item_visibility').val(visibility);
    modal.find('#edit_item_target').prop('checked', target === '_blank');

    // Sembunyikan/Tampilkan field berdasarkan tipe link
    $('#edit_fields_page_post, #edit_fields_external').hide();
    var select = modal.find('#edit_item_link_id');
    select.empty();

    if(type === 'page' || type === 'post') {
        var options = (type === 'page') ? allPages : allPosts;
        $.each(options, function(i, item) {
            select.append($('<option>', { value: item.id, text: item.title }));
        });
        select.val(linkid);
        $('#edit_fields_page_post').show();
    } else if (type === 'external') {
        modal.find('#edit_item_external_url').val(url);
        $('#edit_fields_external').show();
    }
});
    });
    </script>
</body>
</html>