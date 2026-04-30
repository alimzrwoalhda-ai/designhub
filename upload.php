<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $keywords = trim($_POST['keywords'] ?? '');

    if ($title === '' || $description === '' || $categoryId < 1) {
        $errors[] = 'يرجى تعبئة اسم التصميم والوصف والتصنيف.';
    }

    $preview = upload_file($_FILES['preview_image'] ?? [], 'uploads/previews', ['jpg', 'jpeg', 'png', 'webp'], 3145728);
    $designFile = upload_file($_FILES['design_file'] ?? [], 'uploads/files', ['zip', 'rar', 'pdf', 'psd', 'ai', 'fig', 'xd', 'pptx'], 52428800);

    if (!$preview) {
        $errors[] = 'صورة المعاينة يجب أن تكون JPG أو PNG أو WEBP وبحجم مناسب.';
    }
    if (!$designFile) {
        $errors[] = 'ملف التصميم غير صالح. الصيغ المسموحة: zip, rar, pdf, psd, ai, fig, xd, pptx.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO designs (user_id, category_id, title, description, preview_image, design_file, keywords)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([current_user()['id'], $categoryId, $title, $description, $preview, $designFile, $keywords]);
        header('Location: dashboard.php');
        exit;
    }
}

$pageTitle = 'رفع تصميم';
include __DIR__ . '/includes/header.php';
?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="auth-panel">
                <h1 class="h3 mb-4">رفع تصميم جديد</h1>
                <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="mb-3"><label class="form-label">اسم التصميم</label><input class="form-control" name="title" required></div>
                    <div class="mb-3"><label class="form-label">الوصف</label><textarea class="form-control" name="description" rows="5" required></textarea></div>
                    <div class="mb-3">
                        <label class="form-label">التصنيف</label>
                        <select class="form-select" name="category_id" required>
                            <option value="">اختر التصنيف</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int)$category['id'] ?>"><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">صورة معاينة</label><input class="form-control" type="file" name="preview_image" accept="image/*" required></div>
                    <div class="mb-3"><label class="form-label">ملف التصميم</label><input class="form-control" type="file" name="design_file" required></div>
                    <div class="mb-3"><label class="form-label">كلمات مفتاحية</label><input class="form-control" name="keywords" placeholder="شعار، عربي، PSD"></div>
                    <button class="btn btn-primary">نشر التصميم</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
