<?php
session_start();

// Vérifier si l'utilisateur est admin
if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../login.php');
    exit();
}

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Initialiser les variables
$article = null;
$message = '';
$error = '';
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupérer l'article à modifier
if($article_id > 0) {
    $query = "SELECT * FROM blog_articles WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $article_id);
    $stmt->execute();
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$article) {
        $error = "Article non trouvé.";
    }
} else {
    $error = "ID d'article non spécifié.";
}

// Traitement de la modification
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $titre = htmlspecialchars($_POST['titre']);
    $contenu = $_POST['contenu'];
    $categorie = htmlspecialchars($_POST['categorie']);
    $image_url = htmlspecialchars($_POST['image_url']);
    $auteur = htmlspecialchars($_POST['auteur']);
    $mots_cles = htmlspecialchars($_POST['mots_cles']);
    $statut = htmlspecialchars($_POST['statut']);
    
    if(empty($titre) || empty($contenu)) {
        $error = "Le titre et le contenu sont obligatoires.";
    } else {
        $query = "UPDATE blog_articles SET 
                  titre = :titre, 
                  contenu = :contenu, 
                  categorie = :categorie, 
                  image_url = :image_url, 
                  auteur = :auteur, 
                  mots_cles = :mots_cles,
                  statut = :statut,
                  date_modification = NOW()
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':contenu', $contenu);
        $stmt->bindParam(':categorie', $categorie);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':auteur', $auteur);
        $stmt->bindParam(':mots_cles', $mots_cles);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':id', $article_id);
        
        if($stmt->execute()) {
            $message = "Article modifié avec succès !";
            
            // Recharger l'article mis à jour
            $query = "SELECT * FROM blog_articles WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $article_id);
            $stmt->execute();
            $article = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Erreur lors de la modification de l'article.";
        }
    }
}

// Traitement de la suppression
if(isset($_GET['delete_image']) && $article_id > 0) {
    $query = "UPDATE blog_articles SET image_url = NULL WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $article_id);
    
    if($stmt->execute()) {
        $message = "Image supprimée avec succès.";
        $article['image_url'] = null;
    } else {
        $error = "Erreur lors de la suppression de l'image.";
    }
}

// Vérifier si l'article existe
if(!$article && $article_id > 0) {
    $error = "Impossible de charger l'article. Vérifiez l'ID.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Modifier un article de blog sur Shalom DigitalPro">
    <link rel="icon" href="assets/images/Faviconsds.png" type="image/png">
    <title>Modifier l'article - Admin Shalom DigitalPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        <?php include 'assets/css/style.css'; ?>
        
        /* Styles spécifiques admin */
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        
        .admin-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        .admin-nav {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .admin-nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }
        
        .admin-nav a {
            color: var(--text-light);
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: var(--accent);
            color: white;
        }
        
        .admin-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }
        
        @media (max-width: 1024px) {
            .admin-content {
                grid-template-columns: 1fr;
            }
        }
        
        /* Formulaire principal */
        .admin-form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--accent-light);
        }
        
        .form-header h2 {
            color: var(--primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-status {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .status-published {
            background: var(--success-light);
            color: var(--success);
        }
        
        .status-draft {
            background: #fff3cd;
            color: #856404;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--accent);
            outline: none;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        /* Éditeur WYSIWYG */
        #editor {
            height: 400px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .ql-toolbar {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            border-color: #ddd !important;
        }
        
        .ql-container {
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            border-color: #ddd !important;
            font-family: inherit;
        }
        
        /* Prévisualisation image */
        .image-preview-container {
            margin-top: 20px;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            margin-top: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .image-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .image-actions .btn-small {
            padding: 8px 15px;
            font-size: 0.9rem;
        }
        
        /* Sidebar */
        .admin-sidebar {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .sidebar-widget {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .sidebar-widget h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-light);
            color: var(--text-dark);
        }
        
        .article-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .info-value {
            color: var(--text-light);
        }
        
        /* Actions de l'article */
        .article-actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .action-btn i {
            font-size: 1.1rem;
        }
        
        .btn-view {
            background: var(--primary);
            color: white;
        }
        
        .btn-view:hover {
            background: var(--primary-dark);
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .btn-save {
            background: var(--success);
            color: white;
            margin-top: 10px;
        }
        
        .btn-save:hover {
            background: var(--success-dark);
        }
        
        /* Messages */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 20px;
            }
            
            .admin-nav ul {
                flex-direction: column;
            }
            
            .form-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
        
        /* Chargement */
        .loading {
            text-align: center;
            padding: 50px;
            color: var(--text-light);
        }
        
        .loading i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--accent);
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Tags input */
        .tags-input {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            min-height: 46px;
        }
        
        .tag {
            background: var(--accent-light);
            color: var(--accent);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .tag-remove {
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .tags-input input {
            border: none;
            outline: none;
            flex: 1;
            min-width: 100px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <!-- Header simplifié -->
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
            
            <nav>
                <ul>
                    <li><a href="../index.php">Site public</a></li>
                    <li><a href="index.php" class="btn">Dashboard</a></li>
                    <li><a href="../logout.php" class="btn btn-delete">Déconnexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Contenu principal -->
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-edit"></i> Modifier l'article</h1>
            <p>Modifiez et mettez à jour votre contenu</p>
        </div>
        
        <!-- Navigation admin -->
        <nav class="admin-nav">
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="blog-admin.php"><i class="fas fa-newspaper"></i> Tous les articles</a></li>
                <li><a href="blog-admin.php" class="active"><i class="fas fa-edit"></i> Éditer</a></li>
                <li><a href="blog-admin.php"><i class="fas fa-plus"></i> Nouvel article</a></li>
            </ul>
        </nav>
        
        <!-- Messages -->
        <?php if($message): ?>
        <div class="message success">
            <i class="fas fa-check-circle"></i>
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="message error">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if(!$article && $article_id > 0): ?>
        <div class="loading">
            <i class="fas fa-spinner"></i>
            <h3>Chargement de l'article...</h3>
        </div>
        <?php elseif($article): ?>
        
        <div class="admin-content">
            <!-- Formulaire principal -->
            <main>
                <div class="admin-form-container">
                    <div class="form-header">
                        <h2><i class="fas fa-edit"></i> Édition de l'article</h2>
                        <div class="form-status">
                            <span class="status-badge <?php echo ($article['statut'] ?? 'draft') == 'published' ? 'status-published' : 'status-draft'; ?>">
                                <?php echo ($article['statut'] ?? 'draft') == 'published' ? 'Publié' : 'Brouillon'; ?>
                            </span>
                            <span class="info-value">
                                ID: #<?php echo $article['id']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <form method="POST" action="" id="article-form">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                        
                        <!-- Titre et catégorie -->
                        <div class="form-row">
                            <div class="form-group" style="flex: 2;">
                                <label for="titre">Titre de l'article *</label>
                                <input type="text" id="titre" name="titre" class="form-control" required 
                                       value="<?php echo htmlspecialchars($article['titre']); ?>">
                            </div>
                            
                            <div class="form-group" style="flex: 1;">
                                <label for="categorie">Catégorie</label>
                                <select id="categorie" name="categorie" class="form-control">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="Développement Web" <?php echo ($article['categorie'] == 'Développement Web') ? 'selected' : ''; ?>>Développement Web</option>
                                    <option value="Excel & Data" <?php echo ($article['categorie'] == 'Excel & Data') ? 'selected' : ''; ?>>Excel & Data</option>
                                    <option value="Collecte de données" <?php echo ($article['categorie'] == 'Collecte de données') ? 'selected' : ''; ?>>Collecte de données</option>
                                    <option value="Formation" <?php echo ($article['categorie'] == 'Formation') ? 'selected' : ''; ?>>Formation</option>
                                    <option value="Actualités" <?php echo ($article['categorie'] == 'Actualités') ? 'selected' : ''; ?>>Actualités</option>
                                    <option value="Conseils" <?php echo ($article['categorie'] == 'Conseils') ? 'selected' : ''; ?>>Conseils</option>
                                    <option value="Tutoriels" <?php echo ($article['categorie'] == 'Tutoriels') ? 'selected' : ''; ?>>Tutoriels</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Auteur et statut -->
                        <div class="form-row">
                            <div class="form-group" style="flex: 1;">
                                <label for="auteur">Auteur</label>
                                <input type="text" id="auteur" name="auteur" class="form-control" 
                                       value="<?php echo htmlspecialchars($article['auteur'] ?: 'Équipe Shalom DigitalPro'); ?>">
                            </div>
                            
                            <div class="form-group" style="flex: 1;">
                                <label for="statut">Statut</label>
                                <select id="statut" name="statut" class="form-control">
                                    <option value="draft" <?php echo ($article['statut'] ?? 'draft') == 'draft' ? 'selected' : ''; ?>>Brouillon</option>
                                    <option value="published" <?php echo ($article['statut'] ?? 'draft') == 'published' ? 'selected' : ''; ?>>Publié</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Mots-clés -->
                        <div class="form-group">
                            <label for="mots_cles">Mots-clés (séparés par des virgules)</label>
                            <div class="tags-input" id="tags-container">
                                <!-- Les tags seront ajoutés ici par JavaScript -->
                                <input type="text" id="tag-input" placeholder="Ajouter un mot-clé">
                            </div>
                            <input type="hidden" id="mots_cles" name="mots_cles" value="<?php echo htmlspecialchars($article['mots_cles']); ?>">
                            <small style="color: var(--text-light); margin-top: 5px; display: block;">
                                Appuyez sur Entrée ou Tapez pour ajouter un mot-clé
                            </small>
                        </div>
                        
                        <!-- Image d'illustration -->
                        <div class="form-group">
                            <label for="image_url">URL de l'image d'illustration</label>
                            <input type="text" id="image_url" name="image_url" class="form-control" 
                                   value="<?php echo htmlspecialchars($article['image_url']); ?>" 
                                   placeholder="https://example.com/image.jpg">
                            
                            <!-- Prévisualisation de l'image -->
                            <?php if($article['image_url']): ?>
                            <div class="image-preview-container">
                                <p><strong>Image actuelle :</strong></p>
                                <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                                     alt="Prévisualisation" 
                                     class="image-preview"
                                     onerror="this.style.display='none'">
                                <div class="image-actions">
                                    <a href="?id=<?php echo $article['id']; ?>&delete_image=1" 
                                       class="btn btn-small btn-delete"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette image ?')">
                                        <i class="fas fa-trash"></i> Supprimer l'image
                                    </a>
                                    <button type="button" class="btn btn-small" onclick="refreshPreview()">
                                        <i class="fas fa-sync"></i> Actualiser
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <small style="color: var(--text-light); margin-top: 5px; display: block;">
                                URLs recommandées : Unsplash, Pexels ou votre propre hébergement
                            </small>
                        </div>
                        
                        <!-- Éditeur de contenu -->
                        <div class="form-group">
                            <label for="contenu">Contenu de l'article *</label>
                            <div id="editor"><?php echo $article['contenu']; ?></div>
                            <textarea id="contenu" name="contenu" style="display: none;"></textarea>
                            <small style="color: var(--text-light); margin-top: 5px; display: block;">
                                Utilisez les outils ci-dessus pour formater votre texte
                            </small>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="form-navigation" style="display: flex; justify-content: space-between; align-items: center; margin-top: 40px;">
                            <div>
                                <a href="blog-admin.php" class="btn">
                                    <i class="fas fa-arrow-left"></i> Retour à la liste
                                </a>
                            </div>
                            
                            <div style="display: flex; gap: 15px;">
                                <button type="submit" name="save_draft" value="1" class="btn">
                                    <i class="fas fa-save"></i> Enregistrer le brouillon
                                </button>
                                <button type="submit" name="publish" value="1" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Publier l'article
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
            
            <!-- Sidebar -->
            <aside class="admin-sidebar">
                <!-- Informations de l'article -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-info-circle"></i> Informations</h3>
                    <div class="article-info">
                        <div class="info-item">
                            <span class="info-label">ID :</span>
                            <span class="info-value">#<?php echo $article['id']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date de création :</span>
                            <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($article['date_publication'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Dernière modification :</span>
                            <span class="info-value">
                                <?php echo $article['date_modification'] ? date('d/m/Y H:i', strtotime($article['date_modification'])) : 'Jamais'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Auteur :</span>
                            <span class="info-value"><?php echo htmlspecialchars($article['auteur'] ?: 'Non spécifié'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Catégorie :</span>
                            <span class="info-value"><?php echo htmlspecialchars($article['categorie'] ?: 'Non catégorisé'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Statut :</span>
                            <span class="info-value status-badge <?php echo ($article['statut'] ?? 'draft') == 'published' ? 'status-published' : 'status-draft'; ?>">
                                <?php echo ($article['statut'] ?? 'draft') == 'published' ? 'Publié' : 'Brouillon'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-bolt"></i> Actions rapides</h3>
                    <div class="article-actions">
                        <a href="../blog-article.php?id=<?php echo $article['id']; ?>" 
                           target="_blank" 
                           class="action-btn btn-view">
                            <i class="fas fa-eye"></i>
                            Voir l'article
                        </a>
                        
                        <a href="blog-admin.php?delete=<?php echo $article['id']; ?>" 
                           class="action-btn btn-delete"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ? Cette action est irréversible.')">
                            <i class="fas fa-trash"></i>
                            Supprimer l'article
                        </a>
                        
                        <button type="button" class="action-btn btn-save" onclick="submitForm()">
                            <i class="fas fa-save"></i>
                            Enregistrer les modifications
                        </button>
                    </div>
                </div>
                
                <!-- Prévisualisation -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-search"></i> Aperçu</h3>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <p><strong>Titre :</strong> <span id="preview-title"><?php echo htmlspecialchars(substr($article['titre'], 0, 50)); ?>...</span></p>
                        <p><strong>Extrait :</strong> <span id="preview-excerpt"><?php echo htmlspecialchars(substr(strip_tags($article['contenu']), 0, 100)); ?>...</span></p>
                        <p><strong>URL :</strong> <small>blog-article.php?id=<?php echo $article['id']; ?></small></p>
                    </div>
                </div>
                
                <!-- Statistiques -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-chart-bar"></i> Statistiques</h3>
                    <div style="text-align: center; padding: 20px;">
                        <div style="font-size: 3rem; color: var(--accent); margin-bottom: 10px;">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h4 style="margin: 0; color: var(--text-dark);">0 vues</h4>
                        <p style="color: var(--text-light); margin: 5px 0 0 0; font-size: 0.9rem;">
                            Aucune donnée disponible
                        </p>
                    </div>
                </div>
            </aside>
        </div>
        
        <?php else: ?>
        
        <div class="admin-form-container" style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: var(--danger); margin-bottom: 20px;"></i>
            <h3>Article non trouvé</h3>
            <p>L'article que vous essayez de modifier n'existe pas ou a été supprimé.</p>
            <a href="blog-admin.php" class="btn" style="margin-top: 20px;">
                <i class="fas fa-arrow-left"></i> Retour à la liste des articles
            </a>
        </div>
        
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        // Initialiser l'éditeur WYSIWYG
        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'direction': 'rtl' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['link', 'image', 'video', 'formula'],
                    ['clean']
                ]
            }
        });
        
        // Initialiser les tags
        function initTags() {
            const tagsContainer = document.getElementById('tags-container');
            const hiddenInput = document.getElementById('mots_cles');
            const tagInput = document.getElementById('tag-input');
            
            // Charger les tags existants
            let tags = hiddenInput.value ? hiddenInput.value.split(',').map(tag => tag.trim()).filter(tag => tag) : [];
            
            function updateTags() {
                // Vider le conteneur
                tagsContainer.innerHTML = '';
                
                // Ajouter les tags
                tags.forEach((tag, index) => {
                    const tagElement = document.createElement('div');
                    tagElement.className = 'tag';
                    tagElement.innerHTML = `
                        ${tag}
                        <span class="tag-remove" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </span>
                    `;
                    tagsContainer.appendChild(tagElement);
                });
                
                // Ajouter l'input
                const input = document.createElement('input');
                input.type = 'text';
                input.id = 'tag-input';
                input.placeholder = tags.length === 0 ? 'Ajouter un mot-clé' : '';
                input.addEventListener('keydown', addTag);
                tagsContainer.appendChild(input);
                
                // Mettre à jour l'input caché
                hiddenInput.value = tags.join(', ');
            }
            
            function addTag(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const value = e.target.value.trim();
                    
                    if (value && !tags.includes(value)) {
                        tags.push(value);
                        updateTags();
                    }
                    
                    e.target.value = '';
                }
            }
            
            // Gestion de la suppression des tags
            tagsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.tag-remove')) {
                    const index = parseInt(e.target.closest('.tag-remove').dataset.index);
                    tags.splice(index, 1);
                    updateTags();
                }
            });
            
            // Initialiser
            updateTags();
        }
        
        // Soumettre le formulaire
        function submitForm() {
            // Mettre le contenu du quill dans le textarea
            const contenuTextarea = document.getElementById('contenu');
            contenuTextarea.value = quill.root.innerHTML;
            
            // Soumettre le formulaire
            document.getElementById('article-form').submit();
        }
        
        // Gérer les boutons de publication
        document.addEventListener('DOMContentLoaded', function() {
            initTags();
            
            // Mettre à jour la prévisualisation du titre
            const titleInput = document.getElementById('titre');
            const previewTitle = document.getElementById('preview-title');
            
            titleInput.addEventListener('input', function() {
                previewTitle.textContent = this.value.length > 50 ? this.value.substring(0, 50) + '...' : this.value;
            });
            
            // Gérer la publication vs brouillon
            const form = document.getElementById('article-form');
            const publishBtn = form.querySelector('button[name="publish"]');
            const draftBtn = form.querySelector('button[name="save_draft"]');
            
            if (publishBtn) {
                publishBtn.addEventListener('click', function(e) {
                    document.getElementById('statut').value = 'published';
                });
            }
            
            if (draftBtn) {
                draftBtn.addEventListener('click', function(e) {
                    document.getElementById('statut').value = 'draft';
                });
            }
            
            // Auto-save (toutes les 30 secondes)
            let autoSaveTimer;
            function autoSave() {
                const title = document.getElementById('titre').value;
                const content = quill.root.innerHTML;
                
                if (title || content) {
                    // Ici, vous pourriez implémenter un auto-save via AJAX
                    console.log('Auto-save...');
                }
            }
            
            // Démarrer l'auto-save
            autoSaveTimer = setInterval(autoSave, 30000);
            
            // Arrêter l'auto-save à la soumission
            form.addEventListener('submit', function() {
                clearInterval(autoSaveTimer);
            });
            
            // Prévisualisation de l'image
            const imageUrlInput = document.getElementById('image_url');
            imageUrlInput.addEventListener('input', function() {
                refreshPreview();
            });
        });
        
        // Actualiser la prévisualisation de l'image
        function refreshPreview() {
            const url = document.getElementById('image_url').value;
            const preview = document.querySelector('.image-preview');
            
            if (preview && url) {
                preview.src = url;
                preview.style.display = 'block';
            }
        }
        
        // Confirmation avant de quitter si des modifications non sauvegardées
        window.addEventListener('beforeunload', function(e) {
            const title = document.getElementById('titre').value;
            const originalTitle = '<?php echo addslashes($article['titre'] ?? ''); ?>';
            
            if (title !== originalTitle) {
                e.preventDefault();
                e.returnValue = 'Vous avez des modifications non sauvegardées. Êtes-vous sûr de vouloir quitter ?';
            }
        });
                <?php include 'assets/js/script.js'; ?>
    </script>
</body>
</html>