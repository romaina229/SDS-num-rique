<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer les articles du blog
$query = "SELECT * FROM blog_articles ORDER BY date_publication DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les catégories
$query_categories = "SELECT DISTINCT categorie FROM blog_articles WHERE categorie IS NOT NULL";
$stmt_cat = $db->prepare($query_categories);
$stmt_cat->execute();
$categories = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="assets/images/Faviconsds.png">
    <title>Blog - Shalom Digital Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        <?php include 'assets/css/style.css'; ?>
        <?php include 'assets/css/blog.css'; ?>
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">
                <div class="header-logo-container">
                    <img src="assets/images/Logosds.png" alt="Shalom Digital Solutions" class="site-logo">
                    <div class="site-title">
                        SD<span>Solutions</span>
                    </div>
                </div>
            </a>
            
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
            
            <nav>
                <ul>
                    <li><a href="index.php#accueil">Accueil</a></li>
                    <li><a href="index.php#services">Services</a></li>
                    <li><a href="index.php#tarifs">Tarifs</a></li>
                    <li><a href="blog.php" class="active">Blog</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                    <li><a href="commande.php" class="btn">Commander</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Section Hero -->
    <section class="blog-hero">
        <div class="blog-container">
            <div class="blog-header">
                <h1>Blog Shalom Digital Solutions</h1>
                <p>Découvrez nos conseils, tutoriels et actualités sur le développement web, la gestion de données et les outils numériques au Bénin</p>
            </div>
        </div>
    </section>

    <!-- Contenu principal -->
    <section class="blog-container">
        <div class="blog-content">
            <!-- Articles -->
            <main class="articles-section">
                <?php if(empty($articles)): ?>
                <div class="no-articles">
                    <i class="fas fa-newspaper"></i>
                    <h3>Aucun article pour le moment</h3>
                    <p>Revenez bientôt pour découvrir nos premiers articles !</p>
                </div>
                <?php else: ?>
                <div class="articles-list">
                    <?php foreach($articles as $article): ?>
                    <article class="article-card">
                        <?php if($article['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['titre']); ?>" class="article-image">
                        <?php endif; ?>
                        
                        <div class="article-content">
                            <div class="article-meta">
                                <?php if($article['categorie']): ?>
                                <span class="article-category"><?php echo htmlspecialchars($article['categorie']); ?></span>
                                <?php endif; ?>
                                <span class="article-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?php echo date('d/m/Y', strtotime($article['date_publication'])); ?>
                                </span>
                            </div>
                            
                            <h2><?php echo htmlspecialchars($article['titre']); ?></h2>
                            <p><?php echo nl2br(htmlspecialchars(substr($article['contenu'], 0, 200))) . '...'; ?></p>
                            
                            <a href="blog-article.php?id=<?php echo $article['id']; ?>" class="read-more">
                                Lire la suite
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <div class="pagination">
                    <a href="#" class="page-link active">1</a>
                    <a href="#" class="page-link">2</a>
                    <a href="#" class="page-link">3</a>
                    <a href="#" class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </main>
            
            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <div class="sidebar-widget">
                    <h3>Catégories</h3>
                    <ul class="categories-list">
                        <?php foreach($categories as $categorie): ?>
                        <li>
                            <a href="#">
                                <?php echo htmlspecialchars($categorie); ?>
                                <span class="count">3</span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="sidebar-widget">
                    <h3>Newsletter</h3>
                    <p>Abonnez-vous pour recevoir nos derniers articles</p>
                    <form class="subscribe-form">
                        <input type="email" placeholder="Votre email" required>
                        <button type="submit" class="btn">S'abonner</button>
                    </form>
                </div>
                
                <div class="sidebar-widget">
                    <h3>Derniers articles</h3>
                    <div class="recent-posts">
                        <?php for($i = 0; $i < min(3, count($articles)); $i++): ?>
                        <div class="recent-post">
                            <h4><?php echo htmlspecialchars($articles[$i]['titre']); ?></h4>
                            <span><?php echo date('d/m/Y', strtotime($articles[$i]['date_publication'])); ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <!-- Lien admin (visible seulement si connecté) -->
    <?php if(isset($_SESSION['admin'])): ?>
    <a href="admin/blog-admin.php" class="admin-link" title="Gérer les articles">
        <i class="fas fa-plus"></i>
    </a>
    <?php endif; ?>

    <!-- Footer -->
    <footer id="contact">
        <?php include 'includes/footer.php'; ?>
    </footer>

    <script>
        // Gestion de la newsletter
        document.querySelector('.subscribe-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            // Simuler l'envoi
            alert('Merci pour votre inscription à notre newsletter !');
            this.reset();
        });
        
        // Animation des cartes d'articles
        document.querySelectorAll('.article-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
                <?php include 'assets/js/script.js'; ?>
    </script>
</body>
</html>