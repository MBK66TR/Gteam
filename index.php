<?php
require_once 'config.php';
checkLogin();

$db = Database::getInstance()->getConnection();
$games = [];
$error = '';

try {
    $query = "SELECT * FROM games WHERE 1=1";
    $params = [];

    if (!empty($_GET['search'])) {
        $query .= " AND name LIKE ?";
        $params[] = "%" . $_GET['search'] . "%";
    }

    if (!empty($_GET['genre'])) {
        $query .= " AND genre = ?";
        $params[] = $_GET['genre'];
    }

    if (!empty($_GET['price_range'])) {
        list($min, $max) = explode('-', $_GET['price_range']);
        if ($max == '+') {
            $query .= " AND price >= ?";
            $params[] = $min;
        } else {
            $query .= " AND price BETWEEN ? AND ?";
            $params[] = $min;
            $params[] = $max;
        }
    }

    $query .= " ORDER BY name";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = 'Oyunlar yüklenirken bir hata oluştu.';
}

$page_title = 'Ana Sayfa';
$active_page = 'home';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Oyun adı ara..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="genre">
                        <option value="">Tüm Türler</option>
                        <?php
                        $genres = $db->query("SELECT DISTINCT genre FROM games ORDER BY genre")->fetchAll(PDO::FETCH_COLUMN);
                        foreach($genres as $genre):
                        ?>
                        <option value="<?php echo htmlspecialchars($genre); ?>" 
                                <?php echo (isset($_GET['genre']) && $_GET['genre'] == $genre) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($genre); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="price_range">
                        <option value="">Fiyat Aralığı</option>
                        <option value="0-50" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '0-50') ? 'selected' : ''; ?>>0-50 TL</option>
                        <option value="50-100" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '50-100') ? 'selected' : ''; ?>>50-100 TL</option>
                        <option value="100-200" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '100-200') ? 'selected' : ''; ?>>100-200 TL</option>
                        <option value="200+" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '200+') ? 'selected' : ''; ?>>200+ TL</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                </div>
            </form>
        </div>
    </div>

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