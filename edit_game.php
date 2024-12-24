<?php
require_once 'config.php';
checkLogin();
checkAdmin();

$db = Database::getInstance()->getConnection();
$error = '';
$success = '';
$game = null;

// Oyun ID'sini kontrol et
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$game_id = (int)$_GET['id'];

// Oyun bilgilerini getir
try {
    $stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->execute([$game_id]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$game) {
        header('Location: index.php');
        exit();
    }
} catch(PDOException $e) {
    $error = 'Oyun bilgileri yüklenirken bir hata oluştu.';
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $publisher = trim($_POST['publisher'] ?? '');
    $release_date = trim($_POST['release_date'] ?? '');

    if (empty($name) || empty($genre) || empty($description) || $price <= 0 || empty($publisher)) {
        $error = 'Lütfen tüm alanları doldurunuz ve geçerli bir fiyat giriniz.';
    } else {
        try {
            $stmt = $db->prepare("UPDATE games SET name = ?, genre = ?, description = ?, price = ?, publisher = ?, release_date = ? WHERE id = ?");
            $stmt->execute([$name, $genre, $description, $price, $publisher, $release_date, $game_id]);
            $success = 'Oyun başarıyla güncellendi.';
            
            // Güncel bilgileri yeniden yükle
            $stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
            $stmt->execute([$game_id]);
            $game = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $error = 'Oyun güncellenirken bir hata oluştu.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oyun Düzenle - GameStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">GameStore</a>
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
                        <a class="nav-link" href="admin_panel.php">Admin Paneli</a>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Oyun Düzenle</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>

                        <?php if ($game): ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Oyun Adı</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($game['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="genre" class="form-label">Tür</label>
                                <select class="form-select" id="genre" name="genre" required>
                                    <option value="">Tür Seçin</option>
                                    <?php
                                    $genres = ['Aksiyon', 'Macera', 'RPG', 'Strateji', 'Spor', 'Yarış', 'Simülasyon'];
                                    foreach ($genres as $g) {
                                        $selected = ($g == $game['genre']) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($g) . "\" $selected>" . htmlspecialchars($g) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($game['description']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price" class="form-label">Fiyat (TL)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0"
                                       value="<?php echo htmlspecialchars($game['price']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="publisher" class="form-label">Yayımcı</label>
                                <input type="text" class="form-control" id="publisher" name="publisher" 
                                       value="<?php echo htmlspecialchars($game['publisher']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="release_date" class="form-label">Çıkış Tarihi</label>
                                <input type="date" class="form-control" id="release_date" name="release_date" 
                                       value="<?php echo htmlspecialchars($game['release_date']); ?>">
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                                <a href="index.php" class="btn btn-secondary">İptal</a>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 