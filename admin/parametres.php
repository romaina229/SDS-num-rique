<?php
// admin/parametres.php
require_once '../includes/functions.php';
requireAdmin();

$db = getDBConnection();

$message = '';
$message_type = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_general':
            // Mettre à jour les paramètres généraux
            $data = [
                'site_name' => $_POST['site_name'],
                'site_email' => $_POST['site_email'],
                'site_phone' => $_POST['site_phone'],
                'site_address' => $_POST['site_address'],
                'currency_fcfa' => $_POST['currency_fcfa'],
                'currency_euro' => $_POST['currency_euro']
            ];
            
            $success = true;
            foreach ($data as $key => $value) {
                $sql = "INSERT INTO parametres (cle, valeur) VALUES (:cle, :valeur)
                        ON DUPLICATE KEY UPDATE valeur = :valeur2";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':cle' => $key,
                    ':valeur' => $value,
                    ':valeur2' => $value
                ]);
            }
            
            $message = 'Paramètres généraux mis à jour avec succès';
            $message_type = 'success';
            break;
            
        case 'update_seo':
            // Mettre à jour les paramètres SEO
            $data = [
                'meta_title' => $_POST['meta_title'],
                'meta_description' => $_POST['meta_description'],
                'meta_keywords' => $_POST['meta_keywords']
            ];
            
            foreach ($data as $key => $value) {
                $sql = "INSERT INTO parametres (cle, valeur) VALUES (:cle, :valeur)
                        ON DUPLICATE KEY UPDATE valeur = :valeur2";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':cle' => $key,
                    ':valeur' => $value,
                    ':valeur2' => $value
                ]);
            }
            
            $message = 'Paramètres SEO mis à jour avec succès';
            $message_type = 'success';
            break;
            
        case 'update_social':
            // Mettre à jour les réseaux sociaux
            $data = [
                'facebook_url' => $_POST['facebook_url'],
                'twitter_url' => $_POST['twitter_url'],
                'linkedin_url' => $_POST['linkedin_url'],
                'instagram_url' => $_POST['instagram_url']
            ];
            
            foreach ($data as $key => $value) {
                $sql = "INSERT INTO parametres (cle, valeur) VALUES (:cle, :valeur)
                        ON DUPLICATE KEY UPDATE valeur = :valeur2";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':cle' => $key,
                    ':valeur' => $value,
                    ':valeur2' => $value
                ]);
            }
            
            $message = 'Réseaux sociaux mis à jour avec succès';
            $message_type = 'success';
            break;
    }
}

// Récupérer tous les paramètres
$sql = "SELECT cle, valeur FROM parametres";
$stmt = $db->query($sql);
$params = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$page_title = "Paramètres - Admin Shalom Digital Solutions";
$page_description = "Configurez les paramètres de votre site";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'includes/header.php'; ?>
    <style>
        .settings-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .tab-button {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 600;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .tab-button:hover {
            color: var(--primary);
        }
        
        .tab-button.active {
            color: var(--secondary);
            border-bottom-color: var(--secondary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .setting-group {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .setting-group h3 {
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .setting-row {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 20px;
            align-items: center;
        }
        
        @media (max-width: 768px) {
            .setting-row {
                grid-template-columns: 1fr;
            }
        }
        
        .setting-label {
            font-weight: 600;
            color: var(--primary);
        }
        
        .setting-label small {
            display: block;
            font-weight: normal;
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .setting-control {
            width: 100%;
        }
        
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 5px;
            display: inline-block;
            margin-right: 10px;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-cog"></i> Paramètres du site</h1>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <div class="settings-tabs">
                <button class="tab-button active" data-tab="general">
                    <i class="fas fa-globe"></i> Général
                </button>
                <button class="tab-button" data-tab="seo">
                    <i class="fas fa-search"></i> SEO
                </button>
                <button class="tab-button" data-tab="social">
                    <i class="fas fa-share-alt"></i> Réseaux sociaux
                </button>
                <button class="tab-button" data-tab="email">
                    <i class="fas fa-envelope"></i> Emails
                </button>
                <button class="tab-button" data-tab="maintenance">
                    <i class="fas fa-tools"></i> Maintenance
                </button>
            </div>
            
            <!-- Onglet Général -->
            <div id="general-tab" class="tab-content active">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_general">
                    
                    <div class="setting-group">
                        <h3>Informations générales</h3>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                Nom du site
                                <small>Le nom qui apparaît dans le titre</small>
                            </div>
                            <div class="setting-control">
                                <input type="text" name="site_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($params['site_name'] ?? 'Shalom Digital Solutions'); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                Email de contact
                                <small>Email principal pour les communications</small>
                            </div>
                            <div class="setting-control">
                                <input type="email" name="site_email" class="form-control" 
                                       value="<?php echo htmlspecialchars($params['site_email'] ?? 'liferopro@gmail.com'); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                Téléphone
                                <small>Numéro de contact principal</small>
                            </div>
                            <div class="setting-control">
                                <input type="text" name="site_phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($params['site_phone'] ?? '+229 01 69 35 17 66'); ?>">
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                Adresse
                                <small>Adresse physique de l'entreprise</small>
                            </div>
                            <div class="setting-control">
                                <textarea name="site_address" class="form-control" rows="3"><?php echo htmlspecialchars($params['site_address'] ?? 'Cotonou, Bénin'); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="setting-group">
                        <h3>Monnaies</h3>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                Symbole FCFA
                                <small>Affichage pour le Franc CFA</small>
                            </div>
                            <div class="setting-control">
                                <input type="text" name="currency_fcfa" class="form-control" 
                                       value="<?php echo htmlspecialchars($params['currency_fcfa'] ?? 'FCFA'); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                Symbole Euro
                                <small>Affichage pour l'Euro</small>
                            </div>
                            <div class="setting-control">
                                <input type="text" name="currency_euro" class="form-control" 
                                       value="<?php echo htmlspecialchars($params['currency_euro'] ?? '€'); ?>"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Onglet SEO -->
            <div id="seo-tab" class="tab-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_seo">
                    
                    <div class="setting-group">
                        <h3>Optimisation pour les moteurs de recherche</h3>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                Méta-titre
                                <small>Titre qui apparaît dans les résultats de recherche (max 60 caractères)</small>
                            </div>
                            <div class="setting-control">
                                <input type="text" name="meta_title" class="form-control" 
                                       value="<?php echo htmlspecialchars($params['meta_title'] ?? 'Shalom DigitalSolutions- Solutions numériques professionnelles'); ?>"
                                       maxlength="60">
                                <small class="text-muted" id="title-counter">0/60 caractères</small>
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                Méta-description
                                <small>Description qui apparaît dans les résultats de recherche (max 160 caractères)</small>
                            </div>
                            <div class="setting-control">
                                <textarea name="meta_description" class="form-control" rows="3" maxlength="160"><?php echo htmlspecialchars($params['meta_description'] ?? 'Développement web, solutions Excel, collecte de données et formations professionnelles au Bénin et en Afrique.'); ?></textarea>
                                <small class="text-muted" id="desc-counter">0/160 caractères</small>
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                Mots-clés
                                <small>Mots-clés séparés par des virgules</small>
                            </div>
                            <div class="setting-control">
                                <textarea name="meta_keywords" class="form-control" rows="3"><?php echo htmlspecialchars($params['meta_keywords'] ?? 'développement web, excel, kobotoolbox, formation, bénin, afrique'); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les paramètres SEO
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Onglet Réseaux sociaux -->
            <div id="social-tab" class="tab-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_social">
                    
                    <div class="setting-group">
                        <h3>Réseaux sociaux</h3>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                <i class="fab fa-facebook" style="color: #1877f2;"></i> Facebook
                                <small>URL de votre page Facebook</small>
                            </div>
                            <div class="setting-control">
                                <input type="url" name="facebook_url" class="form-control" 
                                       value="<?php echo htmlspecialchars($params['facebook_url'] ?? ''); ?>"
                                       placeholder="https://facebook.com/votrepage">
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                <i class="fab fa-twitter" style="color: #1da1f2;"></i> Twitter
                                <small>URL de votre compte Twitter</small>
                            </div>
                            <div class="setting-control">
                                <input type="url" name="twitter_url" class="form-control" 
                                       value="<?php echo htmlspecialchars($params['twitter_url'] ?? ''); ?>"
                                       placeholder="https://twitter.com/votrecompte">
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                <i class="fab fa-linkedin" style="color: #0077b5;"></i> LinkedIn
                                <small>URL de votre page LinkedIn</small>
                            </div>
                            <div class="setting-control">
                                <input type="url" name="linkedin_url" class="form-control" 
                                       value="<?php echo htmlspecialchars($params['linkedin_url'] ?? ''); ?>"
                                       placeholder="https://linkedin.com/company/votreentreprise">
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <div class="setting-label">
                                <i class="fab fa-instagram" style="color: #e4405f;"></i> Instagram
                                <small>URL de votre compte Instagram</small>
                            </div>
                            <div class="setting-control">
                                <input type="url" name="instagram_url" class="form-control" 
                                       value="<?php echo htmlspecialchars($params['instagram_url'] ?? ''); ?>"
                                       placeholder="https://instagram.com/votrecompte">
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les réseaux sociaux
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Onglet Emails -->
            <div id="email-tab" class="tab-content">
                <div class="setting-group">
                    <h3>Configuration des emails</h3>
                    <p class="text-muted">Cette fonctionnalité sera disponible dans une prochaine mise à jour.</p>
                </div>
            </div>
            
            <!-- Onglet Maintenance -->
            <div id="maintenance-tab" class="tab-content">
                <div class="setting-group">
                    <h3>Mode maintenance</h3>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention :</strong> Le mode maintenance bloque l'accès au site public.
                    </div>
                    
                    <div class="setting-row">
                        <div class="setting-label">
                            Statut du site
                            <small>Activer ou désactiver le mode maintenance</small>
                        </div>
                        <div class="setting-control">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="maintenance_mode" 
                                       <?php echo ($params['maintenance_mode'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="maintenance_mode">
                                    Mode maintenance 
                                    <span id="maintenance-status">
                                        (<?php echo ($params['maintenance_mode'] ?? '0') == '1' ? 'Activé' : 'Désactivé'; ?>)
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="setting-row">
                        <div class="setting-label">
                            Message de maintenance
                            <small>Message affiché aux visiteurs</small>
                        </div>
                        <div class="setting-control">
                            <textarea id="maintenance_message" class="form-control" rows="4"><?php echo htmlspecialchars($params['maintenance_message'] ?? 'Le site est actuellement en maintenance. Nous serons de retour très bientôt !'); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <button type="button" class="btn btn-warning" onclick="saveMaintenanceSettings()">
                            <i class="fas fa-tools"></i> Appliquer les paramètres
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Gestion des onglets
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                // Désactiver tous les onglets
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Activer l'onglet cliqué
                button.classList.add('active');
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId + '-tab').classList.add('active');
            });
        });
        
        // Compteur de caractères pour le SEO
        const titleInput = document.querySelector('input[name="meta_title"]');
        const descInput = document.querySelector('textarea[name="meta_description"]');
        const titleCounter = document.getElementById('title-counter');
        const descCounter = document.getElementById('desc-counter');
        
        if (titleInput && titleCounter) {
            titleInput.addEventListener('input', () => {
                titleCounter.textContent = titleInput.value.length + '/60 caractères';
            });
            titleCounter.textContent = titleInput.value.length + '/60 caractères';
        }
        
        if (descInput && descCounter) {
            descInput.addEventListener('input', () => {
                descCounter.textContent = descInput.value.length + '/160 caractères';
            });
            descCounter.textContent = descInput.value.length + '/160 caractères';
        }
        
        // Mode maintenance
        function saveMaintenanceSettings() {
            const maintenanceMode = document.getElementById('maintenance_mode').checked ? '1' : '0';
            const maintenanceMessage = document.getElementById('maintenance_message').value;
            
            fetch('ajax/save_settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=maintenance&mode=' + maintenanceMode + '&message=' + encodeURIComponent(maintenanceMessage)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const status = document.getElementById('maintenance-status');
                    status.textContent = maintenanceMode === '1' ? '(Activé)' : '(Désactivé)';
                    alert(data.message);
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erreur réseau: ' + error.message);
            });
        }
        
        // Prévisualisation de couleur
        document.querySelectorAll('input[type="color"]').forEach(input => {
            input.addEventListener('input', function() {
                const preview = this.nextElementSibling;
                if (preview && preview.classList.contains('color-preview')) {
                    preview.style.backgroundColor = this.value;
                }
            });
        });
    </script>
</body>
</html>