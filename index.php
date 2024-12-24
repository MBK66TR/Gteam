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

$page_title = 'Ana Sayfa';
$active_page = 'home';
include 'includes/header.php';
?>

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
                    <div class="price-tag">
                        <?php echo number_format($game['price'], 2); ?> TL
                    </div>
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

<?php include 'includes/footer.php'; ?> 