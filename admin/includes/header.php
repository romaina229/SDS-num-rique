<?php
// admin/includes/head.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Admin Shalom Digital Solutions'); ?></title>

    <!-- logo du site -->
    
    <link rel="icon" href="../assets/images/Faviconsds.png" type="image/png">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon/favicon-16x16.png">
    
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="includes/sidebar.css">
    <!-- <link rel="stylesheet" href="includes/index.css">-->
    <!--<link rel="stylesheet" href="includes/header.css">-->
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    
    <?php if (isset($page_css)): ?>
    <link rel="stylesheet" href="<?php echo $page_css; ?>">
    <?php endif; ?>
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="../assets/js/admin-toggle.js"></script>
</head>
<body>
    <!-- HEADER -->
    <header class="main-header" id="main-header">
        <div class="header-left">
            <!-- Bouton hamburger pour mobile -->
            <button class="hamburger-btn" id="hamburger-btn">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Logo et nom du site -->
            <a href="index.php" class="header-logo">
                <img src="../assets/images/Logosds.png" alt="logo site" style="width: 50px; height: 50px; margin-right: 10px; vertical-align: middle;">
                <div class="header-logo-text">
                    <span class="site-name">Shalom Digital Solutions</span>
                    <span class="site-tagline">Administration</span>
                </div>
            </a>
        </div>
        
        <div class="header-right">
            <!-- Date actuelle -->
            <div class="header-date">
                <i class="far fa-calendar-alt"></i>
                <span id="current-date"><?php echo date('l d F Y'); ?></span>
            </div>
            
            <!-- Informations utilisateur -->
            <div class="user-info">
                <div class="user-avatar">
                    <?php 
                    $username = $_SESSION['admin_username'] ?? 'Admin';
                    echo strtoupper(substr($username, 0, 2));
                    ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                    <span class="user-role">Administrateur</span>
                </div>
            </div>
            
            <!-- Bouton de déconnexion -->
            <form action="logout.php" method="POST">
                <button type="submit" class="logout-btn-header">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </button>
            </form>
        </div>
    </header>