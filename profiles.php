<?php
require_once 'config.php';
checkLogin();

$db = Database::getInstance()->getConnection();
$error = '';
$success = '';
$users = [];

// Arama sorgusu
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    if (!empty($search)) {
        $stmt = $db->prepare("
            SELECT id, username, profile_image, bio, join_date 
            FROM users 
            WHERE username LIKE ? OR bio LIKE ?
            ORDER BY username ASC
        ");
        $searchParam = "%{$search}%";
        $stmt->execute([$searchParam, $searchParam]);
    } else {
        $stmt = $db->prepare("
            SELECT id, username, profile_image, bio, join_date 
            FROM users 
            ORDER BY username ASC
        ");
        $stmt->execute();
    }
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Kullanıcılar yüklenirken bir hata oluştu.';
}

$page_title = 'Profiller';
$active_page = 'profiles';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Kullanıcı Profilleri</h3>
                </div>
                <div class="card-body">
                    <!-- Arama Formu -->
                    <form method="GET" action="" class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Kullanıcı ara..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Ara
                            </button>
                        </div>
                    </form>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <!-- Kullanıcı Listesi -->
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        <?php foreach ($users as $user): ?>
                            <div class="col">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <img src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'uploads/profiles/default.jpg'; ?>" 
                                             class="rounded-circle mb-3" 
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </h5>
                                        <?php if (!empty($user['bio'])): ?>
                                            <p class="card-text text-muted small">
                                                <?php echo mb_substr(htmlspecialchars($user['bio']), 0, 100); ?>...
                                            </p>
                                        <?php endif; ?>
                                        <p class="card-text small text-muted">
                                            Katılım: <?php echo date('d.m.Y', strtotime($user['join_date'])); ?>
                                        </p>
                                        <a href="view_profile.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-user"></i> Profili Görüntüle
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>