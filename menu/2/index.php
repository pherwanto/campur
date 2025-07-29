<?php
require 'database.php';
require 'menu_functions.php';

// Ambil semua item untuk 'main-menu'
$menu_items = get_menu_items_for_location($pdo, 'main-menu');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Website Publik</title>
    <style>
        /* CSS Sederhana untuk menu publik */
        nav ul { list-style: none; padding: 0; margin: 0; display: flex; background: #333; }
        nav ul li { position: relative; }
        nav ul li a { display: block; padding: 15px 20px; color: white; text-decoration: none; }
        nav ul li a:hover { background: #555; }
        /* Sembunyikan sub-menu */
        nav ul ul { display: none; position: absolute; top: 100%; left: 0; background: #444; flex-direction: column; }
        /* Tampilkan sub-menu saat hover */
        nav ul li:hover > ul { display: flex; }
    </style>
</head>
<body>
    <header>
        <h1>Selamat Datang di Website Kami</h1>
        <nav>
            <?php
            // Tampilkan menu publik
            if (!empty($menu_items)) {
                echo render_public_menu_html($menu_items);
            }
            ?>
        </nav>
    </header>
    <main style="padding: 20px;">
        <h2>Konten Halaman</h2>
        <p>Ini adalah halaman utama website.</p>
    </main>
</body>
</html>