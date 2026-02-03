<?php
// admin/includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- SIDEBAR DESKTOP -->
<aside class="desktop-sidebar" id="desktop-sidebar">
    <div class="desktop-sidebar-header">
        <p class="desktop-admin-welcome">
            <i class="fas fa-user-circle"></i>
            <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
        </p>
    </div>
    
    <nav class="desktop-sidebar-nav">
        <ul>
            <li>
                <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            
            <li class="desktop-nav-section">
                <span class="desktop-section-title">Gestion</span>
            </li>
            
            <li>
                <a href="commandes.php" class="<?php echo $current_page == 'commandes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Commandes</span>
                    <?php 
                    $pendingCount = $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente'")->fetchColumn();
                    if ($pendingCount > 0): ?>
                    <span class="desktop-badge"><?php echo $pendingCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li>
                <a href="factures.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'factures.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Factures</span>
                </a>
            </li>

            <li>
                <a href="blog_admin.php" class="<?php echo $current_page == 'blog_admin.php' ? 'active' : ''; ?>">
                    <i class="fas fa-blog"></i>
                    <span>Blog</span>

                </a>
            </li>

            <li>
                <a href="contacts-admin.php" class="<?php echo $current_page == 'contacts-admin.php' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Nouveau message</span>

                </a>
            </li>


            
            <li>
                <a href="services.php" class="<?php echo $current_page == 'services.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cogs"></i>
                    <span>Services</span>
                </a>
            </li>
            
            <li>
                <a href="clients.php" class="<?php echo $current_page == 'clients.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Clients</span>
                </a>
            </li>
            
            <li class="desktop-nav-section">
                <span class="desktop-section-title">Configuration</span>
            </li>
            
            <li>
                <a href="parametres.php" class="<?php echo $current_page == 'parametres.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </li>
            
            <li>
                <a href="utilisateurs.php" class="<?php echo $current_page == 'utilisateurs.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-shield"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>
            
            <li>
                <a href="logs.php" class="<?php echo $current_page == 'logs.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Journaux</span>
                </a>
            </li>
            
            <li class="desktop-nav-section">
                <span class="desktop-section-title">Outils</span>
            </li>
            
            <li>
                <a href="backup.php" class="<?php echo $current_page == 'backup.php' ? 'active' : ''; ?>">
                    <i class="fas fa-database"></i>
                    <span>Sauvegarde</span>
                </a>
            </li>
            
            <li>
                <a href="statistiques.php" class="<?php echo $current_page == 'statistiques.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistiques</span>
                </a>
            </li>
            
            <li>
                <a href="maintenance.php" class="<?php echo $current_page == 'maintenance.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tools"></i>
                    <span>Maintenance</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="desktop-sidebar-footer">
        <a href="../index.php" class="desktop-view-site" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>Voir le site</span>
        </a>
        
        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit" class="desktop-logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </button>
        </form>
    </div>
    
    <div class="desktop-sidebar-footer-info">
        <p>Version 1.0.2</p>
        <p>© <?php echo date('Y'); ?> Shalom DigitalPro</p>
    </div>
</aside>

<!-- MENU MOBILE -->
<div class="mobile-overlay" id="mobile-overlay"></div>

<aside class="mobile-menu" id="mobile-menu">
    <div class="mobile-menu-header">
        <div>
            <a href="../index.php" class="mobile-logo" target="_blank">
                <i class="fas fa-code"></i>
                Shalom Digital<span>Solutions</span>
            </a>
            <p class="mobile-admin-welcome">
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
            </p>
        </div>
        <button class="close-mobile-menu" id="close-mobile-menu">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <nav class="mobile-nav">
        <ul>
            <li>
                <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            
            <li class="mobile-nav-section">
                <span class="mobile-section-title">Gestion</span>
            </li>
            
            <li>
                <a href="commandes.php" class="<?php echo $current_page == 'commandes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Commandes</span>
                    <?php 
                    $pendingCount = $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente'")->fetchColumn();
                    if ($pendingCount > 0): ?>
                    <span class="mobile-badge"><?php echo $pendingCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li>
                <a href="factures.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'factures.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Factures</span>
                </a>
            </li>
            
            <li>
                <a href="services.php" class="<?php echo $current_page == 'services.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cogs"></i>
                    <span>Services</span>
                </a>
            </li>
            
            <li>
                <a href="clients.php" class="<?php echo $current_page == 'clients.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Clients</span>
                </a>
            </li>
            
            <li class="mobile-nav-section">
                <span class="mobile-section-title">Configuration</span>
            </li>
            
            <li>
                <a href="parametres.php" class="<?php echo $current_page == 'parametres.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </li>
            
            <li>
                <a href="utilisateurs.php" class="<?php echo $current_page == 'utilisateurs.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-shield"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>
            
            <li>
                <a href="logs.php" class="<?php echo $current_page == 'logs.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Journaux</span>
                </a>
            </li>
            
            <li class="mobile-nav-section">
                <span class="mobile-section-title">Outils</span>
            </li>
            
            <li>
                <a href="backup.php" class="<?php echo $current_page == 'backup.php' ? 'active' : ''; ?>">
                    <i class="fas fa-database"></i>
                    <span>Sauvegarde</span>
                </a>
            </li>
            
            <li>
                <a href="statistiques.php" class="<?php echo $current_page == 'statistiques.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistiques</span>
                </a>
            </li>
            
            <li>
                <a href="maintenance.php" class="<?php echo $current_page == 'maintenance.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tools"></i>
                    <span>Maintenance</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="mobile-menu-footer">
        <a href="../index.php" class="mobile-view-site" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>Voir le site</span>
        </a>
        
        <form action="logout.php" method="POST" class="mobile-logout-form">
            <button type="submit" class="mobile-logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </button>
        </form>
        
        <div class="mobile-menu-footer-info">
            <p>Version 1.0.2</p>
            <p>© <?php echo date('Y'); ?> Shalom DigitalPro</p>
        </div>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const closeMobileMenuBtn = document.getElementById('close-mobile-menu');
    const mobileOverlay = document.getElementById('mobile-overlay');
    
    // Ouvrir le menu mobile
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function() {
            mobileMenu.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Fermer le menu mobile
    function closeMobileMenu() {
        mobileMenu.classList.remove('open');
        document.body.style.overflow = '';
    }
    
    if (closeMobileMenuBtn) {
        closeMobileMenuBtn.addEventListener('click', closeMobileMenu);
    }
    
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMobileMenu);
    }
    
    // Fermer le menu en cliquant sur un lien
    const mobileLinks = document.querySelectorAll('.mobile-nav a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', closeMobileMenu);
    });
    
    // Fermer avec la touche Échap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenu.classList.contains('open')) {
            closeMobileMenu();
        }
    });
    
    // Gérer le redimensionnement
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && mobileMenu.classList.contains('open')) {
            closeMobileMenu();
        }
    });
});
</script>