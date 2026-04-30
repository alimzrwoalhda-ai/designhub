<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    verify_csrf();
    $designId = (int)($_POST['design_id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM designs WHERE id = ? AND user_id = ?');
    $stmt->execute([$designId, current_user()['id']]);
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT d.*, c.name AS category_name FROM designs d
     JOIN categories c ON c.id = d.category_id
     WHERE d.user_id = ? ORDER BY d.created_at DESC'
);
$stmt->execute([current_user()['id']]);
$designs = $stmt->fetchAll();

$stats = [
    'designs' => count($designs),
    'downloads' => array_sum(array_column($designs, 'downloads')),
    'views' => array_sum(array_column($designs, 'views')),
];

$pageTitle = 'لوحة التحكم';
include __DIR__ . '/includes/header.php';
?>
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 m-0">لوحة التحكم</h1>
        <a class="btn btn-primary" href="upload.php">رفع تصميم</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="stat-box"><strong><?= (int)$stats['designs'] ?></strong><br><span class="text-muted">تصميم</span></div></div>
        <div class="col-md-4"><div class="stat-box"><strong><?= (int)$stats['downloads'] ?></strong><br><span class="text-muted">تحميل</span></div></div>
        <div class="col-md-4"><div class="stat-box"><strong><?= (int)$stats['views'] ?></strong><br><span class="text-muted">مشاهدة</span></div></div>
    </div>

    <div class="table-responsive auth-panel">
        <table class="table align-middle">
            <thead><tr><th>التصميم</th><th>القسم</th><th>الحالة</th><th>إحصائيات</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($designs as $design): ?>
                <tr>
                    <td><strong><?= e($design['title']) ?></strong></td>
                    <td><?= e($design['category_name']) ?></td>
                    <td><span class="badge text-bg-secondary"><?= e($design['status']) ?></span></td>
                    <td><?= (int)$design['views'] ?> مشاهدة / <?= (int)$design['downloads'] ?> تحميل</td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="design-details.php?id=<?= (int)$design['id'] ?>">عرض</a>
                        <form class="d-inline" method="post">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="design_id" value="<?= (int)$design['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" data-confirm="حذف التصميم؟">حذف</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$designs): ?><tr><td colspan="5" class="text-center text-muted">لم ترفع أي تصميم بعد.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
