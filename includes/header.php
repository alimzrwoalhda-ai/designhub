<?php
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'DesignHub Arabic';
$assetPath = $assetPath ?? '';
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="منصة عربية مجانية لرفع ومشاركة وتحميل التصاميم والقوالب الجاهزة.">
    <meta name="theme-color" content="#2563eb">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e($assetPath) ?>assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= e($assetPath) ?>index.php">DesignHub Arabic</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= e($assetPath) ?>index.php">الرئيسية</a></li>
                <?php if (is_logged_in()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e($assetPath) ?>dashboard.php">لوحة التحكم</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e($assetPath) ?>upload.php">رفع تصميم</a></li>
                    <?php if (is_admin()): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= e($assetPath) ?>admin/index.php">الأدمن</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <button class="theme-toggle" type="button" id="themeToggle" aria-label="تبديل الوضع">◐</button>
                <?php if (is_logged_in()): ?>
                    <span class="small text-muted"><?= e(current_user()['username']) ?></span>
                    <a class="btn btn-sm btn-outline-danger" href="<?= e($assetPath) ?>logout.php">خروج</a>
                <?php else: ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?= e($assetPath) ?>login.php">دخول</a>
                    <a class="btn btn-sm btn-primary" href="<?= e($assetPath) ?>register.php">حساب جديد</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<main>
