<?php
require_once 'config.php';
checkLogin();
checkAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$game_id = (int)$_GET['id'];

try {
    $db = Database::getInstance()->getConnection();
    
    // Önce oyunun var olduğunu kontrol et
    $stmt = $db->prepare("SELECT id FROM games WHERE id = ?");
    $stmt->execute([$game_id]);
    
    if ($stmt->fetch()) {
        // Oyunu sil
        $stmt = $db->prepare("DELETE FROM games WHERE id = ?");
        $stmt->execute([$game_id]);
    }
    
    header('Location: index.php');
    exit();
} catch(PDOException $e) {
    die('Oyun silinirken bir hata oluştu.');
}
?> 