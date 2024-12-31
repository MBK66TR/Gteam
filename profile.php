<?php
require_once 'config.php';
checkLogin();

$db = Database::getInstance()->getConnection();
$user = null;
$error = '';
$success = '';

try {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = 'Kullanıcı bilgileri yüklenirken bir hata oluştu.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $bio = trim($_POST['bio'] ?? '');
        $favorite_game = trim($_POST['favorite_game'] ?? '');
        $steam_profile = trim($_POST['steam_profile'] ?? '');
        
        try {
            // Resim yükleme işlemi
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $filename = $_FILES['profile_image']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) {
                    throw new Exception('Geçersiz dosya formatı. Sadece JPG, PNG ve WEBP formatları kabul edilir.');
                }
                
                if ($_FILES['profile_image']['size'] > 5000000) { // 5MB limit
                    throw new Exception('Dosya boyutu çok büyük. Maximum 5MB yükleyebilirsiniz.');
                }
                
                $new_filename = uniqid('profile_') . '.' . $ext;
                $upload_path = 'uploads/profiles/' . $new_filename;
                
                if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    throw new Exception('Dosya yüklenirken bir hata oluştu.');
                }
                
                // Eski profil resmini sil (default.jpg değilse)
                if ($user['profile_image'] != 'default.jpg' && file_exists($user['profile_image'])) {
                    unlink($user['profile_image']);
                }
                
                // Veritabanını güncelle
                $stmt = $db->prepare("UPDATE users SET profile_image = ?, bio = ?, favorite_game = ?, steam_profile = ? WHERE id = ?");
                $stmt->execute([$upload_path, $bio, $favorite_game, $steam_profile, $_SESSION['user_id']]);
            } else {
                // Sadece diğer bilgileri güncelle
                $stmt = $db->prepare("UPDATE users SET bio = ?, favorite_game = ?, steam_profile = ? WHERE id = ?");
                $stmt->execute([$bio, $favorite_game, $steam_profile, $_SESSION['user_id']]);
            }
            
            $success = 'Profil bilgileri başarıyla güncellendi.';
            
            // Güncel bilgileri yeniden yükle
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$page_title = 'Profil';
$active_page = 'profile';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <!-- Profil Kartı -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'default.jpg'); ?>" 
                         class="rounded-circle mb-3" 
                         style="width: 150px; height: 150px; object-fit: cover;">
                    <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                    <p class="text-muted">Üyelik: <?php echo date('d.m.Y', strtotime($user['join_date'])); ?></p>
                    
                    <?php if ($user['bio']): ?>
                        <div class="mb-3">
                            <h5>Hakkında</h5>
                            <p><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($user['favorite_game']): ?>
                        <div class="mb-3">
                            <h5>Favori Oyun</h5>
                            <p><?php echo htmlspecialchars($user['favorite_game']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($user['steam_profile']): ?>
                        <a href="<?php echo htmlspecialchars($user['steam_profile']); ?>" 
                           class="btn btn-primary" target="_blank">
                            <i class="fab fa-steam"></i> Steam Profili
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Profil Bilgilerini Düzenle</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profil Fotoğrafı</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            <small class="text-muted">PNG, JPG veya WEBP formatında bir resim seçin.</small>
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label">Hakkımda</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" 
                                    placeholder="Kendinizden bahsedin..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="favorite_game" class="form-label">Favori Oyun</label>
                            <input type="text" class="form-control" id="favorite_game" name="favorite_game"
                                   placeholder="En sevdiğiniz oyun..."
                                   value="<?php echo htmlspecialchars($user['favorite_game'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="steam_profile" class="form-label">Steam Profil Linki</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-steam"></i></span>
                                <input type="url" class="form-control" id="steam_profile" name="steam_profile"
                                       placeholder="https://steamcommunity.com/id/kullaniciadi"
                                       value="<?php echo htmlspecialchars($user['steam_profile'] ?? ''); ?>">
                            </div>
                            <small class="text-muted">Steam profil linkinizi ekleyin.</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Değişiklikleri Kaydet
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 