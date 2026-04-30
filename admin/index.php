<?php
$assetPath = '../';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_design') {
        $stmt = $pdo->prepare('DELETE FROM designs WHERE id = ?');
        $stmt->execute([(int)($_POST['design_id'] ?? 0)]);
    }

    if ($action === 'update_status') {
        $status = $_POST['status'] ?? 'approved';
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $stmt = $pdo->prepare('UPDATE designs SET status = ? WHERE id = ?');
            $stmt->execute([$status, (int)($_POST['design_id'] ?? 0)]);
        }
    }

    if ($action === 'delete_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId !== (int)current_user()['id']) {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role != "admin"');
            $stmt->execute([$userId]);
        }
    }

    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $stmt = $pdo->prepare('INSERT IGNORE INTO categories (name) VALUES (?)');
            $stmt->execute([$name]);
        }
    }

    if ($action === 'delete_category') {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([(int)($_POST['category_id'] ?? 0)]);
    }

    header('Location: index.php');
    exit;
}

$counts = [
    'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'designs' => (int)$pdo->query('SELECT COUNT(*) FROM designs')->fetchColumn(),
    'downloads' => (int)$pdo->query('SELECT COUNT(*) FROM downloads')->fetchColumn(),
    'pending' => (int)$pdo->query("SELECT COUNT(*) FROM designs WHERE status = 'pending'")->fetchColumn(),
];

$designs = $pdo->query(
    'SELECT d.*, u.username, c.name AS category_name
     FROM designs d JOIN users u ON u.id = d.user_id JOIN categories c ON c.id = d.category_id
     ORDER BY d.created_at DESC LIMIT 30'
)->fetchAll();
$users = $pdo->query('SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 30')->fetchAll();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'لوحة الأدمن';
include __DIR__ . '/../includes/header.php';
?>
<section class="container py-5">
    <h1 class="h3 mb-4">لوحة الأدمن</h1>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="stat-box"><strong><?= $counts['users'] ?></strong><br><span class="text-muted">مستخدم</span></div></div>
        <div class="col-md-3"><div class="stat-box"><strong><?= $counts['designs'] ?></strong><br><span class="text-muted">تصميم</span></div></div>
        <div class="col-md-3"><div class="stat-box"><strong><?= $counts['downloads'] ?></strong><br><span class="text-muted">تحميل</span></div></div>
        <div class="col-md-3"><div class="stat-box"><strong><?= $counts['pending'] ?></strong><br><span class="text-muted">بانتظار المراجعة</span></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="auth-panel">
                <h2 class="h5 mb-3">مراجعة الملفات</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>العنوان</th><th>المصمم</th><th>القسم</th><th>الحالة</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($designs as $design): ?>
                            <tr>
                                <td><a href="../design-details.php?id=<?= (int)$design['id'] ?>"><?= e($design['title']) ?></a></td>
                                <td><?= e($design['username']) ?></td>
                                <td><?= e($design['category_name']) ?></td>
                                <td>
                                    <form method="post" class="d-flex gap-2">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="design_id" value="<?= (int)$design['id'] ?>">
                                        <select class="form-select form-select-sm" name="status">
                                            <?php foreach (['pending' => 'مراجعة', 'approved' => 'مقبول', 'rejected' => 'مرفوض'] as $value => $label): ?>
                                                <option value="<?= e($value) ?>" <?= $design['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-sm btn-outline-primary">حفظ</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete_design">
                                        <input type="hidden" name="design_id" value="<?= (int)$design['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" data-confirm="حذف الملف؟">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="auth-panel mb-4">
                <h2 class="h5 mb-3">إدارة الأقسام</h2>
                <form method="post" class="d-flex gap-2 mb-3">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="add_category">
                    <input class="form-control" name="name" placeholder="قسم جديد">
                    <button class="btn btn-primary">إضافة</button>
                </form>
                <?php foreach ($categories as $category): ?>
                    <form method="post" class="d-flex justify-content-between align-items-center border-top py-2">
                        <span><?= e($category['name']) ?></span>
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" data-confirm="حذف القسم؟">حذف</button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="auth-panel mt-4">
        <h2 class="h5 mb-3">إدارة المستخدمين</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>الاسم</th><th>البريد</th><th>الدور</th><th>تاريخ التسجيل</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= e($user['username']) ?></td>
                        <td><?= e($user['email']) ?></td>
                        <td><?= e($user['role']) ?></td>
                        <td><?= e($user['created_at']) ?></td>
                        <td>
                            <?php if ($user['role'] !== 'admin'): ?>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" data-confirm="حذف المستخدم وكل تصاميمه؟">حذف</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
