<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$user['id'], 'username' => $user['username'], 'email' => $user['email'], 'role' => $user['role']];
        header('Location: dashboard.php');
        exit;
    }

    $error = 'بيانات الدخول غير صحيحة.';
}

$pageTitle = 'تسجيل الدخول';
include __DIR__ . '/includes/header.php';
?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="auth-panel">
                <h1 class="h3 mb-4">تسجيل الدخول</h1>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="mb-3"><label class="form-label">البريد</label><input class="form-control" type="email" name="email" required></div>
                    <div class="mb-3"><label class="form-label">كلمة المرور</label><input class="form-control" type="password" name="password" required></div>
                    <button class="btn btn-primary w-100">دخول</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
