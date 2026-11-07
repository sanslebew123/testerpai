# PAI Asisten (PHP + OpenRouter)

Alur: `index.php` (sambutan + tombol "Mari Bertanya") -> `tanya.php` (chat AI).

## Pasang di XAMPP
1. Salin folder `pai-asisten` ke `C:\xampp\htdocs\`.
2. Aktifkan Apache di XAMPP, pastikan `extension=curl` dan `extension=mbstring` aktif di php.ini.
3. Buka http://localhost/pai-asisten/
4. Edit `config/config.php` (di VS Code) untuk semua kustomisasi:
   - `landing`  : teks & warna halaman depan, tujuan tombol
   - `site`     : judul, font, logo navbar
   - `theme`    : warna halaman tanya jawab
   - `image`    : gambar latar (assets/img/img.jpg)
   - `openrouter`, `system_prompt`, `knowledge` : AI dan datanya

## Struktur
index.php, tanya.php, api/chat.php, config/config.php, config/.htaccess,
assets/css/{style,landing}.css, assets/js/{app,landing}.js, assets/img/*
