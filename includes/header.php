<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>GameVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <?php if (isset($extra_css)) echo $extra_css; ?>
    <style>
        /* Dropdown menü stilleri */
        .dropdown-menu {
            background-color: #212529; /* Koyu arka plan */
            border: 1px solid #373b3e;
        }

        .dropdown-item {
            color: #fff; /* Beyaz yazı */
            padding: 0.5rem 1rem;
        }

        .dropdown-item:hover {
            background-color: #373b3e; /* Hover durumunda koyu gri */
            color: #fff;
        }

        .dropdown-item i {
            width: 20px; /* İkonlar için sabit genişlik */
            text-align: center;
            margin-right: 8px;
        }

        /* Aktif menü öğesi */
        .dropdown-item.active, 
        .dropdown-item:active {
            background-color: #0d6efd; /* Bootstrap primary rengi */
            color: #fff;
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
                        <a class="nav-link <?php echo $active_page == 'home' ? 'active' : ''; ?>" href="index.php">Ana Sayfa</a>
                    </li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page == 'add_game' ? 'active' : ''; ?>" href="add_game.php">Oyun Ekle</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page == 'admin' ? 'active' : ''; ?>" href="admin_panel.php">Admin Paneli</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo $active_page == 'community' ? 'active' : ''; ?>" 
                           href="#" id="communityDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-users me-1"></i>Topluluk
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="communityDropdown">
                            <li>
                                <a class="dropdown-item" href="community.php">
                                    <i class="fas fa-comments me-2"></i>Forum
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="profiles.php">
                                    <i class="fas fa-user-friends me-2"></i>Profiller
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page == 'profile' ? 'active' : ''; ?>" href="profile.php">
                            <i class="fas fa-user"></i> <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html> 