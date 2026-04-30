<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$q = trim($_GET['q'] ?? '');
$categoryId = (int)($_GET['category'] ?? 0);

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$where = ["d.status = 'approved'"];
$params = [];
if ($q !== '') {
    $where[] = '(d.title LIKE ? OR d.description LIKE ? OR d.keywords LIKE ?)';
    $params[] = "%{$q}%";
    $params[] = "%{$q}%";
    $params[] = "%{$q}%";
}
if ($categoryId > 0) {
    $where[] = 'd.category_id = ?';
    $params[] = $categoryId;
}

// Get total count for pagination
$countSql = 'SELECT COUNT(*) FROM designs d WHERE ' . implode(' AND ', $where);
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$offset = ($page - 1) * $perPage;

$sql = 'SELECT d.*, c.name AS category_name, u.username
    FROM designs d
    JOIN categories c ON c.id = d.category_id
    JOIN users u ON u.id = d.user_id
    WHERE ' . implode(' AND ', $where) . '
    ORDER BY d.created_at DESC
    LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$latestDesigns = $stmt->fetchAll();

$popularDesigns = $pdo->query(
    "SELECT d.*, u.username FROM designs d JOIN users u ON u.id = d.user_id
     WHERE d.status = 'approved' ORDER BY d.downloads DESC, d.views DESC LIMIT 6"
)->fetchAll();

$topDesigners = $pdo->query(
    "SELECT u.username, u.profile_image, COUNT(d.id) AS total_designs, COALESCE(SUM(d.downloads), 0) AS total_downloads
     FROM users u LEFT JOIN designs d ON d.user_id = u.id AND d.status = 'approved'
     GROUP BY u.id ORDER BY total_downloads DESC, total_designs DESC LIMIT 5"
)->fetchAll();

$pageTitle = 'DesignHub Arabic - منصة التصاميم العربية';
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1>شارك تصاميمك وقوالبك مع المجتمع العربي.</h1>
                <p class="lead mt-3">منصة مجانية لرفع وتحميل الشعارات، البوسترات، قوالب السوشيال ميديا، العروض، وملفات التصميم الجاهزة.</p>
                <form class="search-box mt-4" method="get">
                    <div class="input-group input-group-lg">
                        <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="ابحث عن شعار، قالب Canva، PSD...">
                        <button class="btn btn-primary">بحث</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-5">
                <div class="row g-3">
                    <div class="col-6"><div class="stat-box"><strong><?= count($latestDesigns) ?></strong><br><span class="text-muted">تصميم حديث</span></div></div>
                    <div class="col-6"><div class="stat-box"><strong><?= count($categories) ?></strong><br><span class="text-muted">قسم</span></div></div>
                    <div class="col-12"><div class="stat-box"><strong>مجاني وقابل للتطوير</strong><br><span class="text-muted">مناسب للتشغيل المحلي عبر XAMPP والمناقشة الجامعية.</span></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container mt-4">
    <?php if ($total > $perPage): ?>
        <?php $last = (int)ceil($total / $perPage); ?>
        <nav aria-label="pagination">
            <ul class="pagination">
                <?php for ($p = 1; $p <= $last; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</section>

<section class="container">
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a class="category-pill" href="index.php">الكل</a>
        <?php foreach ($categories as $category): ?>
            <a class="category-pill" href="index.php?category=<?= (int)$category['id'] ?>"><?= e($category['name']) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 m-0">أحدث التصاميم</h2>
        <?php if (is_logged_in()): ?><a href="upload.php" class="btn btn-primary btn-sm">رفع تصميم</a><?php endif; ?>
    </div>
    <div class="row g-4">
        <?php foreach ($latestDesigns as $design): ?>
            <div class="col-md-6 col-lg-4">
                <article class="card design-card">
                    <img data-lazy="true" data-src="<?= e($design['preview_image']) ?>" loading="lazy" alt="<?= e($design['title']) ?>">
                    <div class="card-body">
                        <span class="badge text-bg-primary mb-2"><?= e($design['category_name']) ?></span>
                        <h3 class="h5"><?= e($design['title']) ?></h3>
                        <p class="text-muted small"><?= e(function_exists('mb_substr') ? mb_substr($design['description'], 0, 90) : substr($design['description'], 0, 180)) ?>...</p>
                        <div class="d-flex justify-content-between small text-muted mb-3">
                            <span><?= e($design['username']) ?></span>
                            <span><?= (int)$design['downloads'] ?> تحميل</span>
                        </div>
                        <a class="btn btn-outline-primary w-100" href="design-details.php?id=<?= (int)$design['id'] ?>">عرض التصميم</a>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
        <?php if (!$latestDesigns): ?>
            <div class="col-12"><div class="alert alert-info">لا توجد تصاميم مطابقة حاليًا.</div></div>
        <?php endif; ?>
    </div>
</section>

<section class="container mt-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <h2 class="h4 mb-3">الأكثر تحميلًا</h2>
            <div class="row g-3">
                <?php foreach ($popularDesigns as $design): ?>
                    <div class="col-md-6">
                        <a class="stat-box d-block text-decoration-none" href="design-details.php?id=<?= (int)$design['id'] ?>">
                            <strong><?= e($design['title']) ?></strong>
                            <div class="text-muted small"><?= (int)$design['downloads'] ?> تحميل - <?= (int)$design['views'] ?> مشاهدة</div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-lg-4">
            <h2 class="h4 mb-3">أفضل المصممين</h2>
            <?php foreach ($topDesigners as $designer): ?>
                <div class="stat-box mb-2">
                    <strong><?= e($designer['username']) ?></strong>
                    <div class="text-muted small"><?= (int)$designer['total_designs'] ?> تصميم - <?= (int)$designer['total_downloads'] ?> تحميل</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
