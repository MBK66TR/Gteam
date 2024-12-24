<?php
require_once 'config.php';
checkLogin();

$db = Database::getInstance()->getConnection();
$error = '';
$game = null;

// Oyun ID'sini kontrol et
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$game_id = (int)$_GET['id'];

// Oyun bilgilerini getir
try {
    $stmt = $db->prepare("
        SELECT g.*, u.username as publisher_name 
        FROM games g 
        LEFT JOIN users u ON g.added_by = u.id 
        WHERE g.id = ?
    ");
    $stmt->execute([$game_id]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$game) {
        header('Location: index.php');
        exit();
    }
} catch(PDOException $e) {
    $error = 'Oyun bilgileri yüklenirken bir hata oluştu.';
}

// Yorum ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    $rating = (int)$_POST['rating'];
    
    if (empty($comment) || $rating < 1 || $rating > 5) {
        $error = 'Lütfen geçerli bir yorum ve değerlendirme puanı girin.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO comments (game_id, user_id, comment, rating) VALUES (?, ?, ?, ?)");
            $stmt->execute([$game_id, $_SESSION['user_id'], $comment, $rating]);
            $success = 'Yorumunuz başarıyla eklendi.';
        } catch(PDOException $e) {
            $error = 'Yorum eklenirken bir hata oluştu.';
        }
    }
}

// Yorumları getir
try {
    $stmt = $db->prepare("
        SELECT c.*, u.username 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.game_id = ? 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$game_id]);
    $comments = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Yorumlar yüklenirken bir hata oluştu.';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['name']); ?> - GameVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .game-image {
            max-height: 400px;
            object-fit: cover;
            width: 100%;
        }
    </style>
</head>
<body>
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
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="add_game.php">Oyun Ekle</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_panel.php">Admin Paneli</a>
                    </li>
                    <?php endif; ?>
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

        <?php if ($game): ?>
        <div class="row">
            <div class="col-md-8">
                <?php if (!empty($game['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($game['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($game['name']); ?>" 
                     class="game-image mb-4">
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title"><?php echo htmlspecialchars($game['name']); ?></h1>
                        <p class="text-muted">
                            <strong>Tür:</strong> <?php echo htmlspecialchars($game['genre']); ?>
                        </p>
                        <p class="text-muted">
                            <strong>Yayımcı:</strong> <?php echo htmlspecialchars($game['publisher']); ?>
                        </p>
                        <?php if ($game['release_date']): ?>
                        <p class="text-muted">
                            <strong>Çıkış Tarihi:</strong> 
                            <?php echo date('d.m.Y', strtotime($game['release_date'])); ?>
                        </p>
                        <?php endif; ?>
                        <p class="text-muted">
                            <strong>Ekleyen:</strong> 
                            <?php echo htmlspecialchars($game['publisher_name']); ?>
                        </p>
                        <h3 class="text-primary mb-4"><?php echo number_format($game['price'], 2); ?> TL</h3>
                        
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <div class="d-grid gap-2">
                            <a href="edit_game.php?id=<?php echo $game['id']; ?>" 
                               class="btn btn-primary">Düzenle</a>
                            <a href="delete_game.php?id=<?php echo $game['id']; ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Bu oyunu silmek istediğinizden emin misiniz?')">Sil</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Oyun Açıklaması</h3>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($game['description'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h3 class="text-primary mb-4"><?php echo number_format($game['price'], 2); ?> TL</h3>
                <div class="d-grid gap-2">
                    <a href="https://store.steampowered.com/search/?term=<?php echo urlencode($game['name']); ?>" 
                       target="_blank" class="btn btn-success btn-lg">
                        <i class="fas fa-shopping-cart me-2"></i>Satın Al
                    </a>
                </div>
            </div>
        </div>

        <!-- Yorum Formu -->
        <div class="card mt-4">
            <div class="card-header">
                <h3>Yorum Yap</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="rating" class="form-label">Puanınız</label>
                        <select class="form-select" id="rating" name="rating" required>
                            <option value="">Puan Seçin</option>
                            <option value="5">5 - Mükemmel</option>
                            <option value="4">4 - Çok İyi</option>
                            <option value="3">3 - İyi</option>
                            <option value="2">2 - Orta</option>
                            <option value="1">1 - Kötü</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="comment" class="form-label">Yorumunuz</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Yorum Yap</button>
                </form>
            </div>
        </div>

        <!-- Yorumlar Listesi -->
        <div class="card mt-4">
            <div class="card-header">
                <h3>Yorumlar</h3>
            </div>
            <div class="card-body">
                <?php foreach ($comments as $comment): ?>
                <div class="comment mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0"><?php echo htmlspecialchars($comment['username']); ?></h5>
                        <div class="rating text-warning">
                            <?php for($i = 0; $i < $comment['rating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                    <small class="text-muted">
                        <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                    </small>
                    <hr>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 