<?php
header('Content-Type: application/json');
require 'database.php';

// Ambil data JSON yang dikirim dari AJAX
$menuData = $_POST['menu'] ?? null;

if (!$menuData) {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada data menu yang diterima.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Loop melalui setiap item menu yang diterima
    foreach ($menuData as $order => $item) {
        // Pastikan item tidak kosong dan memiliki id
        if (empty($item['id'])) continue;

        $itemId = $item['id'];
		$parentId = !empty($item['parent_id']) ? (int)$item['parent_id'] : 0;

        // Update parent_id dan menu_order di database
        $stmt = $pdo->prepare("UPDATE menu_items SET parent_id = :parent_id, menu_order = :menu_order WHERE id = :id");
        $stmt->execute([
            'parent_id' => $parentId,
            'menu_order' => $order,
            'id' => $itemId
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Struktur menu berhasil disimpan!']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan menu: ' . $e->getMessage()]);
}
?>