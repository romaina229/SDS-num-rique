<?php
// includes/header.php
session_start();

// Déterminer le titre de la page
$page_title = isset($page_title) ? $page_title : 'Shalom Digital Solutions- Solutions Numériques';
$page_description = isset($page_description) ? $page_description : 'Votre partenaire en création de sites web, Excel avancé et collecte de données';
$current_page = isset($current_page) ? $current_page : basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Logo du site-->
    <link rel="icon" href="../assets/images/Faviconsds.png" type="image/png">

    <!-- Meta tags SEO -->
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="site web, création site, Excel avancé, KoboToolbox, ODK, SurveyCTO, développement web, Bénin, Abomey-Calavi">
    <meta name="author" content="Shalom Digital Solutions">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:image" content="https://shalomviepro.com/assets/images/og-image.jpg">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="twitter:image" content="https://shalomviepro.com/assets/images/twitter-image.jpg">
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="assets/images/favicon/site.webmanifest">
    <link rel="mask-icon" href="assets/images/favicon/safari-pinned-tab.svg" color="#3498db">
    <meta name="msapplication-TileColor" content="#2c3e50">
    <meta name="theme-color" content="#ffffff">
    
    <!-- Styles CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- CSS spécifique à la page -->
    <?php if (file_exists('assets/css/' . $current_page . '.css')): ?>
    <link rel="stylesheet" href="assets/css/<?php echo $current_page; ?>.css">
    <?php endif; ?>
    
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Scripts en tête -->
    <script>
        // Détection des fonctionnalités
        window.html5 = {
            inputs: (function() {
                var inputs = ['search', 'tel', 'url', 'email', 'date'];
                for (var i = 0; i < inputs.length; i++) {
                    var el = document.createElement('input');
                    el.setAttribute('type', inputs[i]);
                    if (el.type !== 'text') {
                        return false;
                    }
                }
                return true;
            })()
        };
        
        // Variables globales
        window.SITE_URL = '<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>';
        window.CSRF_TOKEN = '<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>';
    </script>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">
            <img src="../assets/images/Logosds.png" alt="logo site" style="width: 70px; height: 70px; margin-right: 10px; vertical-align: middle;">
                Shalom Digital <span>Solutions</span>
            </a>
            
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
            
            <nav>
                <ul>
                    <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Accueil</a></li>
                    <li><a href="index.php#apropos" class="<?php echo isset($_GET['section']) && $_GET['section'] == 'apropos' ? 'active' : ''; ?>">À propos</a></li>
                    <li><a href="index.php#services" class="<?php echo isset($_GET['section']) && $_GET['section'] == 'services' ? 'active' : ''; ?>">Services</a></li>
                    <li><a href="index.php#tarifs" class="<?php echo isset($_GET['section']) && $_GET['section'] == 'tarifs' ? 'active' : ''; ?>">Tarifs</a></li>
                    <li><a href="blog.php" class="<?php echo $current_page == 'blog.php' ? 'active' : ''; ?>">Blog</a></li>
                    <li><a href="commande.php" class="btn <?php echo $current_page == 'commande.php' ? 'active' : ''; ?>">Commander</a></li>
                    
                    <?php if(isset($_SESSION['admin']) && $_SESSION['admin']): ?>
                    <li><a href="admin/" class="btn" style="background-color: var(--success);">
                        <i class="fas fa-cog"></i> Admin
                    </a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Messages d'alerte -->
    <?php if(isset($_SESSION['message'])): ?>
    <div class="container">
        <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?>" id="session-message">
            <?php 
            echo htmlspecialchars($_SESSION['message']);
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
    </div>
    <?php endif; ?>