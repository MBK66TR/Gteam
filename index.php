<?php
require_once 'config.php';
checkLogin();

$db = Database::getInstance()->getConnection();
$games = [];
$error = '';

try {
    $stmt = $db->query("SELECT * FROM games ORDER BY name");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = 'Oyunlar yüklenirken bir hata oluştu.';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameStore - Ana Sayfa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
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
                        <a class="nav-link active" href="index.php">Ana Sayfa</a>
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
        <h2 class="mb-4">Oyun Listesi</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($games as $game): ?>
            <div class="col">
                <div class="card h-100">
                    <?php if (!empty($game['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($game['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($game['name']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="game_details.php?id=<?php echo $game['id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($game['name']); ?>
                            </a>
                        </h5>
                        <p class="card-text">
                            <strong>Tür:</strong> <?php echo htmlspecialchars($game['genre']); ?><br>
                            <strong>Yayımcı:</strong> <?php echo htmlspecialchars($game['publisher']); ?><br>
                            <strong>Fiyat:</strong> <?php echo number_format($game['price'], 2); ?> TL
                        </p>
                        <a href="game_details.php?id=<?php echo $game['id']; ?>" class="btn btn-primary btn-sm">Detayları Gör</a>
                    </div>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <div class="card-footer">
                        <a href="edit_game.php?id=<?php echo $game['id']; ?>" class="btn btn-primary btn-sm">Düzenle</a>
                        <a href="delete_game.php?id=<?php echo $game['id']; ?>" class="btn btn-danger btn-sm" 
                           onclick="return confirm('Bu oyunu silmek istediğinizden emin misiniz?')">Sil</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 