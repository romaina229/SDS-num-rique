<?php
// index.php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Définir le taux de TVA (18%)
$taux_tva = 0.05;

// Récupérer les services par catégorie
$categories = ['web', 'excel', 'survey', 'formation'];
$services_by_category = [];

foreach ($categories as $categorie) {
    $query = "SELECT * FROM services WHERE categorie = :categorie ORDER BY popular DESC, id ASC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':categorie', $categorie);
    $stmt->execute();
    $services_by_category[$categorie] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculer la AIB pour chaque service
    foreach ($services_by_category[$categorie] as &$service) {
        $service['prix_ttc_fcfa'] = $service['prix_fcfa'] * (1 + $taux_tva);
        $service['prix_ttc_euro'] = $service['prix_euro'] * (1 + $taux_tva);
    }
}

// Récupérer les services web pour la section tarifs
$query_web = "SELECT * FROM services WHERE categorie = 'web' ORDER BY popular DESC, id ASC";
$stmt_web = $db->prepare($query_web);
$stmt_web->execute();
$services_web = $stmt_web->fetchAll(PDO::FETCH_ASSOC);

// Calculer la AIB pour les services web
foreach ($services_web as &$service) {
    $service['prix_ttc_fcfa'] = $service['prix_fcfa'] * (1 + $taux_tva);
    $service['prix_ttc_euro'] = $service['prix_euro'] * (1 + $taux_tva);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Logo du site-->
    <link rel="icon" href="assets/images/Faviconsds.png" type="image/png">

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

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="assets/images/favicon/site.webmanifest">
    <link rel="mask-icon" href="assets/images/favicon/safari-pinned-tab.svg" color="#3498db">
    <meta name="msapplication-TileColor" content="#2c3e50">
    <meta name="theme-color" content="#ffffff">

    <title>Shalom Digital Solutions- Solutions Numériques Complètes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Tous les styles CSS précédents restent identiques */
        <?php include 'assets/css/style.css'; ?>
        
        /* Style pour l'indication AIB */
        .tva-indication {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }
        
        .price-details {
            margin-top: 10px;
            font-size: 0.9rem;
        }
        
        .price-ttc {
            color: var(--accent);
            font-weight: bold;
        }
        
        li {
            margin-left: 45px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">
            <img src="assets/images/Logosds.png" alt="logo site" style="width: 70px; height: 70px; margin-right: 10px; vertical-align: middle;">
                Shalom Digital <span>Solutions</span>
            </a>
            
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
            
            <nav>
                <ul>
                    <li><a href="#accueil">Accueil</a></li>
                    <li><a href="#apropos">À propos</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#tarifs">Tarifs Web</a></li>
                    <li><a href="commande.php" class="btn">Commander</a></li>
                    <?php if(isset($_SESSION['admin'])): ?>
                    <li><a href="admin/" class="btn" style="background-color: var(--success);">Admin</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Section Hero -->
    <section class="hero" id="accueil">
        <div class="container">
            <h1>Shalom Digital Solutions- Votre partenaire en solutions numériques innovantes</h1>
            <p>Nous accompagnons les organisations, entrepreneurs, ONG et projets dans la <b>création de sites web performants</b>, la <b>gestion et l’analyse de données</b>, ainsi que <b>la collecte de données digitales</b> grâce à des outils modernes.</p>
            <a href="#services" class="btn">Découvrir tous nos services</a>
        </div>
    </section>

    <!-- Section À propos -->
    <section id="apropos">
        <div class="container">
            <h2 class="section-title">À propos de Shalom Digital Solutions</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>Fondée en 2021, <strong>Shalom Digital Solutions</strong> est une entreprise spécialisée dans la conception et la mise en œuvre de solutions numériques adaptées aux besoins des professionnels, organisations et porteurs de projets. Elle intervient dans la création de sites web, la gestion et l’analyse de données, la mise en place de systèmes de collecte d’informations et l’automatisation des processus numériques, en privilégiant des outils fiables, accessibles et durables.</p>
                    <p>Notre expertise s’articule autour de <strong>trois domaines clés</strong> : 
                        <ul>
                            <li>
                                la<strong> création de sites web modernes et performants</strong>,
                            </li>
                            <li>
                                la <strong>gestion, l'analyse et la valorisation des données</strong> à l'aide d'outils numériques avancés,
                            </li>
                            <li>
                                la <strong>mise en place de systèmes fiable de collecte de données</strong> pour le suivi, l'évaluation et la prise de décision.
                            </li>
                        </ul>
                    </p>
                    <p>Nous sommes convaincus que chaque entreprise mérite des <strong>outils numériques performants, évolutifs et alignés sur ses objectifs spécifiques</strong>, afin d’améliorer son efficacité et sa visibilité.</p><br>
                    <a href="#services" class="btn">Voir nos services</a>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1551650975-87deedd944c3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" alt="Équipe Shalom Digital Solutions">
                </div>
            </div>
        </div>
    </section>

    <!-- Section Services COMPLETS -->
    <section id="services" class="services">
        <div class="container">
            <h2 class="section-title">Nos Services Complets</h2>
            <p style="text-align: center; margin-bottom: 30px; max-width: 800px; margin-left: auto; margin-right: auto;">
                Découvrez notre gamme complète de services numériques adaptés à tous vos besoins professionnels.
                <br><span class="tva-indication">Tous les prix incluent un AIB de <?php echo ($taux_tva * 100); ?>%</span>
            </p>
            
            <!-- Onglets de services -->
            <div class="services-tabs">
                <button class="tab-btn active" data-tab="web">Sites Web</button>
                <button class="tab-btn" data-tab="excel">Gestion et analyse de données</button>
                <button class="tab-btn" data-tab="survey">Collecte de Données</button>
                <button class="tab-btn" data-tab="formation">Formations</button>
            </div>
            
            <!-- Services Web -->
            <div class="services-category active" id="web-services">
                <h3 style="text-align: center; margin-bottom: 30px;">Services de Création de Sites Web</h3>
                <div class="services-grid">
                    <?php foreach ($services_by_category['web'] as $service): ?>
                    <div class="service-card web">
                        <div class="service-icon">
                            <i class="<?php echo htmlspecialchars($service['icone']); ?>"></i>
                        </div>
                        <?php if($service['popular']): ?>
                        <div class="popular-tag" style="position: absolute; top: 20px; right: 20px; background-color: var(--secondary); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">
                            Populaire
                        </div>
                        <?php endif; ?>
                        <div class="price-tag">
                            <?php echo number_format($service['prix_ttc_fcfa'], 0, ',', ' '); ?> FCFA TTC
                        </div>
                        <h3><?php echo htmlspecialchars($service['nom']); ?></h3>
                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                        <div class="price-details">
                            <span>HT : <?php echo number_format($service['prix_fcfa'], 0, ',', ' '); ?> FCFA</span><br>
                            <span>AIB (<?php echo ($taux_tva * 100); ?>%) : <?php echo number_format($service['prix_fcfa'] * $taux_tva, 0, ',', ' '); ?> FCFA</span>
                        </div>
                        <ul class="features-list">
                            <?php 
                            $caracteristiques = explode('|', $service['caracteristiques']);
                            foreach ($caracteristiques as $caract):
                            ?>
                            <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($caract); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div style="margin-top: 20px; font-weight: 600; color: var(--accent);">
                            Durée : <?php echo htmlspecialchars($service['duree']); ?>
                        </div>
                        <a href="commande.php?service=<?php echo $service['id']; ?>" class="btn" style="margin-top: 20px; display: block;">Commander</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Services Excel et Office -->
            <div class="services-category" id="excel-services">
                <h3 style="text-align: center; margin-bottom: 30px;">Services de Gestion de Données</h3>
                <div class="services-grid">
                    <?php foreach ($services_by_category['excel'] as $service): ?>
                    <div class="service-card excel">
                        <div class="service-icon">
                            <i class="<?php echo htmlspecialchars($service['icone']); ?>"></i>
                        </div>
                        <div class="price-tag">
                            <?php echo number_format($service['prix_ttc_fcfa'], 0, ',', ' '); ?> FCFA TTC
                        </div>
                        <h3><?php echo htmlspecialchars($service['nom']); ?></h3>
                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                        <div class="price-details">
                            <span>HT : <?php echo number_format($service['prix_fcfa'], 0, ',', ' '); ?> FCFA</span><br>
                            <span>AIB (<?php echo ($taux_tva * 100); ?>%) : <?php echo number_format($service['prix_fcfa'] * $taux_tva, 0, ',', ' '); ?> FCFA</span>
                        </div>
                        <ul class="features-list">
                            <?php 
                            $caracteristiques = explode('|', $service['caracteristiques']);
                            foreach ($caracteristiques as $caract):
                            ?>
                            <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($caract); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div style="margin-top: 20px; font-weight: 600; color: var(--accent);">
                            Durée : <?php echo htmlspecialchars($service['duree']); ?>
                        </div>
                        <a href="commande.php?service=<?php echo $service['id']; ?>" class="btn" style="margin-top: 20px; display: block;">Commander</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Services Collecte de Données -->
            <div class="services-category" id="survey-services">
                <h3 style="text-align: center; margin-bottom: 30px;">Services de Collecte de Données</h3>
                <div class="services-grid">
                    <?php foreach ($services_by_category['survey'] as $service): ?>
                    <div class="service-card survey">
                        <div class="service-icon">
                            <i class="<?php echo htmlspecialchars($service['icone']); ?>"></i>
                        </div>
                        <div class="price-tag">
                            <?php echo number_format($service['prix_ttc_fcfa'], 0, ',', ' '); ?> FCFA TTC
                        </div>
                        <h3><?php echo htmlspecialchars($service['nom']); ?></h3>
                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                        <div class="price-details">
                            <span>HT : <?php echo number_format($service['prix_fcfa'], 0, ',', ' '); ?> FCFA</span><br>
                            <span>AIB (<?php echo ($taux_tva * 100); ?>%) : <?php echo number_format($service['prix_fcfa'] * $taux_tva, 0, ',', ' '); ?> FCFA</span>
                        </div>
                        <ul class="features-list">
                            <?php 
                            $caracteristiques = explode('|', $service['caracteristiques']);
                            foreach ($caracteristiques as $caract):
                            ?>
                            <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($caract); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div style="margin-top: 20px; font-weight: 600; color: var(--accent);">
                            Durée : <?php echo htmlspecialchars($service['duree']); ?>
                        </div>
                        <a href="commande.php?service=<?php echo $service['id']; ?>" class="btn" style="margin-top: 20px; display: block;">Commander</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Services Formations -->
            <div class="services-category" id="formation-services">
                <h3 style="text-align: center; margin-bottom: 30px;">Formations et Accompagnement</h3>
                <div class="services-grid">
                    <?php foreach ($services_by_category['formation'] as $service): ?>
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="<?php echo htmlspecialchars($service['icone']); ?>"></i>
                        </div>
                        <?php if(strpos($service['prix_fcfa'], 'À partir') !== false): ?>
                        <div class="price-tag price-ttc">
                            <?php 
                            // Extraire le nombre du prix
                            preg_match('/\d+/', $service['prix_fcfa'], $matches);
                            $prix_ht = isset($matches[0]) ? (int)$matches[0] : 0;
                            $prix_ttc = $prix_ht * (1 + $taux_tva);
                            echo "À partir de " . number_format($prix_ttc, 0, ',', ' ') . " FCFA TTC";
                            ?>
                        </div>
                        <?php else: ?>
                        <div class="price-tag price-ttc">
                            <?php echo number_format($service['prix_ttc_fcfa'], 0, ',', ' '); ?> FCFA TTC
                        </div>
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($service['nom']); ?></h3>
                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                        <div class="price-details">
                            <span>HT : <?php echo number_format($service['prix_fcfa'], 0, ',', ' '); ?> FCFA</span><br>
                            <span>AIB (<?php echo ($taux_tva * 100); ?>%) : <?php echo number_format($service['prix_fcfa'] * $taux_tva, 0, ',', ' '); ?> FCFA</span>
                        </div>
                        <ul class="features-list">
                            <?php 
                            $caracteristiques = explode('|', $service['caracteristiques']);
                            foreach ($caracteristiques as $caract):
                            ?>
                            <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($caract); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div style="margin-top: 20px; font-weight: 600; color: var(--accent);">
                            Durée : <?php echo htmlspecialchars($service['duree']); ?>
                        </div>
                        <a href="commande.php?service=<?php echo $service['id']; ?>" class="btn" style="margin-top: 20px; display: block;">
                            <?php echo $service['categorie'] == 'formation' ? 'Réserver' : 'Commander'; ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Tarifs Web -->
    <section id="tarifs">
        <div class="container">
            <h2 class="section-title">Nos Tarifs Sites Web</h2>
            <p style="text-align: center; margin-bottom: 30px;">
                <span class="tva-indication">Tous les prix incluent un AIB de <?php echo ($taux_tva * 100); ?>%</span>
            </p>
            <div class="pricing-grid">
                <?php foreach ($services_web as $service): ?>
                <div class="pricing-card <?php echo $service['popular'] ? 'popular' : ''; ?>">
                    <?php if($service['popular']): ?>
                    <div class="popular-tag">Le plus populaire</div>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($service['nom']); ?></h3>
                    <div class="duration">Durée : <?php echo htmlspecialchars($service['duree']); ?></div>
                    <div class="price price-ttc">
                        <?php echo number_format($service['prix_ttc_fcfa'], 0, ',', ' '); ?> FCFA TTC
                        <span>/ <?php echo number_format($service['prix_ttc_euro'], 0, ',', ' '); ?> € TTC</span>
                    </div>
                    <div class="price-details" style="text-align: center; margin: 10px 0;">
                        <small>HT : <?php echo number_format($service['prix_fcfa'], 0, ',', ' '); ?> FCFA</small><br>
                        <small>AIB : <?php echo number_format($service['prix_fcfa'] * $taux_tva, 0, ',', ' '); ?> FCFA</small>
                    </div>
                    <ul class="features-list">
                        <?php 
                        $caracteristiques = explode('|', $service['caracteristiques']);
                        foreach ($caracteristiques as $caract):
                        ?>
                        <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($caract); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="commande.php?service=<?php echo $service['id']; ?>" class="btn <?php echo $service['popular'] ? 'btn-accent' : ''; ?>" style="margin-top: 20px;">
                        <?php echo strpos($service['nom'], 'sur mesure') !== false ? 'Demander un devis' : 'Commander'; ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 40px;">
                <p><strong>Note :</strong> Tous nos prix incluent la conception, le développement, les tests et la mise en ligne. L'hébergement et le nom de domaine sont inclus la première année.</p>
                <p>Nous proposons également des formules de maintenance mensuelle après la première année.</p>
                <p class="tva-indication"><strong>Information AIB :</strong> Tous les prix sont affichés TTC (Toutes Taxes Comprises) avec un AIB de <?php echo ($taux_tva * 100); ?>%.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <?php include 'includes/footer.php'; ?>
    </footer>

    <script>
        <?php include 'assets/js/script.js'; ?>
    </script>
</body>
</html>