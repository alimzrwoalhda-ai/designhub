<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $errors[] = 'يرجى إدخال بيانات صحيحة، وكلمة مرور لا تقل عن 8 أحرف.';
    }

    $profileImage = null;
    if (!empty($_FILES['profile_image']['name'])) {
        $profileImage = upload_file($_FILES['profile_image'], 'uploads/profiles', ['jpg', 'jpeg', 'png', 'webp'], 2097152);
        if (!$profileImage) {
            $errors[] = 'صورة الملف الشخصي غير صالحة.';
        }
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, profile_image) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $profileImage]);
            $_SESSION['user'] = ['id' => (int)$pdo->lastInsertId(), 'username' => $username, 'email' => $email, 'role' => 'user'];
            header('Location: dashboard.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'البريد مستخدم مسبقًا.';
        }
    }
}

$pageTitle = 'إنشاء حساب';
include __DIR__ . '/includes/header.php';
?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="auth-panel">
                <h1 class="h3 mb-4">إنشاء حساب جديد</h1>
                <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="mb-3"><label class="form-label">الاسم</label><input class="form-control" name="username" required></div>
                    <div class="mb-3"><label class="form-label">البريد</label><input class="form-control" type="email" name="email" required></div>
                    <div class="mb-3"><label class="form-label">كلمة المرور</label><input class="form-control" type="password" name="password" minlength="8" required></div>
                    <div class="mb-3"><label class="form-label">صورة شخصية</label><input class="form-control" type="file" name="profile_image" accept="image/*"></div>
                    <button class="btn btn-primary w-100">تسجيل</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
