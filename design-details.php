<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    "SELECT d.*, c.name AS category_name, u.username, u.profile_image
     FROM designs d
     JOIN categories c ON c.id = d.category_id
     JOIN users u ON u.id = d.user_id
     WHERE d.id = ? AND (d.status = 'approved' OR d.user_id = ?)"
);
$stmt->execute([$id, current_user()['id'] ?? 0]);
$design = $stmt->fetch();
if (!$design) {
    http_response_code(404);
    exit('التصميم غير موجود.');
}

$pdo->prepare('UPDATE designs SET views = views + 1 WHERE id = ?')->execute([$id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    verify_csrf();
    $comment = trim($_POST['comment'] ?? '');
    if ($comment !== '') {
        $stmt = $pdo->prepare('INSERT INTO comments (user_id, design_id, comment) VALUES (?, ?, ?)');
        $stmt->execute([current_user()['id'], $id, $comment]);
        header('Location: design-details.php?id=' . $id);
        exit;
    }
}

$commentsStmt = $pdo->prepare(
    'SELECT c.*, u.username FROM comments c JOIN users u ON u.id = c.user_id WHERE c.design_id = ? ORDER BY c.created_at DESC'
);
$commentsStmt->execute([$id]);
$comments = $commentsStmt->fetchAll();

$similarStmt = $pdo->prepare(
    "SELECT id, title, preview_image FROM designs WHERE category_id = ? AND id != ? AND status = 'approved' ORDER BY created_at DESC LIMIT 3"
);
$similarStmt->execute([$design['category_id'], $id]);
$similar = $similarStmt->fetchAll();

$pageTitle = $design['title'];
include __DIR__ . '/includes/header.php';
?>
<section class="container py-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <img class="preview-large" data-lazy="true" data-src="<?= e($design['preview_image']) ?>" loading="lazy" alt="<?= e($design['title']) ?>">
        </div>
        <div class="col-lg-4">
            <div class="auth-panel">
                <span class="badge text-bg-primary mb-2"><?= e($design['category_name']) ?></span>
                <h1 class="h3"><?= e($design['title']) ?></h1>
                <p class="text-muted"><?= nl2br(e($design['description'])) ?></p>
                <div class="stat-box mb-3">
                    <strong><?= e($design['username']) ?></strong>
                    <div class="text-muted small">المشاهدات: <?= (int)$design['views'] + 1 ?> - التحميلات: <?= (int)$design['downloads'] ?></div>
                </div>
                <?php
                // File info
                $filePath = $design['design_file'];
                $size = is_file($filePath) ? filesize($filePath) : 0;
                function human_filesize($bytes, $decimals = 2) {
                    $sz = ['B','KB','MB','GB','TB'];
                    $factor = floor((strlen((string)$bytes) - 1) / 3);
                    return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $sz[$factor]);
                }
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                ?>
                <div class="mb-2 small text-muted">حجم الملف: <?= e(human_filesize($size)) ?> • الصيغة: <?= e($ext) ?></div>
                <a class="btn btn-success w-100" href="download.php?id=<?= (int)$design['id'] ?>">تحميل مجاني</a>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-3">
        <div class="col-lg-8">
            <h2 class="h4 mb-3">التعليقات</h2>
            <?php if (is_logged_in()): ?>
                <form class="auth-panel mb-3" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <textarea class="form-control mb-2" name="comment" rows="3" placeholder="اكتب تعليقك"></textarea>
                    <button class="btn btn-primary btn-sm">إضافة تعليق</button>
                </form>
            <?php endif; ?>
            <?php foreach ($comments as $comment): ?>
                <div class="stat-box mb-2">
                    <strong><?= e($comment['username']) ?></strong>
                    <p class="mb-0 text-muted"><?= nl2br(e($comment['comment'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="col-lg-4">
            <h2 class="h4 mb-3">تصاميم مشابهة</h2>
            <?php foreach ($similar as $item): ?>
                <a class="stat-box d-flex gap-3 mb-2 text-decoration-none" href="design-details.php?id=<?= (int)$item['id'] ?>">
                    <img src="<?= e($item['preview_image']) ?>" alt="" style="width:72px;height:52px;object-fit:cover;border-radius:6px">
                    <strong><?= e($item['title']) ?></strong>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
