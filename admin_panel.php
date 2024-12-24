<?php
require_once 'config.php';
checkLogin();
checkAdmin();

$db = Database::getInstance()->getConnection();
$error = '';
$success = '';

// Kullanıcı listesi
$users = [];
try {
    $stmt = $db->query("SELECT * FROM users ORDER BY username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = 'Kullanıcılar yüklenirken bir hata oluştu.';
}

// Oyun istatistikleri
$stats = [
    'total_users' => 0,
    'total_games' => 0,
    'total_admins' => 0,
    'total_value' => 0
];

try {
    // Toplam kullanıcı sayısı
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();

    // Toplam admin sayısı
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE is_admin = 1");
    $stats['total_admins'] = $stmt->fetchColumn();

    // Toplam oyun sayısı
    $stmt = $db->query("SELECT COUNT(*), SUM(price) FROM games");
    $result = $stmt->fetch(PDO::FETCH_NUM);
    $stats['total_games'] = $result[0];
    $stats['total_value'] = $result[1] ?? 0;
} catch(PDOException $e) {
    $error = 'İstatistikler yüklenirken bir hata oluştu.';
}

// Kullanıcı yetki değiştirme işlemi
if (isset($_POST['toggle_admin']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    
    try {
        // Mevcut admin durumunu kontrol et
        $stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $current_status = $stmt->fetchColumn();
        
        // Admin durumunu değiştir
        $stmt = $db->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        $stmt->execute([!$current_status, $user_id]);
        
        $success = 'Kullanıcı yetkileri güncellendi.';
        
        // Sayfayı yenile
        header('Location: admin_panel.php');
        exit();
    } catch(PDOException $e) {
        $error = 'Kullanıcı yetkileri güncellenirken bir hata oluştu.';
    }
}

// Kullanıcı silme işlemi
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Admin kendisini silemesin
    if ($user_id == $_SESSION['user_id']) {
        $error = 'Kendi hesabınızı silemezsiniz.';
    } else {
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            $success = 'Kullanıcı başarıyla silindi.';
            
            // Sayfayı yenile
            header('Location: admin_panel.php');
            exit();
        } catch(PDOException $e) {
            $error = 'Kullanıcı silinirken bir hata oluştu.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - GameVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">GameVault</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_game.php">Oyun Ekle</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_panel.php">Admin Paneli</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- İstatistikler -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Toplam Kullanıcı</h5>
                        <h2><?php echo $stats['total_users']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Toplam Admin</h5>
                        <h2><?php echo $stats['total_admins']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Toplam Oyun</h5>
                        <h2><?php echo $stats['total_games']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">Toplam Değer</h5>
                        <h2><?php echo number_format($stats['total_value'], 2); ?> TL</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kullanıcı Listesi -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Kullanıcı Yönetimi</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kullanıcı Adı</th>
                                <th>E-posta</th>
                                <th>Admin</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="badge bg-success">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Kullanıcı</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="toggle_admin" class="btn btn-warning btn-sm">
                                            <?php echo $user['is_admin'] ? 'Admin Yetkisini Al' : 'Admin Yap'; ?>
                                        </button>
                                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">
                                            Sil
                                        </button>
                                    </form>
                                    <?php else: ?>
                                        <span class="text-muted">Mevcut Kullanıcı</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 