<?php
require_once 'config.php';
checkLogin();
checkAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $publisher = $_POST['publisher'] ?? '';
    $release_date = $_POST['release_date'] ?? '';

    if (empty($name) || empty($genre) || empty($description) || empty($price) || empty($publisher) || empty($release_date)) {
        $error = 'Tüm alanları doldurunuz.';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO games (name, genre, description, price, publisher, release_date, added_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $genre, $description, $price, $publisher, $release_date, $_SESSION['user_id']]);
            $success = 'Oyun başarıyla eklendi.';
        } catch(PDOException $e) {
            $error = 'Oyun eklenirken bir hata oluştu.';
        }
    }
}

$page_title = 'Oyun Ekle';
$active_page = 'add_game';
include 'includes/header.php';
?>

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

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Oyun Adı</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="genre" class="form-label">Tür</label>
                            <input type="text" class="form-control" id="genre" name="genre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Fiyat</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>

                        <div class="mb-3">
                            <label for="publisher" class="form-label">Yayıncı</label>
                            <input type="text" class="form-control" id="publisher" name="publisher" required>
                        </div>

                        <div class="mb-3">
                            <label for="release_date" class="form-label">Çıkış Tarihi</label>
                            <input type="date" class="form-control" id="release_date" name="release_date" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Oyun Ekle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 



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
