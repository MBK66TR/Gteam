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

// Beğeni ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['like_post_id'])) {
    $like_post_id = (int)$_POST['like_post_id'];
    
    try {
        $stmt = $db->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$like_post_id, $_SESSION['user_id']]);
        $success = 'Gönderi beğenildi.';
    } catch(PDOException $e) {
        $error = 'Beğeni eklenirken bir hata oluştu.';
    }
}

// Gönderileri getir
try {
    $stmt = $db->prepare("
        SELECT fp.*, u.username, 
        (SELECT COUNT(*) FROM forum_comments WHERE post_id = fp.id) as comment_count,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = fp.id) as like_count
        FROM forum_posts fp 
        JOIN users u ON fp.user_id = u.id 
        ORDER BY fp.created_at DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Gönderiler yüklenirken bir hata oluştu.';
}

// Yorum ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_content']) && isset($_POST['post_id'])) {
    $comment_content = trim($_POST['comment_content']);
    $post_id = (int)$_POST['post_id'];
    
    if (empty($comment_content)) {
        $error = 'Lütfen yorumunuzu giriniz.';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO forum_comments (post_id, user_id, comment) VALUES (?, ?, ?)");
            $stmt->execute([$post_id, $_SESSION['user_id'], $comment_content]);
            $success = 'Yorumunuz başarıyla eklendi.';
        } catch(PDOException $e) {
            $error = 'Yorum eklenirken bir hata oluştu.';
        }
    }
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
                            <button class="btn btn-sm btn-primary comment-toggle" data-post-id="<?php echo $post['id']; ?>">
                                Yorumlar (<?php echo $post['comment_count']; ?>)
                            </button>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary like-btn" data-post-id="<?php echo $post['id']; ?>">
                                <i class="fas fa-heart me-1"></i>
                                <span class="like-count"><?php echo $post['like_count']; ?></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Yorumlar -->
                <div class="comments" id="comments-<?php echo $post['id']; ?>" style="display: none;">
                    <h6>Yorumlar</h6>
                    <?php
                    $stmt = $db->prepare("SELECT c.*, u.username FROM forum_comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
                    $stmt->execute([$post['id']]);
                    $comments = $stmt->fetchAll();
                    foreach ($comments as $comment): ?>
                        <div class="comment mb-2">
                            <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                            <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Yorum Ekleme Formu -->
                    <form method="POST" action="">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <div class="mb-3">
                            <textarea class="form-control" name="comment_content" rows="2" placeholder="Yorumunuzu yazın..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-sm">Yorum Yap</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.comment-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const commentsDiv = document.getElementById('comments-' + postId);
            if (commentsDiv.style.display === 'none') {
                commentsDiv.style.display = 'block';
            } else {
                commentsDiv.style.display = 'none';
            }
        });
    });

    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const likeCountSpan = this.querySelector('.like-count');

            // AJAX isteği ile beğeni ekle
            fetch('community.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'like_post_id=' + postId
            })
            .then(response => response.text())
            .then(data => {
                // Beğeni sayısını güncelle
                let currentCount = parseInt(likeCountSpan.textContent);
                likeCountSpan.textContent = currentCount + 1;
            })
            .catch(error => console.error('Hata:', error));
        });
    });
</script>

<?php include 'includes/footer.php'; ?> 