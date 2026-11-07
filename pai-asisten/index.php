<?php
declare(strict_types=1);

$config = require __DIR__ . '/config/config.php';
$L      = $config['landing'];
$C      = $L['colors'];
$logos  = $config['site']['logos'] ?? [];

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="<?= e($config['site']['lang']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($L['title']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Serif+Display&family=Open+Sans:wght@600;700&display=swap">
    <link rel="stylesheet" href="assets/css/landing.css">
    <link rel="prefetch" href="<?= e($L['target']) ?>">
    <style>
        :root {
            --utama: <?= e($C['main']) ?>;
            --sedang: <?= e($C['mid']) ?>;
            --muda: <?= e($C['light']) ?>;
            --tombol: <?= e($C['button']) ?>;
            --teks: <?= e($C['text']) ?>;
        }
    </style>
</head>
<body>

<section class="home">
    <div class="panel" aria-hidden="true">
        <svg viewBox="0 0 600 1000" preserveAspectRatio="none">
            <path d="M0 0H560C470 220 520 640 700 1000H0Z" fill="var(--muda)"/>
            <path d="M0 0H500C420 230 470 650 640 1000H0Z" fill="var(--sedang)"/>
            <path d="M0 0H440C370 240 420 660 590 1000H0Z" fill="var(--utama)"/>
        </svg>
    </div>

    <div class="logos">
        <?php foreach ($logos as $lg): ?><img src="<?= e($lg) ?>" alt="Logo"><?php endforeach; ?>
    </div>

    <div class="main">
        <h1><?= e($L['title']) ?></h1>
        <p class="sub"><?= e($L['subtitle']) ?></p>
    </div>

    <div class="cta-wrap">
        <span class="ring"></span><span class="ring r2"></span>
        <a class="cta" id="cta" href="<?= e($L['target']) ?>">
            <span><?= e($L['button']) ?></span>
            <svg class="panah" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</section>

<div id="wipe"></div>
<script src="assets/js/landing.js"></script>
</body>
</html>
