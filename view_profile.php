<?php
require_once 'config.php';
checkLogin();

$db = Database::getInstance()->getConnection();
$user = null;
$error = '';

// URL'den kullanıcı ID'sini al
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Kullanıcı bilgilerini getir
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$profile_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = 'Kullanıcı bulunamadı.';
    }

    // Kullanıcının forum gönderilerini getir
    $stmt = $db->prepare("
        SELECT fp.*, COUNT(pl.id) as like_count, COUNT(fc.id) as comment_count
        FROM forum_posts fp
        LEFT JOIN post_likes pl ON fp.id = pl.post_id
        LEFT JOIN forum_comments fc ON fp.id = fc.post_id
        WHERE fp.user_id = ?
        GROUP BY fp.id
        ORDER BY fp.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$profile_id]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Kullanıcının oyun yorumlarını getir
    $stmt = $db->prepare("
        SELECT c.*, g.name as game_name
        FROM comments c
        JOIN games g ON c.game_id = g.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$profile_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = 'Profil bilgileri yüklenirken bir hata oluştu.';
}

$page_title = $user ? htmlspecialchars($user['username']) . ' - Profil' : 'Profil Bulunamadı';
include 'includes/header.php';
?>

<div class="container mt-4">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($user): ?>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'uploads/profiles/default.jpg'; ?>" 
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
                <!-- Son Forum Gönderileri -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Son Forum Gönderileri</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($posts)): ?>
                            <p class="text-muted">Henüz forum gönderisi bulunmuyor.</p>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <div class="mb-3">
                                    <h5>
                                        <a href="community.php?post=<?php echo $post['id']; ?>">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h5>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?> |
                                        <?php echo $post['like_count']; ?> beğeni |
                                        <?php echo $post['comment_count']; ?> yorum
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Son Oyun Yorumları -->
                <div class="card">
                    <div class="card-header">
                        <h4>Son Oyun Yorumları</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($comments)): ?>
                            <p class="text-muted">Henüz oyun yorumu bulunmuyor.</p>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="mb-3">
                                    <h5>
                                        <a href="game_details.php?id=<?php echo $comment['game_id']; ?>">
                                            <?php echo htmlspecialchars($comment['game_name']); ?>
                                        </a>
                                    </h5>
                                    <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                                    <div class="rating text-warning">
                                        <?php for($i = 0; $i < $comment['rating']; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 