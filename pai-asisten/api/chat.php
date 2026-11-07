<?php
declare(strict_types=1);

/**
 * Endpoint chat: menerima riwayat pesan dari browser,
 * meneruskannya ke OpenRouter, lalu mengembalikan jawaban AI.
 * API key hanya ada di server ini.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $code, array $data): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond(405, ['error' => 'Metode tidak diizinkan.']);
}

$config = require __DIR__ . '/../config/config.php';
$or     = $config['openrouter'];

$apiKey = trim((string) $or['api_key']);
if ($apiKey === '' || str_contains($apiKey, 'ISI_API_KEY')) {
    respond(500, ['error' => 'API key belum diisi. Buka config/config.php lalu isi openrouter.api_key.']);
}

// Pembatas sederhana: minimal 1 detik antar permintaan per sesi
session_start();
$now = microtime(true);
if (isset($_SESSION['last_req']) && ($now - $_SESSION['last_req']) < 1.0) {
    respond(429, ['error' => 'Terlalu cepat, tunggu sebentar.']);
}
$_SESSION['last_req'] = $now;
session_write_close();

// Baca & bersihkan input
$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input) || !isset($input['messages']) || !is_array($input['messages'])) {
    respond(400, ['error' => 'Format permintaan tidak valid.']);
}

$history = [];
foreach ($input['messages'] as $m) {
    if (!is_array($m)) continue;
    $role    = $m['role'] ?? '';
    $content = trim((string) ($m['content'] ?? ''));
    if (!in_array($role, ['user', 'assistant'], true) || $content === '') continue;
    $history[] = ['role' => $role, 'content' => mb_substr($content, 0, 2000)];
}
$history = array_slice($history, -max(1, (int) $or['history_limit']));

if (!$history || end($history)['role'] !== 'user') {
    respond(400, ['error' => 'Pesan kosong.']);
}

$systemPrompt = (string) $config['system_prompt'];
$knowledge    = trim((string) ($config['knowledge'] ?? ''));

if ($knowledge !== '') {
    $systemPrompt .= "\n\nGunakan DATA berikut sebagai sumber utama jawaban. "
                   . "Jika jawabannya tidak ada di data, katakan tidak tahu "
                   . "dan sarankan menghubungi admin prodi.\n\n" . $knowledge;
}

$messages = array_merge(
    [['role' => 'system', 'content' => $systemPrompt]],
    $history
);

$payload = [
    'model'       => $or['model'],
    'messages'    => $messages,
    'temperature' => (float) $or['temperature'],
    'max_tokens'  => (int) $or['max_tokens'],
];
if (!empty($or['fallback_models'])) {
    $payload['models'] = array_merge([$or['model']], $or['fallback_models']);
}

$ch = curl_init($or['endpoint']);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => (int) $or['timeout'],
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'HTTP-Referer: ' . $or['site_url'],
        'X-Title: ' . $or['site_name'],
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
]);

$raw    = curl_exec($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err    = curl_error($ch);
curl_close($ch);

if ($raw === false) {
    respond(502, ['error' => 'Gagal menghubungi OpenRouter: ' . $err]);
}

$data = json_decode($raw, true);

if ($status >= 400 || !is_array($data)) {
    $msg = $data['error']['message'] ?? 'OpenRouter mengembalikan error (HTTP ' . $status . ').';
    respond($status >= 400 ? $status : 502, ['error' => $msg]);
}

$reply = $data['choices'][0]['message']['content'] ?? '';
if (trim((string) $reply) === '') {
    respond(502, ['error' => 'Model tidak mengembalikan jawaban.']);
}

respond(200, ['reply' => $reply, 'model' => $data['model'] ?? $or['model']]);
