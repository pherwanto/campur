<?php
/**
 * Mengambil item menu dari database untuk lokasi tertentu.
 * Tidak menyusunnya secara hierarkis, karena kita akan melakukannya di fungsi render.
 */
function get_menu_items_for_location(PDO $pdo, string $location_slug): array {
    $stmt = $pdo->prepare("
        SELECT mi.*
        FROM menu_items mi
        JOIN menu_locations ml ON mi.menu_location_id = ml.id
        WHERE ml.slug = :slug
        ORDER BY mi.parent_id, mi.menu_order ASC
    ");
    $stmt->execute(['slug' => $location_slug]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fungsi rekursif untuk menampilkan menu di halaman admin (untuk drag-and-drop).
 */
function render_admin_menu_html(array $items, int $parent_id = 0): string {
    $html = '<ol class="sortable">';
    foreach ($items as $item) {
        if ($item['parent_id'] == $parent_id) {
            $html .= "<li id='menuItem_{$item['id']}'>";
            $html .= "<div><span class='menu-title'>{$item['title']}</span> <span class='menu-type'>({$item['type']})</span></div>";
            
            // Panggil rekursif untuk anak-anaknya
            $html .= render_admin_menu_html($items, $item['id']);
            
            $html .= "</li>";
        }
    }
    $html .= '</ol>';
    return $html;
}

/**
 * Fungsi rekursif untuk menampilkan menu di halaman publik (frontend).
 */

/**
 * Fungsi rekursif untuk menampilkan menu di halaman publik (frontend).
 * SUDAH DIPERBARUI DENGAN FITUR BARU
 */
function render_public_menu_html(array $items, int $parent_id = 0): string {
    // Simulasi status login, ganti dengan logika sesi Anda
    if (session_status() == PHP_SESSION_NONE) { session_start(); }
    $is_logged_in = !empty($_SESSION['user_id']); // Ganti 'user_id' dengan key sesi Anda

    $html = '<ul>';
    foreach ($items as $item) {
        if ((int)$item['parent_id'] == $parent_id) {
            
            // LOGIKA VISIBILITAS: Cek apakah item boleh ditampilkan
            $can_show = false;
            if ($item['visibility'] === 'all' ||
               ($item['visibility'] === 'logged_in' && $is_logged_in) ||
               ($item['visibility'] === 'logged_out' && !$is_logged_in)) {
                $can_show = true;
            }

            if ($can_show) {
                // Tentukan URL
                $url = '#';
                switch ($item['type']) {
                    case 'page': $url = "page.php?id={$item['link_id']}"; break;
                    case 'post': $url = "post.php?id={$item['link_id']}"; break;
                    case 'external': $url = $item['external_url']; break;
                }
                
                // Atribut tambahan
                $li_class = !empty($item['css_classes']) ? 'class="'.htmlspecialchars($item['css_classes']).'"' : '';
                $a_target = !empty($item['target']) ? 'target="'.htmlspecialchars($item['target']).'"' : '';

                // Render item
                $html .= "<li {$li_class}>";
                $html .= "<a href='{$url}' {$a_target}>{$item['title']}</a>";
                
                // Rekursi untuk submenu
                $html .= render_public_menu_html($items, (int)$item['id']); 
                $html .= "</li>";
            }
        }
    }
    $html .= '</ul>';
    
    return (strpos($html, '<li>') === false) ? '' : $html;
}
?>
