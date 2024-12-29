<?php
require_once 'config.php';
checkLogin();

$db = Database::getInstance()->getConnection();
$error = '';
$success = '';

// Yeni gönderi ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_content'])) {
    $title = trim($_POST['post_title']);
    $content = trim($_POST['post_content']);
    
    if (empty($title) || empty($content)) {
        $error = 'Lütfen başlık ve içerik giriniz.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO forum_posts (user_id, title, content) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $content]);
            $success = 'Gönderiniz başarıyla paylaşıldı.';
        } catch(PDOException $e) {
            $error = 'Gönderi paylaşılırken bir hata oluştu.';
        }
    }
}

// Gönderileri getir
try {
    $stmt = $db->prepare("
        SELECT fp.*, u.username, 
        (SELECT COUNT(*) FROM forum_comments WHERE post_id = fp.id) as comment_count
        FROM forum_posts fp 
        JOIN users u ON fp.user_id = u.id 
        ORDER BY fp.created_at DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Gönderiler yüklenirken bir hata oluştu.';
}

$page_title = 'Topluluk';
$active_page = 'community';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Yeni Gönderi Formu -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Yeni Gönderi Oluştur</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="post_title" class="form-label">Başlık</label>
                            <input type="text" class="form-control" id="post_title" name="post_title" required>
                        </div>
                        <div class="mb-3">
                            <label for="post_content" class="form-label">İçerik</label>
                            <textarea class="form-control" id="post_content" name="post_content" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Paylaş</button>
                    </form>
                </div>
            </div>

            <!-- Gönderiler -->
            <?php foreach ($posts as $post): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-user me-2"></i>
                        <?php echo htmlspecialchars($post['username']); ?>
                    </div>
                    <small class="text-muted">
                        <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?>
                    </small>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="post_details.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-comments me-1"></i>
                                Yorumlar (<?php echo $post['comment_count']; ?>)
                            </a>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary like-btn" data-post-id="<?php echo $post['id']; ?>">
                                <i class="fas fa-heart me-1"></i>
                                <span class="like-count"><?php echo $post['likes']; ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 