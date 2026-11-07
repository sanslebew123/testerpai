<?php
declare(strict_types=1);

$config = require __DIR__ . '/config/config.php';

$site  = $config['site'];
$hero  = $config['hero'];
$theme = $config['theme'];
$image = $config['image'];
$nav   = $config['nav'];
$sec   = $config['sections'];

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$font = $site['font'];
$fontUrl = 'https://fonts.googleapis.com/css2?family=' . str_replace(' ', '+', $font) . ':wght@400;500;600;700&display=swap';

$fontD = $site['font_display'] ?? $font;
$fontDUrl = 'https://fonts.googleapis.com/css2?family=' . str_replace(' ', '+', $fontD) . '&display=swap';

$imageOk = $image['enabled'] && is_file(__DIR__ . '/' . $image['src']);

$jsConfig = [
    'endpoint'    => 'api/chat.php',
    'greeting'    => $hero['greeting'],
    'errorText'   => $hero['error_text'],
    'loadingText' => $hero['loading'],
];
?>
<!DOCTYPE html>
<html lang="<?= e($site['lang']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($site['title']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= e($fontUrl) ?>">
    <link rel="stylesheet" href="<?= e($fontDUrl) ?>">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --font: '<?= e($font) ?>', system-ui, -apple-system, 'Segoe UI', sans-serif;
            --font-display: '<?= e($fontD) ?>', Georgia, serif;
            --primary: <?= e($theme['primary']) ?>;
            --brand: <?= e($theme['brand']) ?>;
            --navy: <?= e($theme['navy']) ?>;
            --text: <?= e($theme['text']) ?>;
            --muted: <?= e($theme['muted']) ?>;
            --border: <?= e($theme['border']) ?>;
            --bg-top: <?= e($theme['bg_top']) ?>;
            --bg-bottom: <?= e($theme['bg_bottom']) ?>;
            --blob: <?= e($theme['blob']) ?>;
            --bubble-bot: <?= e($theme['bubble_bot']) ?>;
            --bubble-user: <?= e($theme['bubble_user']) ?>;
            --bubble-user-text: <?= e($theme['bubble_user_text']) ?>;
            --container: <?= e($theme['container']) ?>;
            --chat-width: <?= e($theme['chat_width']) ?>;
            --radius: <?= e($theme['radius']) ?>;
            --img-opacity: <?= (float) $image['opacity'] ?>;
            --img-blur: <?= e($image['blur']) ?>;
        }
    </style>
</head>
<body>

<!-- Lapisan latar: gradasi + bercak biru + gambar img.jpg -->
<div class="bg-layer" aria-hidden="true">
    <?php if ($imageOk): ?>
        <div class="bg-image" style="background-image:url('<?= e($image['src']) ?>');background-size:<?= e($image['size']) ?>;background-position:<?= e($image['position']) ?>"></div>
    <?php endif; ?>
    <div class="bg-blob"></div>
</div>

<!-- NAVBAR -->
<header class="navbar">
    <a class="brand" href="#">
        <?php if (!empty($site['logos'])): ?>
            <span class="brand-logos">
                <?php foreach ($site['logos'] as $lg): ?><img src="<?= e($lg) ?>" alt=""><?php endforeach; ?>
            </span>
        <?php elseif ($site['logo_src'] !== ''): ?>
            <img class="brand-logo" src="<?= e($site['logo_src']) ?>" alt="">
        <?php else: ?>
            <svg class="brand-icon" viewBox="0 0 32 28" width="30" height="27" aria-hidden="true">
                <path fill="currentColor" d="M16 27.2C14.9 26.3 3.2 17.9 1.4 11.2 0 5.9 3.6 1 9 1c3 0 5.4 1.5 7 4 1.6-2.5 4-4 7-4 5.4 0 9 4.9 7.6 10.2C28.8 17.9 17.1 26.3 16 27.2z"/>
                <path fill="#fff" fill-opacity=".9" d="M7.5 8.2c1-1.5 2.8-2 4.2-1.3-1.7.1-3 1-3.7 2.6-.4.8-1 .5-.5-1.3z"/>
            </svg>
        <?php endif; ?>
        <span class="brand-text"><?= e($site['brand']) ?></span>
    </a>

    <nav class="nav-links">
        <?php foreach ($nav as $item): ?>
            <a href="<?= e($item['href']) ?>" class="<?= !empty($item['button']) ? 'nav-btn' : 'nav-link' ?>"><?= e($item['label']) ?></a>
        <?php endforeach; ?>
    </nav>
</header>

<main>
    <!-- HERO + CHAT -->
    <section class="hero" id="tanya">
        <h1 class="headline"><?= e($hero['headline']) ?></h1>

        <div class="chat">
            <div class="thread" id="thread" aria-live="polite"></div>

            <div class="composer">
                <textarea id="input" rows="1" placeholder="<?= e($hero['placeholder']) ?>" aria-label="Pesan"></textarea>

                <div class="composer-actions">
                    <div class="composer-left">
                        <button type="button" class="pill-btn" id="btn-new">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                            <?= e($hero['btn_new']) ?>
                        </button>
                        <button type="button" class="pill-btn" id="btn-visi">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M6 13l6 6 6-6"/></svg>
                            <?= e($hero['btn_visi']) ?>
                        </button>
                    </div>
                    <button type="button" class="send-btn" id="btn-send" aria-label="Kirim">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                    </button>
                </div>
            </div>

            <div class="chips">
                <?php foreach ($config['suggestions'] as $s): ?>
                    <button type="button" class="chip" data-q="<?= e($s) ?>"><?= e($s) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <a href="#tujuan" class="scroll-down" aria-label="Gulir ke bawah">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </a>
    </section>

    <!-- TUJUAN PRODI -->
    <section class="info" id="tujuan">
        <div class="info-card">
            <h2><?= e($sec['tujuan']['title']) ?></h2>
            <ul>
                <?php foreach ($sec['tujuan']['items'] as $it): ?>
                    <li><?= e($it) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- VISI & MISI -->
    <section class="info" id="visi-misi">
        <div class="info-card">
            <h2><?= e($sec['visi_misi']['title']) ?></h2>
            <h3>Visi</h3>
            <p><?= e($sec['visi_misi']['visi']) ?></p>
            <h3>Misi</h3>
            <ol>
                <?php foreach ($sec['visi_misi']['misi'] as $it): ?>
                    <li><?= e($it) ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>
</main>

<script>window.CHAT_CONFIG = <?= json_encode($jsConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
<script src="assets/js/app.js"></script>
</body>
</html>
