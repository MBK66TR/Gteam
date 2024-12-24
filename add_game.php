<?php
require_once 'config.php';
checkLogin();
checkAdmin();

$error = '';
$success = '';

function checkUploadError($file) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Geçersiz dosya parametreleri.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('Dosya seçilmedi.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Dosya boyutu limiti aşıldı.');
        case UPLOAD_ERR_PARTIAL:
            throw new RuntimeException('Dosya tam yüklenemedi.');
        default:
            throw new RuntimeException('Bilinmeyen bir hata oluştu.');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $publisher = trim($_POST['publisher'] ?? '');
    $release_date = trim($_POST['release_date'] ?? '');
    
    // Resim yükleme işlemi
    $image_url = '';
    if (isset($_FILES['game_image']) && $_FILES['game_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['game_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $error = 'Sadece JPG, JPEG, PNG ve WEBP formatları kabul edilir.';
        } else {
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = uniqid() . '.' . $ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['game_image']['tmp_name'], $destination)) {
                $image_url = $destination;
            } else {
                $error = 'Resim yüklenirken bir hata oluştu.';
            }
        }
    }

    if (empty($name) || empty($genre) || empty($description) || $price <= 0 || empty($publisher)) {
        $error = 'Lütfen tüm alanları doldurunuz ve geçerli bir fiyat giriniz.';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO games (name, genre, description, price, image_url, publisher, release_date, added_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $genre, $description, $price, $image_url, $publisher, $release_date, $_SESSION['user_id']]);
            $success = 'Oyun başarıyla eklendi.';
            
            // Formu temizle
            $name = $genre = $description = $publisher = $release_date = '';
            $price = 0;
        } catch(PDOException $e) {
            $error = 'Hata Detayı: (' . $e->getCode() . ') ' . $e->getMessage();
            // Dosya yükleme hatası için ek kontrol
            if (!empty($image_url) && file_exists($image_url)) {
                unlink($image_url); // Hata durumunda yüklenen dosyayı sil
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oyun Ekle - GameStore</title>
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
                        <a class="nav-link active" href="add_game.php">Oyun Ekle</a>
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
                        <h3 class="mb-0">Yeni Oyun Ekle</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label">Oyun Adı</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="genre" class="form-label">Tür</label>
                                <select class="form-select" id="genre" name="genre" required>
                                    <option value="">Tür Seçin</option>
                                    <option value="Aksiyon">Aksiyon</option>
                                    <option value="Macera">Macera</option>
                                    <option value="RPG">RPG</option>
                                    <option value="Strateji">Strateji</option>
                                    <option value="Spor">Spor</option>
                                    <option value="Yarış">Yarış</option>
                                    <option value="Simülasyon">Simülasyon</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price" class="form-label">Fiyat (TL)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0"
                                       value="<?php echo isset($price) ? htmlspecialchars($price) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="publisher" class="form-label">Yayımcı</label>
                                <input type="text" class="form-control" id="publisher" name="publisher" 
                                       value="<?php echo isset($publisher) ? htmlspecialchars($publisher) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="release_date" class="form-label">Çıkış Tarihi</label>
                                <input type="date" class="form-control" id="release_date" name="release_date" 
                                       value="<?php echo isset($release_date) ? htmlspecialchars($release_date) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="game_image" class="form-label">Oyun Resmi</label>
                                <input type="file" class="form-control" id="game_image" name="game_image" accept="image/*" required>
                                <small class="text-muted">Desteklenen formatlar: JPG, JPEG, PNG, WEBP</small>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Oyunu Ekle</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 



// Genel PDO Hata Kodları
SQLSTATE[HY000]: Genel hata
SQLSTATE[23000]: Bütünlük kısıtlaması ihlali
SQLSTATE[42S02]: Tablo bulunamadı
SQLSTATE[42S22]: Sütun bulunamadı
SQLSTATE[42000]: Sözdizimi hatası
SQLSTATE[08004]: Sunucu bağlantı reddi

// Dosya Yükleme Hata Kodları
UPLOAD_ERR_OK (0): Dosya başarıyla yüklendi
UPLOAD_ERR_INI_SIZE (1): Dosya boyutu php.ini limitini aşıyor
UPLOAD_ERR_FORM_SIZE (2): Dosya boyutu HTML form limitini aşıyor
UPLOAD_ERR_PARTIAL (3): Dosya kısmen yüklendi
UPLOAD_ERR_NO_FILE (4): Dosya yüklenmedi
UPLOAD_ERR_NO_TMP_DIR (6): Geçici klasör eksik
UPLOAD_ERR_CANT_WRITE (7): Diske yazma hatası
UPLOAD_ERR_EXTENSION (8): PHP uzantısı dosya yüklemesini durdurdu
