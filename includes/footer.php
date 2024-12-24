<?php
// Veritabanı bağlantısını al
$db = Database::getInstance()->getConnection();

// Footer istatistikleri
try {
    $total_games = $db->query("SELECT COUNT(*) FROM games")->fetchColumn();
    $total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
} catch(PDOException $e) {
    $total_games = 0;
    $total_users = 0;
}
?>

<footer class="footer mt-5 py-3 bg-dark text-light">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>GameVault Hakkında</h5>
                <p>GameVault, oyunseverlerin buluşma noktası. En yeni ve popüler oyunları keşfedin.</p>
            </div>
            <div class="col-md-4">
                <h5>Hızlı İstatistikler</h5>
                <ul class="list-unstyled">
                    <li>Toplam Oyun: <?php echo number_format($total_games); ?></li>
                    <li>Toplam Üye: <?php echo number_format($total_users); ?></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Bizi Takip Edin</h5>
                <div class="social-links">
                    <a href="#" class="text-light me-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light me-2"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-discord"></i></a>
                </div>
            </div>
        </div>
        <hr class="mt-3">
        <div class="row">
            <div class="col-12 text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> GameVault. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 