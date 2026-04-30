<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return is_logged_in() && current_user()['role'] === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: ../index.php');
        exit;
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        exit('طلب غير صالح. يرجى تحديث الصفحة والمحاولة مرة أخرى.');
    }
}

function upload_file(array $file, string $targetDir, array $allowedExtensions, int $maxSize = 10485760): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    if (($file['size'] ?? 0) > $maxSize) {
        return null;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return null;
    }

    // Basic MIME/type checks
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
    if ($finfo) finfo_close($finfo);

    // Deny executable/script uploads by MIME
    $denyMimes = [
        'text/x-php', 'application/x-php', 'application/php', 'application/x-httpd-php'
    ];
    if (in_array($mime, $denyMimes, true)) {
        return null;
    }

    // For image previews, ensure actual image
    $imageMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (in_array($extension, ['jpg','jpeg','png','webp'], true)) {
        if (!in_array($mime, $imageMimes, true)) return null;
        if (getimagesize($file['tmp_name']) === false) return null;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }

    // Ensure non-executable permissions
    @chmod($destination, 0644);

    return str_replace('\\', '/', $destination);
}
