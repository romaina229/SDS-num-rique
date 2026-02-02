<?php
session_start();

// Vérifier si l'utilisateur est admin
if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Traitement des actions
$message = '';
$error = '';

// Ajouter un article
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $titre = htmlspecialchars($_POST['titre']);
    $contenu = $_POST['contenu']; // On ne sanitise pas pour garder le HTML
    $categorie = htmlspecialchars($_POST['categorie']);
    $image_url = htmlspecialchars($_POST['image_url']);
    $auteur = htmlspecialchars($_POST['auteur']);
    $mots_cles = htmlspecialchars($_POST['mots_cles']);
    
    if(empty($titre) || empty($contenu)) {
        $error = "Le titre et le contenu sont obligatoires.";
    } else {
        $query = "INSERT INTO blog_articles (titre, contenu, categorie, image_url, auteur, mots_cles) 
                  VALUES (:titre, :contenu, :categorie, :image_url, :auteur, :mots_cles)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':contenu', $contenu);
        $stmt->bindParam(':categorie', $categorie);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':auteur', $auteur);
        $stmt->bindParam(':mots_cles', $mots_cles);
        
        if($stmt->execute()) {
            $message = "Article ajouté avec succès !";
        } else {
            $error = "Erreur lors de l'ajout de l'article.";
        }
    }
}

// Supprimer un article
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $query = "DELETE FROM blog_articles WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if($stmt->execute()) {
        $message = "Article supprimé avec succès !";
    } else {
        $error = "Erreur lors de la suppression.";
    }
}

// Récupérer tous les articles
$query = "SELECT * FROM blog_articles ORDER BY date_publication DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/images/Logosds.png">
    <title>Administration Blog - Shalom Digital Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        <?php include '../assets/css/style.css'; ?>
        
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
        
        /* Formulaire */
        .admin-form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
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
        
        /* Éditeur WYSIWYG */
        #editor {
            height: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
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
        
        /* Liste des articles */
        .articles-table {
            width: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .articles-table th,
        .articles-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .articles-table th {
            background: var(--accent-light);
            color: var(--accent);
            font-weight: 600;
        }
        
        .articles-table tr:hover {
            background: #f9f9f9;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-small {
            padding: 5px 15px;
            font-size: 0.9rem;
        }
        
        .btn-edit {
            background: var(--primary);
        }
        
        .btn-delete {
            background: #dc3545;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        /* Messages */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
            .articles-table {
                display: block;
                overflow-x: auto;
            }
            
            .admin-nav ul {
                flex-direction: column;
            }
        }
        
        /* Prévisualisation image */
        .image-preview {
            max-width: 200px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }
        
        .image-preview.visible {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Header simplifié -->
    <header>
        <div class="container header-container">
            <a href="../index.php" class="logo">
                <img src="../assets/images/Logosds.png" alt="logo site" style="width: 50px; height: 50px; margin-right: 10px; vertical-align: middle;">
                Shalom Digital<span>Solutions</span>
            </a>
            
            <nav>
                <ul>
                    <li><a href="../index.php">Site public</a></li>
                    <li><a href="index.php" class="btn">Admin</a></li>
                    <li><a href="../logout.php" class="btn btn-delete">Déconnexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Contenu principal -->
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-newspaper"></i> Administration du Blog</h1>
            <p>Gérez facilement les articles de votre blog</p>
        </div>
        
        <!-- Navigation admin -->
        <nav class="admin-nav">
            <ul>
                <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="blog-admin.php"><i class="fas fa-newspaper"></i> Blog</a></li>
                <li><a href="services-admin.php"><i class="fas fa-cogs"></i> Services</a></li>
                <li><a href="commandes-admin.php"><i class="fas fa-shopping-cart"></i> Commandes</a></li>
            </ul>
        </nav>
        
        <!-- Messages -->
        <?php if($message): ?>
        <div class="message success">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="message error">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <!-- Formulaire d'ajout -->
        <div class="admin-form-container">
            <h2><i class="fas fa-plus-circle"></i> Ajouter un nouvel article</h2>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="titre">Titre de l'article *</label>
                        <input type="text" id="titre" name="titre" class="form-control" required>
                    </div>
                    
                    <div class="form-group" style="flex: 1;">
                        <label for="categorie">Catégorie</label>
                        <select id="categorie" name="categorie" class="form-control">
                            <option value="">-- Sélectionner --</option>
                            <option value="Développement Web">Développement Web</option>
                            <option value="Excel & Data">Excel & Data</option>
                            <option value="Collecte de données">Collecte de données</option>
                            <option value="Formation">Formation</option>
                            <option value="Actualités">Actualités</option>
                            <option value="Conseils">Conseils</option>
                            <option value="Accompagnement">Accompagnement</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="auteur">Auteur</label>
                        <input type="text" id="auteur" name="auteur" class="form-control" value="Équipe Shalom Digital Solutions">
                    </div>
                    
                    <div class="form-group" style="flex: 2;">
                        <label for="mots_cles">Mots-clés (séparés par des virgules)</label>
                        <input type="text" id="mots_cles" name="mots_cles" class="form-control" placeholder="ex: web, Bénin, développement">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image_url">URL de l'image d'illustration</label>
                    <input type="text" id="image_url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
                    <img id="image_preview" src="" alt="Prévisualisation" class="image-preview">
                </div>
                
                <div class="form-group">
                    <label for="contenu">Contenu de l'article *</label>
                    <div id="editor"></div>
                    <textarea id="contenu" name="contenu" style="display: none;"></textarea>
                </div>
                
                <div class="form-navigation">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Publier l'article
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Liste des articles existants -->
        <div class="admin-form-container">
            <h2><i class="fas fa-list"></i> Articles publiés (<?php echo count($articles); ?>)</h2>
            
            <?php if(empty($articles)): ?>
            <p style="text-align: center; padding: 40px; color: var(--text-light);">
                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 20px; display: block;"></i>
                Aucun article publié pour le moment
            </p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="articles-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Date</th>
                            <th>Auteur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($articles as $article): ?>
                        <tr>
                            <td>#<?php echo $article['id']; ?></td>
                            <td><?php echo htmlspecialchars(substr($article['titre'], 0, 50)); ?>...</td>
                            <td>
                                <span class="article-category"><?php echo htmlspecialchars($article['categorie']); ?></span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($article['date_publication'])); ?></td>
                            <td><?php echo htmlspecialchars($article['auteur']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="blog-edit.php?id=<?php echo $article['id']; ?>" class="btn btn-small btn-edit">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="?delete=<?php echo $article['id']; ?>" class="btn btn-small btn-delete" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        // Initialiser l'éditeur WYSIWYG
        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });
        
        // Transférer le contenu du quill vers le textarea avant soumission
        document.querySelector('form').addEventListener('submit', function(e) {
            const contenuTextarea = document.getElementById('contenu');
            contenuTextarea.value = quill.root.innerHTML;
        });
        
        // Prévisualisation de l'image
        const imageUrlInput = document.getElementById('image_url');
        const imagePreview = document.getElementById('image_preview');
        
        imageUrlInput.addEventListener('input', function() {
            if(this.value) {
                imagePreview.src = this.value;
                imagePreview.classList.add('visible');
            } else {
                imagePreview.classList.remove('visible');
            }
        });
        
        // Charger un article existant (pour édition)
        <?php if(isset($_GET['edit'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const articleId = <?php echo intval($_GET['edit']); ?>;
            // Charger l'article via AJAX ici
        });
        <?php endif; ?>
    </script>
</body>
</html>