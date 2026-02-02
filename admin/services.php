<?php
// admin/services.php
require_once '../includes/functions.php';
requireAdmin();

$db = getDBConnection();

// Définir le taux de AIB (vous pouvez le mettre dans un fichier config si ce n'est pas déjà fait)
$taux_tva = 0.05; // 5% de AIB

// Traitement des actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            // Ajouter un service avec calcul de la AIB
            $caracteristiques = isset($_POST['caracteristiques']) ? $_POST['caracteristiques'] : [];
            $prix_fcfa = floatval($_POST['prix_fcfa']);
            $prix_euro = floatval($_POST['prix_euro']);
            
            // Calculer la AIB et les totaux TTC
            $tva_fcfa = $prix_fcfa * $taux_tva;
            $tva_euro = $prix_euro * $taux_tva;
            $total_ttc_fcfa = $prix_fcfa + $tva_fcfa;
            $total_ttc_euro = $prix_euro + $tva_euro;
            
            $data = [
                'categorie' => $_POST['categorie'],
                'nom' => $_POST['nom'],
                'description' => $_POST['description'],
                'prix_fcfa' => $prix_fcfa,
                'prix_euro' => $prix_euro,
                'tva_fcfa' => $tva_fcfa,
                'tva_euro' => $tva_euro,
                'total_ttc_fcfa' => $total_ttc_fcfa,
                'total_ttc_euro' => $total_ttc_euro,
                'duree' => $_POST['duree'],
                'caracteristiques' => implode('|', array_filter($caracteristiques)),
                'icone' => $_POST['icone'],
                'couleur' => $_POST['couleur'],
                'popular' => isset($_POST['popular']) ? 1 : 0
            ];
            
            // Modifier la requête SQL pour inclure les colonnes AIB
            $sql = "INSERT INTO services (categorie, nom, description, prix_fcfa, prix_euro, 
                    tva_fcfa, tva_euro, total_ttc_fcfa, total_ttc_euro, 
                    duree, caracteristiques, icone, couleur, popular) 
                    VALUES (:categorie, :nom, :description, :prix_fcfa, :prix_euro, 
                    :tva_fcfa, :tva_euro, :total_ttc_fcfa, :total_ttc_euro,
                    :duree, :caracteristiques, :icone, :couleur, :popular)";
            
            $stmt = $db->prepare($sql);
            if ($stmt->execute($data)) {
                $message = 'Service ajouté avec succès';
                $message_type = 'success';
            } else {
                $message = 'Erreur lors de l\'ajout du service';
                $message_type = 'error';
            }
            break;
            
        case 'edit':
            // Modifier un service avec AIB
            $caracteristiques = isset($_POST['caracteristiques']) ? $_POST['caracteristiques'] : [];
            $prix_fcfa = floatval($_POST['prix_fcfa']);
            $prix_euro = floatval($_POST['prix_euro']);
            
            // Calculer la AIB et les totaux TTC
            $tva_fcfa = $prix_fcfa * $taux_tva;
            $tva_euro = $prix_euro * $taux_tva;
            $total_ttc_fcfa = $prix_fcfa + $tva_fcfa;
            $total_ttc_euro = $prix_euro + $tva_euro;
            
            $data = [
                'id' => $_POST['id'],
                'categorie' => $_POST['categorie'],
                'nom' => $_POST['nom'],
                'description' => $_POST['description'],
                'prix_fcfa' => $prix_fcfa,
                'prix_euro' => $prix_euro,
                'tva_fcfa' => $tva_fcfa,
                'tva_euro' => $tva_euro,
                'total_ttc_fcfa' => $total_ttc_fcfa,
                'total_ttc_euro' => $total_ttc_euro,
                'duree' => $_POST['duree'],
                'caracteristiques' => implode('|', array_filter($caracteristiques)),
                'icone' => $_POST['icone'],
                'couleur' => $_POST['couleur'],
                'popular' => isset($_POST['popular']) ? 1 : 0
            ];
            
            // Modifier la requête SQL pour inclure les colonnes AIB
            $sql = "UPDATE services SET 
                    categorie = :categorie,
                    nom = :nom,
                    description = :description,
                    prix_fcfa = :prix_fcfa,
                    prix_euro = :prix_euro,
                    tva_fcfa = :tva_fcfa,
                    tva_euro = :tva_euro,
                    total_ttc_fcfa = :total_ttc_fcfa,
                    total_ttc_euro = :total_ttc_euro,
                    duree = :duree,
                    caracteristiques = :caracteristiques,
                    icone = :icone,
                    couleur = :couleur,
                    popular = :popular
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            if ($stmt->execute($data)) {
                $message = 'Service modifié avec succès';
                $message_type = 'success';
                // Rediriger pour enlever le paramètre edit de l'URL
                header('Location: services.php?success=modified');
                exit();
            } else {
                $message = 'Erreur lors de la modification du service';
                $message_type = 'error';
            }
            break;
            
        case 'delete':
            // Supprimer un service
            $id = $_POST['id'];
            
            // Vérifier si le service est utilisé dans des commandes
            $checkSql = "SELECT COUNT(*) FROM commandes WHERE service_id = :id";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            $count = $checkStmt->fetchColumn();
            
            if ($count > 0) {
                $message = 'Impossible de supprimer ce service car il est utilisé dans des commandes';
                $message_type = 'error';
            } else {
                $sql = "DELETE FROM services WHERE id = :id";
                $stmt = $db->prepare($sql);
                if ($stmt->execute([':id' => $id])) {
                    $message = 'Service supprimé avec succès';
                    $message_type = 'success';
                } else {
                    $message = 'Erreur lors de la suppression du service';
                    $message_type = 'error';
                }
            }
            break;
    }
}

// Vérifier si un message de succès vient d'une redirection
if (isset($_GET['success']) && $_GET['success'] === 'modified') {
    $message = 'Service modifié avec succès';
    $message_type = 'success';
}

// Récupérer tous les services
$sql = "SELECT * FROM services ORDER BY categorie, popular DESC, nom";
$stmt = $db->query($sql);
$services = $stmt->fetchAll();

// Récupérer un service spécifique pour l'édition
$editService = null;
if (isset($_GET['edit'])) {
    $sql = "SELECT * FROM services WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $_GET['edit']]);
    $editService = $stmt->fetch();
}

// Catégories disponibles
$categories = ['web', 'excel', 'survey', 'formation', 'accompagnement'];

// Icônes disponibles
$icons = [
    'fas fa-laptop-code',
    'fas fa-shopping-cart',
    'fas fa-rocket',
    'fas fa-file-excel',
    'fas fa-file-word',
    'fas fa-chart-line',
    'fas fa-clipboard-list',
    'fas fa-poll',
    'fab fa-google',
    'fas fa-graduation-cap',
    'fas fa-mobile-alt',
    'fas fa-code'
];

// Couleurs disponibles
$colors = [
    'secondary' => 'Bleu (principal)',
    'excel' => 'Vert Excel',
    'kobo' => 'Orange KoboToolbox',
    'google' => 'Bleu Google',
    'microsoft' => 'Bleu Microsoft'
];

$page_title = "Gestion des Services - Admin Shalom Digital Solutions";
$page_description = "Gérez vos services et tarifs";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/services.css">
    <style>
        /* Styles pour la section TVA */
        .tva-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .tva-section h4 {
            margin-top: 0;
            color: var(--secondary);
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        
        .price-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .price-detail {
            background-color: white;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #eee;
        }
        
        .price-detail label {
            display: block;
            font-weight: bold;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .price-value {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--accent);
        }
        
        .taux-tva {
            color: var(--secondary);
            font-weight: bold;
        }
        
        /* Styles pour l'affichage des services */
        .price-breakdown {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        
        .price-breakdown div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .price-total {
            font-weight: bold;
            color: var(--accent);
            border-top: 1px solid #ddd;
            padding-top: 5px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-cogs"></i> Gestion des Services</h1>
                <button class="btn btn-primary" id="open-add-service">
                    <i class="fas fa-plus"></i> Ajouter un service
                </button>
            </div>
            
            <!-- Information sur la AIB -->
            <div class="tva-info" style="background-color: #e7f3ff; padding: 10px 15px; border-radius: 5px; margin-bottom: 20px;">
                <i class="fas fa-info-circle"></i> 
                <strong>Taux de l'AIB : <?php echo ($taux_tva * 100); ?>%</strong> - 
                Les montants HT (Hors Taxes), AIB et TTC sont calculés automatiquement.
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <!-- Liste des services par catégorie -->
            <?php foreach ($categories as $categorie): ?>
            <div class="section">
                <h2>
                    <?php 
                    $categorieNames = [
                        'web' => 'Sites Web',
                        'excel' => 'Gestion et analyse',
                        'survey' => 'Collecte de Données',
                        'formation' => 'Formations'
                    ];
                    echo $categorieNames[$categorie] ?? ucfirst($categorie);
                    ?>
                </h2>
                
                <div class="services-grid-admin">
                    <?php 
                    $categorieServices = array_filter($services, function($service) use ($categorie) {
                        return $service['categorie'] === $categorie;
                    });
                    
                    if (empty($categorieServices)): ?>
                    <div class="alert alert-info">
                        Aucun service dans cette catégorie.
                    </div>
                    <?php else: ?>
                        <?php foreach ($categorieServices as $service): ?>
                        <div class="service-card-admin">
                            <?php if ($service['popular']): ?>
                            <div class="popular-badge">Populaire</div>
                            <?php endif; ?>
                            
                            <div class="service-header">
                                <h3><?php echo htmlspecialchars($service['nom']); ?></h3>
                                <span class="service-category"><?php echo $service['categorie']; ?></span>
                            </div>
                            
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                            
                            <!-- Affichage des prix avec AIB -->
                            <div class="service-price">
                                <div class="price-breakdown">
                                    <div>
                                        <span>HT :</span>
                                        <span><?php echo formatPrice($service['prix_fcfa']); ?> </span>
                                    </div>
                                    <div>
                                        <span>AIB (<?php echo ($taux_tva * 100); ?>%) :</span>
                                        <span><?php echo formatPrice($service['tva_fcfa'] ?? ($service['prix_fcfa'] * $taux_tva)); ?> </span>
                                    </div>
                                    <div class="price-total">
                                        <span>TTC :</span>
                                        <span><?php echo formatPrice($service['total_ttc_fcfa'] ?? ($service['prix_fcfa'] * (1 + $taux_tva))); ?> </span>
                                    </div>
                                </div>
                                <small><?php echo formatPrice($service['prix_euro'], '€'); ?> HT</small>
                            </div>
                            
                            <div>Durée : <?php echo htmlspecialchars($service['duree']); ?></div>
                            
                            <ul class="service-features">
                                <?php 
                                $features = explode('|', $service['caracteristiques']);
                                foreach (array_slice($features, 0, 3) as $feature):
                                ?>
                                <li><?php echo htmlspecialchars($feature); ?></li>
                                <?php endforeach; ?>
                                <?php if (count($features) > 3): ?>
                                <li>... et <?php echo count($features) - 3; ?> autres</li>
                                <?php endif; ?>
                            </ul>
                            
                            <div class="service-actions">
                                <a href="?edit=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <button onclick="deleteService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars(addslashes($service['nom'])); ?>')" 
                                        class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Modal d'ajout/modification -->
    <div id="add-service-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-cogs"></i>
                    <?php echo $editService ? 'Modifier le service' : 'Ajouter un service'; ?>
                </h2>
                <button class="close-modal" onclick="closeModal('add-service-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <form method="POST" action="" id="service-form">
                    <input type="hidden" name="action" value="<?php echo $editService ? 'edit' : 'add'; ?>">
                    <?php if ($editService): ?>
                    <input type="hidden" name="id" value="<?php echo $editService['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="categorie">Catégorie <span style="color: red">*</span></label>
                            <select name="categorie" id="categorie" class="form-control" required>
                                <option value="">Sélectionnez une catégorie</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" 
                                    <?php echo ($editService && $editService['categorie'] == $cat) ? 'selected' : ''; ?>>
                                    <?php 
                                    $catNames = [
                                        'web' => 'Sites Web',
                                        'excel' => 'Gestion et analyse',
                                        'survey' => 'Collecte de Données',
                                        'formation' => 'Formations',
                                        'accompagnement' => 'Accompagnement'
                                    ];
                                    echo $catNames[$cat] ?? ucfirst($cat);
                                    ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="nom">Nom du service <span style="color: red">*</span></label>
                            <input type="text" name="nom" id="nom" class="form-control" required
                                   value="<?php echo $editService ? htmlspecialchars($editService['nom']) : ''; ?>"
                                   placeholder="Ex: Site Vitrine Standard">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description <span style="color: red">*</span></label>
                        <textarea name="description" id="description" class="form-control" rows="3" required
                                  placeholder="Description courte du service"><?php echo $editService ? htmlspecialchars($editService['description']) : ''; ?></textarea>
                    </div>
                    
                    <!-- Section Prix HT -->
                    <div class="tva-section">
                        <h4><i class="fas fa-money-bill-wave"></i> Tarification (Hors Taxes)</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="prix_fcfa">Prix HT (FCFA) <span style="color: red">*</span></label>
                                <input type="number" name="prix_fcfa" id="prix_fcfa" class="form-control" required
                                       value="<?php echo $editService ? $editService['prix_fcfa'] : ''; ?>"
                                       placeholder="Ex: 150000"
                                       step="1000"
                                       onchange="updateAIB()">
                            </div>
                            
                            <div class="form-group">
                                <label for="prix_euro">Prix HT (€) <span style="color: red">*</span></label>
                                <input type="number" name="prix_euro" id="prix_euro" class="form-control" required
                                       value="<?php echo $editService ? $editService['prix_euro'] : ''; ?>"
                                       placeholder="Ex: 230"
                                       step="1"
                                       onchange="updateAIB()">
                            </div>
                            
                            <div class="form-group">
                                <label for="duree">Durée estimée <span style="color: red">*</span></label>
                                <input type="text" name="duree" id="duree" class="form-control" required
                                       value="<?php echo $editService ? htmlspecialchars($editService['duree']) : ''; ?>"
                                       placeholder="Ex: 2-3 semaines">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section Calcul AIB -->
                    <div class="tva-section">
                        <h4><i class="fas fa-calculator"></i> Calcul automatique de l'AIB (<?php echo ($taux_tva * 100); ?>%)</h4>
                        
                        <div class="price-grid">
                            <!-- En FCFA -->
                            <div class="price-detail">
                                <label>HT (FCFA) :</label>
                                <div class="price-value" id="preview-ht-fcfa">
                                    <?php 
                                    if ($editService) {
                                        echo formatPrice($editService['prix_fcfa']) . ' FCFA';
                                    } else {
                                        echo '--';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="price-detail">
                                <label>AIB (FCFA) :</label>
                                <div class="price-value" id="preview-tva-fcfa">
                                    <?php 
                                    if ($editService) {
                                        echo formatPrice($editService['tva_fcfa'] ?? ($editService['prix_fcfa'] * $taux_tva)) . ' FCFA';
                                    } else {
                                        echo '--';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="price-detail">
                                <label>TTC (FCFA) :</label>
                                <div class="price-value" id="preview-ttc-fcfa">
                                    <?php 
                                    if ($editService) {
                                        echo formatPrice($editService['total_ttc_fcfa'] ?? ($editService['prix_fcfa'] * (1 + $taux_tva))) . ' FCFA';
                                    } else {
                                        echo '--';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <!-- En Euros -->
                            <div class="price-detail">
                                <label>HT (€) :</label>
                                <div class="price-value" id="preview-ht-euro">
                                    <?php 
                                    if ($editService) {
                                        echo formatPrice($editService['prix_euro']) . ' €';
                                    } else {
                                        echo '--';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="price-detail">
                                <label>AIB (€) :</label>
                                <div class="price-value" id="preview-tva-euro">
                                    <?php 
                                    if ($editService) {
                                        echo formatPrice($editService['tva_euro'] ?? ($editService['prix_euro'] * $taux_tva)) . ' €';
                                    } else {
                                        echo '--';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="price-detail">
                                <label>TTC (€) :</label>
                                <div class="price-value" id="preview-ttc-euro">
                                    <?php 
                                    if ($editService) {
                                        echo formatPrice($editService['total_ttc_euro'] ?? ($editService['prix_euro'] * (1 + $taux_tva))) . ' €';
                                    } else {
                                        echo '--';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div style="text-align: center; font-size: 0.9rem; color: #666;">
                            <i class="fas fa-info-circle"></i> Les montants AIB et TTC sont calculés automatiquement
                        </div>
                    </div>
                    
                    <!-- Autres informations -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="icone">Icône <span style="color: red">*</span></label>
                            <select name="icone" id="icone" class="form-control" required>
                                <option value="">Sélectionnez une icône</option>
                                <?php foreach ($icons as $icon): ?>
                                <option value="<?php echo $icon; ?>" 
                                    <?php echo ($editService && $editService['icone'] == $icon) ? 'selected' : ''; ?>>
                                    <?php echo $icon; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="help-text">Prévisualisation : <i id="icon-preview"></i></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="couleur">Couleur <span style="color: red">*</span></label>
                            <select name="couleur" id="couleur" class="form-control" required>
                                <option value="">Sélectionnez une couleur</option>
                                <?php foreach ($colors as $value => $label): ?>
                                <option value="<?php echo $value; ?>" 
                                    <?php echo ($editService && $editService['couleur'] == $value) ? 'selected' : ''; ?>>
                                    <span class="color-preview color-<?php echo $value; ?>"></span>
                                    <?php echo $label; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="popular" value="1" 
                                    <?php echo ($editService && $editService['popular']) ? 'checked' : ''; ?>>
                                Service populaire
                            </label>
                            <small class="help-text">Affichera une mention "Populaire" sur le service</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Caractéristiques <span style="color: red">*</span></label>
                        <div id="features-container">
                            <?php 
                            $features = $editService ? explode('|', $editService['caracteristiques']) : ['', '', ''];
                            foreach ($features as $index => $feature):
                                if (trim($feature) !== '' || $index < 3):
                            ?>
                            <div class="feature-input">
                                <input type="text" name="caracteristiques[]" class="form-control"
                                       value="<?php echo htmlspecialchars($feature); ?>"
                                       placeholder="Ex: Design responsive">
                                <?php if ($index >= 3): ?>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeFeature(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addFeature()">
                            <i class="fas fa-plus"></i> Ajouter une caractéristique
                        </button>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $editService ? 'Modifier le service' : 'Ajouter le service'; ?>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeModalAndRedirect()">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    // Taux de AIB
    const tauxAIB = <?php echo $taux_tva; ?>;
    
    // Fonction pour formater les nombres
    function formatNumber(num) {
        return new Intl.NumberFormat('fr-FR').format(Math.round(num));
    }
    
    // Fonction pour mettre à jour les prévisualisations AIB
    function updateAIB() {
        const prixFcfa = parseFloat(document.getElementById('prix_fcfa').value) || 0;
        const prixEuro = parseFloat(document.getElementById('prix_euro').value) || 0;
        
        // Calcul des montants en FCFA
        const tvaFcfa = prixFcfa * tauxAIB;
        const ttcFcfa = prixFcfa + tvaFcfa;
        
        // Calcul des montants en Euros
        const tvaEuro = prixEuro * tauxAIB;
        const ttcEuro = prixEuro + tvaEuro;
        
        // Mise à jour des prévisualisations
        document.getElementById('preview-ht-fcfa').textContent = formatNumber(prixFcfa) + ' FCFA';
        document.getElementById('preview-tva-fcfa').textContent = formatNumber(tvaFcfa) + ' FCFA';
        document.getElementById('preview-ttc-fcfa').textContent = formatNumber(ttcFcfa) + ' FCFA';
        
        document.getElementById('preview-ht-euro').textContent = formatNumber(prixEuro) + ' €';
        document.getElementById('preview-tva-euro').textContent = formatNumber(tvaEuro) + ' €';
        document.getElementById('preview-ttc-euro').textContent = formatNumber(ttcEuro) + ' €';
    }
    
    // Gestion des modales
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Mettre à jour la prévisualisation AIB quand on ouvre la modal
        setTimeout(updateAIB, 100);
    }
    
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    function closeModalAndRedirect() {
        closeModal('add-service-modal');
        // Si on est en mode édition, rediriger vers la page sans paramètre
        if (window.location.search.includes('edit=')) {
            window.location.href = window.location.pathname;
        }
    }
    
    // Prévisualisation de l'icône
    const iconSelect = document.getElementById('icone');
    const iconPreview = document.getElementById('icon-preview');
    
    if (iconSelect && iconPreview) {
        iconSelect.addEventListener('change', function() {
            iconPreview.className = this.value;
        });
        
        // Initialiser la prévisualisation
        if (iconSelect.value) {
            iconPreview.className = iconSelect.value;
        }
    }
    
    // Gestion des caractéristiques
    function addFeature() {
        const container = document.getElementById('features-container');
        
        const div = document.createElement('div');
        div.className = 'feature-input';
        div.innerHTML = `
            <input type="text" name="caracteristiques[]" class="form-control" placeholder="Ex: Support technique">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeFeature(this)">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(div);
    }
    
    function removeFeature(button) {
        button.parentElement.remove();
    }
    
    // Suppression d'un service
    function deleteService(id, name) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer le service "${name}" ?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            
            form.appendChild(actionInput);
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Ouvrir la modal d'ajout quand on clique sur le bouton
    document.getElementById('open-add-service').addEventListener('click', function() {
        // Réinitialiser le formulaire
        const form = document.getElementById('service-form');
        if (form) {
            form.reset();
            // Réinitialiser le champ action
            const actionInput = form.querySelector('input[name="action"]');
            if (actionInput) {
                actionInput.value = 'add';
            }
            // Supprimer le champ id s'il existe
            const idInput = form.querySelector('input[name="id"]');
            if (idInput) {
                idInput.remove();
            }
            // Réinitialiser les caractéristiques
            const featuresContainer = document.getElementById('features-container');
            if (featuresContainer) {
                featuresContainer.innerHTML = `
                    <div class="feature-input">
                        <input type="text" name="caracteristiques[]" class="form-control" placeholder="Ex: Design responsive">
                    </div>
                    <div class="feature-input">
                        <input type="text" name="caracteristiques[]" class="form-control" placeholder="Ex: Hébergement inclus">
                    </div>
                    <div class="feature-input">
                        <input type="text" name="caracteristiques[]" class="form-control" placeholder="Ex: Maintenance 1 mois">
                    </div>
                `;
            }
            // Réinitialiser les prévisualisations AIB
            updateAIB();
        }
        openModal('add-service-modal');
    });
    
    // Ouvrir la modal d'édition si on a un paramètre edit
    <?php if (isset($_GET['edit'])): ?>
    document.addEventListener('DOMContentLoaded', function() {
        openModal('add-service-modal');
    });
    <?php endif; ?>
    
    // Écouter les changements dans les champs de prix pour mettre à jour la AIB
    document.getElementById('prix_fcfa')?.addEventListener('input', updateAIB);
    document.getElementById('prix_euro')?.addEventListener('input', updateAIB);
    
    // Validation du formulaire
    document.getElementById('service-form')?.addEventListener('submit', function(e) {
        const features = document.querySelectorAll('input[name="caracteristiques[]"]');
        let hasFeatures = false;
        
        features.forEach(input => {
            if (input.value.trim()) {
                hasFeatures = true;
            }
        });
        
        if (!hasFeatures) {
            e.preventDefault();
            alert('Veuillez ajouter au moins une caractéristique');
            return;
        }
        
        // Validation des prix
        const prixFcfa = parseFloat(document.getElementById('prix_fcfa').value);
        const prixEuro = parseFloat(document.getElementById('prix_euro').value);
        
        if (prixFcfa <= 0 || prixEuro <= 0) {
            e.preventDefault();
            alert('Les prix doivent être supérieurs à zéro');
            return;
        }
    });
    
    // Fermer la modal en cliquant à l'extérieur
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('add-service-modal');
        if (e.target === modal) {
            closeModalAndRedirect();
        }
    });
    
    // Fermer la modal avec la touche Echap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModalAndRedirect();
        }
    });
    
    // Initialiser la prévisualisation AIB au chargement
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('prix_fcfa')?.value || document.getElementById('prix_euro')?.value) {
            updateAIB();
        }
    });
    </script>
</body>
</html>