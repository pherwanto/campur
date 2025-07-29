<?php
// Ganti dengan koneksi database Anda
$host = 'localhost';
$db   = 'menu';
$user = 'root';
$pass = '134nj4r4n';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_menus':
        $stmt = $pdo->query("SELECT * FROM menus ORDER BY item_order ASC");
        $menus = $stmt->fetchAll();
        // Mengelompokkan menu berdasarkan posisi
        $grouped_menus = ['main' => [], 'footer' => []];
        foreach ($menus as $menu) {
            $grouped_menus[$menu['position']][] = $menu;
        }
        echo json_encode($grouped_menus);
        break;

    case 'save_menu':
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'];
        $type = $_POST['type'];
        $page_id = ($type == 'internal') ? $_POST['page_id'] : null;
        $url = ($type == 'external') ? $_POST['url'] : null;
        $position = $_POST['position'];

        if ($id) { // Update
            $sql = "UPDATE menus SET title=?, type=?, page_id=?, url=?, position=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $type, $page_id, $url, $position, $id]);
        } else { // Insert
            $sql = "INSERT INTO menus (title, type, page_id, url, position, parent_id) VALUES (?, ?, ?, ?, ?, 0)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $type, $page_id, $url, $position]);
        }
        echo json_encode(['success' => true]);
        break;

    case 'delete_menu':
        $id = $_POST['id'];
        $sql = "DELETE FROM menus WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        // Juga hapus semua submenu yang terkait
        $sql_child = "DELETE FROM menus WHERE parent_id = ?";
        $stmt_child = $pdo->prepare($sql_child);
        $stmt_child->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'save_structure':
        $structure = json_decode($_POST['structure'], true);
        $position = $_POST['position'];
        
        $pdo->beginTransaction();
        try {
            foreach ($structure as $order => $item) {
                // Update parent dan order untuk item utama
                $sql = "UPDATE menus SET parent_id = 0, item_order = ?, position = ? WHERE id = ?";
                $pdo->prepare($sql)->execute([$order, $position, $item['id']]);

                // Jika ada submenu
                if (!empty($item['children'])) {
                    update_children($item['children'], $item['id'], $pdo);
                }
            }
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Struktur menu berhasil disimpan.']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
}

// Fungsi rekursif untuk update submenu
function update_children($children, $parent_id, $pdo) {
    foreach ($children as $order => $item) {
        $sql = "UPDATE menus SET parent_id = ?, item_order = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$parent_id, $order, $item['id']]);

        if (!empty($item['children'])) {
            update_children($item['children'], $item['id'], $pdo);
        }
    }
}
?>