<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM designs WHERE id = ? AND status = 'approved'");
$stmt->execute([$id]);
$design = $stmt->fetch();

if (!$design) {
    http_response_code(404);
    exit('الملف غير موجود.');
}

// Resolve file path and ensure it's inside uploads/files
$filePath = $design['design_file'];
$real = realpath($filePath);
$uploadsDir = realpath(__DIR__ . '/uploads/files');
if ($real === false || $uploadsDir === false || strpos($real, $uploadsDir) !== 0 || !is_file($real)) {
    http_response_code(404);
    exit('الملف غير موجود أو غير مصرح بالوصول.');
}

// Update stats
$pdo->prepare('UPDATE designs SET downloads = downloads + 1 WHERE id = ?')->execute([$id]);
$pdo->prepare('INSERT INTO downloads (user_id, design_id) VALUES (?, ?)')->execute([current_user()['id'] ?? null, $id]);

// Determine content type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = $finfo ? finfo_file($finfo, $real) : 'application/octet-stream';
if ($finfo) finfo_close($finfo);

// Create friendly download name from title
$ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
$safeTitle = preg_replace('/[^A-Za-z0-9\-\_\s\p{Arabic}]/u', '', $design['title']);
$safeTitle = trim(preg_replace('/\s+/', '-', $safeTitle));
$clientName = ($safeTitle ?: 'design') . '.' . $ext;

// Send headers and stream file
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $clientName . '"');
header('Content-Length: ' . filesize($real));
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');

$fp = fopen($real, 'rb');
if ($fp) {
    while (!feof($fp)) {
        echo fread($fp, 8192);
        flush();
    }
    fclose($fp);
}
exit;
