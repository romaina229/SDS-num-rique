<?php
// commande.php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Configuration de la AIB (vous pouvez mettre cela dans un fichier config.php)
$taux_tva = 0.05; // 5% de AIB (ajustez selon votre pays)

// Traitement des services sélectionnés depuis index.php
$selected_service = null;
$service_id = isset($_GET['service']) ? intval($_GET['service']) : 0;
$direct_order = $service_id > 0;

if ($service_id > 0) {
    $query = "SELECT * FROM services WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $service_id);
    $stmt->execute();
    $selected_service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculer la AIB pour le service sélectionné
    if ($selected_service) {
        $selected_service['tva_fcfa'] = $selected_service['prix_fcfa'] * $taux_tva;
        $selected_service['tva_euro'] = $selected_service['prix_euro'] * $taux_tva;
        $selected_service['total_ttc_fcfa'] = $selected_service['prix_fcfa'] + $selected_service['tva_fcfa'];
        $selected_service['total_ttc_euro'] = $selected_service['prix_euro'] + $selected_service['tva_euro'];
    }
    
    $_SESSION['selected_service'] = $selected_service;
}

// Traitement du formulaire de commande
$success = false;
$error = '';
$order_number = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_nom = htmlspecialchars($_POST['client_nom']);
    $client_email = htmlspecialchars($_POST['client_email']);
    $client_telephone = htmlspecialchars($_POST['client_telephone']);
    $client_entreprise = htmlspecialchars($_POST['client_entreprise']);
    $message = htmlspecialchars($_POST['message']);
    $methode_paiement = htmlspecialchars($_POST['methode_paiement']);
    
    // Validation
    if (empty($client_nom) || empty($client_email) || empty($client_telephone)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Vérifier que le service est sélectionné
        if (!$selected_service) {
            $error = "Veuillez sélectionner un service avant de continuer.";
        } else {
            // Calculer les montants AIB
            $tva_fcfa = $selected_service['prix_fcfa'] * $taux_tva;
            $tva_euro = $selected_service['prix_euro'] * $taux_tva;
            $total_ttc_fcfa = $selected_service['prix_fcfa'] + $tva_fcfa;
            $total_ttc_euro = $selected_service['prix_euro'] + $tva_euro;
            
            // Générer numéro de commande
            $order_number = 'LFP-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Mettre à jour la requête SQL pour inclure la AIB
            $query = "INSERT INTO commandes (
                numero_commande, service_id, service_nom, montant_fcfa, montant_euro, 
                tva_fcfa, tva_euro, total_ttc_fcfa, total_ttc_euro,
                duree_estimee, client_nom, client_email, client_telephone, 
                client_entreprise, message, methode_paiement
            ) VALUES (
                :numero_commande, :service_id, :service_nom, :montant_fcfa, :montant_euro,
                :tva_fcfa, :tva_euro, :total_ttc_fcfa, :total_ttc_euro,
                :duree_estimee, :client_nom, :client_email, :client_telephone,
                :client_entreprise, :message, :methode_paiement
            )";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':numero_commande', $order_number);
            $stmt->bindParam(':service_id', $selected_service['id']);
            $stmt->bindParam(':service_nom', $selected_service['nom']);
            $stmt->bindParam(':montant_fcfa', $selected_service['prix_fcfa']);
            $stmt->bindParam(':montant_euro', $selected_service['prix_euro']);
            $stmt->bindParam(':tva_fcfa', $tva_fcfa);
            $stmt->bindParam(':tva_euro', $tva_euro);
            $stmt->bindParam(':total_ttc_fcfa', $total_ttc_fcfa);
            $stmt->bindParam(':total_ttc_euro', $total_ttc_euro);
            $stmt->bindParam(':duree_estimee', $selected_service['duree']);
            $stmt->bindParam(':client_nom', $client_nom);
            $stmt->bindParam(':client_email', $client_email);
            $stmt->bindParam(':client_telephone', $client_telephone);
            $stmt->bindParam(':client_entreprise', $client_entreprise);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':methode_paiement', $methode_paiement);
            
            if ($stmt->execute()) {
                $success = true;
                
                // Préparer les montants pour l'email
                $montant_ht_fcfa = number_format($selected_service['prix_fcfa'], 0, ',', ' ');
                $montant_tva_fcfa = number_format($tva_fcfa, 0, ',', ' ');
                $montant_ttc_fcfa = number_format($total_ttc_fcfa, 0, ',', ' ');
                
                // Envoyer email de confirmation
                $to = $client_email;
                $subject = "Confirmation de commande Shalom Digital Solutions- " . $order_number;
                $message_email = "
                    Bonjour $client_nom,\n\n
                    Merci pour votre commande chez Shalom Digital Solutions.\n
                    Numéro de commande: $order_number\n
                    Service: {$selected_service['nom']}\n
                    Durée estimée: {$selected_service['duree']}\n
                    \nDÉTAILS FINANCIERS :\n
                    Montant HT: $montant_ht_fcfa FCFA\n
                    AIB ($taux_tva%): $montant_tva_fcfa FCFA\n
                    Montant TTC: $montant_ttc_fcfa FCFA\n\n
                    Notre équipe vous contactera dans les 24 heures.\n\n
                    Cordialement,\n
                    L'équipe Shalom Digital Solutions
                ";
                
                // En production, décommentez cette ligne
                // mail($to, $subject, $message_email);
                
                // Vider la session
                unset($_SESSION['selected_service']);
            } else {
                $error = "Une erreur est survenue lors de l'enregistrement de votre commande.";
            }
        }
    }
}

// Récupérer tous les services
$all_services = [];
if (!$direct_order && !$success) {
    $query = "SELECT * FROM services ORDER BY categorie, nom";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $all_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculer la AIB pour chaque service
    foreach ($all_services as &$service) {
        $service['tva_fcfa'] = $service['prix_fcfa'] * $taux_tva;
        $service['tva_euro'] = $service['prix_euro'] * $taux_tva;
        $service['total_ttc_fcfa'] = $service['prix_fcfa'] + $service['tva_fcfa'];
        $service['total_ttc_euro'] = $service['prix_euro'] + $service['tva_euro'];
    }
}

$default_step = $direct_order ? 2 : 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/images/Faviconsds.png" type="image/png">
    <title>Commander - Shalom Digital Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        <?php include 'assets/css/commande.css'; ?>
        <?php include 'assets/css/style.css'; ?>
        
        /* Styles pour la AIB */
        .tva-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
            font-size: 0.9rem;
        }
        
        .tva-info .taux {
            color: var(--accent);
            font-weight: bold;
        }
        
        .price-details {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .price-breakdown {
            font-size: 0.9rem;
            color: #666;
        }
        
        .price-breakdown div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
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
                    <li><a href="index.php#accueil">Accueil</a></li>
                    <li><a href="index.php#apropos">À propos</a></li>
                    <li><a href="index.php#services">Services</a></li>
                    <li><a href="index.php#tarifs">Tarifs Web</a></li>
                    <li><a href="commande.php" class="btn">Commander</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Section Commande -->
    <section id="commander" class="order-section">
        <div class="container order-container">
            <h1 class="section-title">Commander un Service</h1>
            
            <!-- Information sur la AIB -->
            <div class="tva-info">
                <i class="fas fa-info-circle"></i> Tous les prix sont soumis à l'AIB de 
                <span class="taux"><?php echo ($taux_tva * 100); ?>%</span>
            </div>
            
            <?php if($error): ?>
            <div class="alert alert-error" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="confirmation-message">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Commande confirmée !</h2>
                <p>Votre commande a été enregistrée avec succès. Notre équipe vous contactera dans les 24 heures.</p>
                
                <div class="order-details">
                    <h4>Détails de votre commande :</h4>
                    <div class="summary-item">
                        <span>Numéro de commande :</span>
                        <span><?php echo $order_number; ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Service :</span>
                        <span><?php echo htmlspecialchars($selected_service['nom']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Durée :</span>
                        <span><?php echo htmlspecialchars($selected_service['duree']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Montant HT :</span>
                        <span><?php echo number_format($selected_service['prix_fcfa'], 0, ',', ' '); ?> FCFA</span>
                    </div>
                    <div class="summary-item">
                        <span>AIB (<?php echo ($taux_tva * 100); ?>%) :</span>
                        <span><?php echo number_format($tva_fcfa, 0, ',', ' '); ?> FCFA</span>
                    </div>
                    <div class="summary-item">
                        <span>Montant TTC :</span>
                        <span><?php echo number_format($total_ttc_fcfa, 0, ',', ' '); ?> FCFA</span>
                    </div>
                    <div class="summary-item">
                        <span>Méthode de paiement :</span>
                        <span>
                            <?php 
                            $payment_methods = [
                                'carte' => 'Carte bancaire',
                                'paypal' => 'PayPal',
                                'virement' => 'Virement bancaire',
                                'mobile' => 'Mobile Money'
                            ];
                            echo $payment_methods[$methode_paiement] ?? 'Non spécifiée';
                            ?>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span>Date :</span>
                        <span><?php echo date('d/m/Y H:i'); ?></span>
                    </div>
                </div>
                
                <p>Un email de confirmation a été envoyé à <strong><?php echo htmlspecialchars($client_email); ?></strong>.</p>
                
                <a href="index.php" class="btn" style="margin-top: 30px;">Retour à l'accueil</a>
                <a href="commande.php" class="btn" style="margin-top: 30px; background-color: var(--success);">Nouvelle commande</a>
            </div>
            
            <?php else: ?>
            
            <!-- Bannière service sélectionné -->
            <?php if($direct_order && $selected_service): ?>
            <div class="service-selected-banner">
                <div class="service-selected-info">
                    <h4>Service sélectionné : <?php echo htmlspecialchars($selected_service['nom']); ?></h4>
                    <p><?php echo htmlspecialchars($selected_service['description']); ?></p>
                    <div class="price-details">
                        <div class="price-breakdown">
                            <div>
                                <span>Prix HT :</span>
                                <span><?php echo number_format($selected_service['prix_fcfa'], 0, ',', ' '); ?> FCFA</span>
                            </div>
                            <div>
                                <span>AIB (<?php echo ($taux_tva * 100); ?>%) :</span>
                                <span><?php echo number_format($selected_service['tva_fcfa'], 0, ',', ' '); ?> FCFA</span>
                            </div>
                            <div class="price-total">
                                <span>Total TTC :</span>
                                <span><?php echo number_format($selected_service['total_ttc_fcfa'], 0, ',', ' '); ?> FCFA</span>
                            </div>
                        </div>
                    </div>
                    <div><strong>Durée :</strong> <?php echo htmlspecialchars($selected_service['duree']); ?></div>
                </div>
                <a href="commande.php" class="change-service-btn">Changer de service</a>
            </div>
            <?php endif; ?>
            
            <!-- Étapes de la commande -->
            <div class="order-steps">
                <div class="step <?php echo $default_step >= 1 ? 'completed' : ($default_step == 1 ? 'active' : ''); ?>" id="step1">
                    <div class="step-circle">1</div>
                    <div>Service</div>
                </div>
                <div class="step <?php echo $default_step >= 2 ? 'completed' : ($default_step == 2 ? 'active' : ''); ?>" id="step2">
                    <div class="step-circle">2</div>
                    <div>Informations</div>
                </div>
                <div class="step <?php echo $default_step >= 3 ? 'completed' : ($default_step == 3 ? 'active' : ''); ?>" id="step3">
                    <div class="step-circle">3</div>
                    <div>Paiement</div>
                </div>
                <div class="step <?php echo $default_step >= 4 ? 'completed' : ($default_step == 4 ? 'active' : ''); ?>" id="step4">
                    <div class="step-circle">4</div>
                    <div>Confirmation</div>
                </div>
            </div>
            
            <!-- Formulaire de commande -->
            <form method="POST" action="" class="order-form-container" id="order-form">
                <!-- Étape 1: Sélection du service -->
                <div class="form-page <?php echo $default_step == 1 ? 'active' : ''; ?>" id="page1" <?php echo $direct_order ? 'style="display: none;"' : ''; ?>>
                    <h3>Choisissez votre service</h3>
                    <p>Sélectionnez le service que vous souhaitez commander.</p>
                    
                    <?php if(!empty($all_services)): ?>
                    <div class="service-options">
                        <?php foreach ($all_services as $service): ?>
                        <div class="service-option <?php echo $selected_service && $selected_service['id'] == $service['id'] ? 'selected' : ''; ?>" 
                             data-service="<?php echo $service['id']; ?>"
                             data-price="<?php echo $service['prix_fcfa']; ?>"
                             data-euros="<?php echo $service['prix_euro']; ?>"
                             data-duree="<?php echo htmlspecialchars($service['duree']); ?>"
                             data-name="<?php echo htmlspecialchars($service['nom']); ?>"
                             data-description="<?php echo htmlspecialchars($service['description']); ?>">
                            <h4><?php echo htmlspecialchars($service['nom']); ?></h4>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                            <div class="price-details">
                                <div class="price-breakdown">
                                    <div>
                                        <span>HT :</span>
                                        <span><?php echo number_format($service['prix_fcfa'], 0, ',', ' '); ?> FCFA</span>
                                    </div>
                                    <div>
                                        <span>AIB :</span>
                                        <span><?php echo number_format($service['tva_fcfa'], 0, ',', ' '); ?> FCFA</span>
                                    </div>
                                    <div class="price-total">
                                        <span>TTC :</span>
                                        <span><?php echo number_format($service['total_ttc_fcfa'], 0, ',', ' '); ?> FCFA</span>
                                    </div>
                                </div>
                            </div>
                            <div class="duration">Durée : <?php echo htmlspecialchars($service['duree']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <input type="hidden" name="service_id" id="service_id" value="<?php echo $selected_service ? $selected_service['id'] : ''; ?>">
                    
                    <div class="form-navigation">
                        <div></div>
                        <button type="button" class="btn next-btn" data-next="2">Suivant</button>
                    </div>
                </div>
                
                <!-- Étape 2: Informations personnelles -->
                <div class="form-page <?php echo $default_step == 2 ? 'active' : ''; ?>" id="page2" <?php echo $default_step != 2 ? 'style="display: none;"' : ''; ?>>
                    <h3>Vos informations</h3>
                    <p>Remplissez vos coordonnées pour que nous puissions vous contacter.</p>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom complet *</label>
                            <input type="text" id="nom" name="client_nom" class="form-control" required 
                                   value="<?php echo isset($_POST['client_nom']) ? htmlspecialchars($_POST['client_nom']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="entreprise">Entreprise</label>
                            <input type="text" id="entreprise" name="client_entreprise" class="form-control"
                                   value="<?php echo isset($_POST['client_entreprise']) ? htmlspecialchars($_POST['client_entreprise']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="client_email" class="form-control" required
                                   value="<?php echo isset($_POST['client_email']) ? htmlspecialchars($_POST['client_email']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="telephone">Téléphone *</label>
                            <input type="tel" id="telephone" name="client_telephone" class="form-control" required
                                   value="<?php echo isset($_POST['client_telephone']) ? htmlspecialchars($_POST['client_telephone']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Description de votre projet</label>
                        <textarea id="message" name="message" class="form-control" rows="4" 
                                  placeholder="Décrivez brièvement votre projet, vos attentes, vos besoins spécifiques..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-navigation">
                        <?php if(!$direct_order): ?>
                        <button type="button" class="btn prev-btn" data-prev="1">Précédent</button>
                        <?php endif; ?>
                        <button type="button" class="btn next-btn" data-next="3">Suivant</button>
                    </div>
                </div>
                
                <!-- Étape 3: Paiement -->
                <div class="form-page" id="page3" style="display: none;">
                    <h3>Paiement</h3>
                    <p>Choisissez votre méthode de paiement et finalisez votre commande.</p>
                    
                    <div class="order-summary">
                        <h4>Récapitulatif de votre commande</h4>
                        <div class="summary-item">
                            <span>Service :</span>
                            <span id="summary-service"><?php echo $selected_service ? htmlspecialchars($selected_service['nom']) : 'Non sélectionné'; ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Durée estimée :</span>
                            <span id="summary-duree"><?php echo $selected_service ? htmlspecialchars($selected_service['duree']) : '--'; ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Montant HT :</span>
                            <span id="summary-montant-ht">
                                <?php if($selected_service): ?>
                                <?php echo number_format($selected_service['prix_fcfa'], 0, ',', ' '); ?> FCFA
                                <?php else: ?>
                                -- 
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="summary-item">
                            <span>AIB (<?php echo ($taux_tva * 100); ?>%) :</span>
                            <span id="summary-tva">
                                <?php if($selected_service): ?>
                                <?php echo number_format($selected_service['tva_fcfa'], 0, ',', ' '); ?> FCFA
                                <?php else: ?>
                                -- 
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="summary-item">
                            <span>Méthode de paiement :</span>
                            <span id="summary-paiement">À sélectionner</span>
                        </div>
                        <div class="summary-total">
                            <span>Total TTC :</span>
                            <span id="summary-total">
                                <?php echo $selected_service ? number_format($selected_service['total_ttc_fcfa'], 0, ',', ' ') . ' FCFA' : '--'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <h4 style="margin-top: 30px;">Choisissez votre méthode de paiement</h4>
                    <div class="payment-options">
                        <div class="payment-option" data-payment="carte">
                            <i class="fas fa-credit-card"></i>
                            <h4>Carte bancaire</h4>
                            <p>Paiement sécurisé en ligne</p>
                        </div>
                        
                        <div class="payment-option" data-payment="paypal">
                            <i class="fab fa-paypal"></i>
                            <h4>PayPal</h4>
                            <p>Paiement via votre compte PayPal</p>
                        </div>
                        
                        <div class="payment-option" data-payment="virement">
                            <i class="fas fa-university"></i>
                            <h4>Virement bancaire</h4>
                            <p>Paiement par virement</p>
                        </div>
                        
                        <div class="payment-option" data-payment="mobile">
                            <i class="fas fa-mobile-alt"></i>
                            <h4>Mobile Money</h4>
                            <p>Orange Money, MTN Mobile Money</p>
                        </div>
                    </div>
                    
                    <input type="hidden" name="methode_paiement" id="methode_paiement" value="">
                    
                    <div id="card-form" style="display: none; margin-top: 30px;">
                        <h4>Informations de carte bancaire</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="card-number">Numéro de carte</label>
                                <input type="text" id="card-number" class="form-control" placeholder="1234 5678 9012 3456">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="card-expiry">Date d'expiration</label>
                                <input type="text" id="card-expiry" class="form-control" placeholder="MM/AA">
                            </div>
                            <div class="form-group">
                                <label for="card-cvc">Code CVC</label>
                                <input type="text" id="card-cvc" class="form-control" placeholder="123">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="card-name">Nom sur la carte</label>
                            <input type="text" id="card-name" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn prev-btn" data-prev="2">Précédent</button>
                        <button type="submit" class="btn btn-success" id="confirm-order">Confirmer et Payer</button>
                    </div>
                </div>
            </form>
            
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <?php include 'includes/footer.php'; ?>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const defaultStep = <?php echo $default_step; ?>;
            const directOrder = <?php echo $direct_order ? 'true' : 'false'; ?>;
            const selectedService = <?php echo $selected_service ? json_encode($selected_service) : 'null'; ?>;
            const tauxTVA = <?php echo $taux_tva; ?>;
            
            if (directOrder && selectedService) {
                updateOrderSummary(selectedService);
            }
            
            // Gestion des boutons
            document.querySelectorAll('.next-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const nextPage = this.getAttribute('data-next');
                    goToPage(nextPage);
                });
            });
            
            document.querySelectorAll('.prev-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const prevPage = this.getAttribute('data-prev');
                    goToPage(prevPage);
                });
            });
            
            // Gestion de la sélection des services
            document.querySelectorAll('.service-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.service-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    
                    this.classList.add('selected');
                    
                    const serviceId = this.getAttribute('data-service');
                    document.getElementById('service_id').value = serviceId;
                    
                    // Calculer la AIB en JavaScript
                    const prixHT = parseFloat(this.getAttribute('data-price'));
                    const prixEuro = parseFloat(this.getAttribute('data-euros'));
                    const tvaFcfa = prixHT * tauxAIB;
                    const tvaEuro = prixEuro * tauxAIB;
                    const totalTTCFcfa = prixHT + tvaFcfa;
                    const totalTTCEuro = prixEuro + tvaEuro;
                    
                    const serviceInfo = {
                        id: serviceId,
                        name: this.getAttribute('data-name'),
                        description: this.getAttribute('data-description'),
                        prix_fcfa: prixHT,
                        prix_euro: prixEuro,
                        tva_fcfa: tvaFcfa,
                        tva_euro: tvaEuro,
                        total_ttc_fcfa: totalTTCFcfa,
                        total_ttc_euro: totalTTCEuro,
                        duree: this.getAttribute('data-duree')
                    };
                    
                    updateOrderSummary(serviceInfo);
                });
            });
            
            // Gestion de la sélection des méthodes de paiement
            document.querySelectorAll('.payment-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.payment-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    
                    this.classList.add('selected');
                    
                    const paymentMethod = this.getAttribute('data-payment');
                    document.getElementById('methode_paiement').value = paymentMethod;
                    
                    updatePaymentSummary(paymentMethod);
                    
                    const cardForm = document.getElementById('card-form');
                    if (paymentMethod === 'carte') {
                        cardForm.style.display = 'block';
                    } else {
                        cardForm.style.display = 'none';
                    }
                });
            });
            
            // Validation du formulaire
            document.getElementById('order-form').addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });
            
            function goToPage(pageNumber) {
                if (pageNumber == 2) {
                    if (!validateStep1()) {
                        return;
                    }
                } else if (pageNumber == 3) {
                    if (!validateStep2()) {
                        return;
                    }
                }
                
                document.querySelectorAll('.form-page').forEach(page => {
                    page.style.display = 'none';
                });
                
                const page = document.getElementById('page' + pageNumber);
                if (page) {
                    page.style.display = 'block';
                }
                
                updateSteps(pageNumber);
            }
            
            function validateStep1() {
                const serviceId = document.getElementById('service_id').value;
                if (!serviceId) {
                    alert('Veuillez sélectionner un service avant de continuer.');
                    return false;
                }
                return true;
            }
            
            function validateStep2() {
                const nom = document.getElementById('nom').value;
                const email = document.getElementById('email').value;
                const telephone = document.getElementById('telephone').value;
                
                if (!nom || !email || !telephone) {
                    alert('Veuillez remplir tous les champs obligatoires (nom, email, téléphone).');
                    return false;
                }
                
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    alert('Veuillez entrer une adresse email valide.');
                    return false;
                }
                
                return true;
            }
            
            function validateForm() {
                const paymentMethod = document.getElementById('methode_paiement').value;
                if (!paymentMethod) {
                    alert('Veuillez sélectionner une méthode de paiement.');
                    return false;
                }
                return true;
            }
            
            function updateSteps(currentPage) {
                document.querySelectorAll('.step').forEach((step, index) => {
                    const stepNumber = index + 1;
                    
                    step.classList.remove('active', 'completed');
                    
                    if (stepNumber < currentPage) {
                        step.classList.add('completed');
                    } else if (stepNumber == currentPage) {
                        step.classList.add('active');
                    }
                });
            }
            
            function updateOrderSummary(serviceInfo) {
                if (!serviceInfo) return;
                
                document.getElementById('summary-service').textContent = serviceInfo.name || 'Non sélectionné';
                document.getElementById('summary-duree').textContent = serviceInfo.duree || '--';
                
                if (serviceInfo.prix_fcfa) {
                    const formattedHT = parseInt(serviceInfo.prix_fcfa).toLocaleString('fr-FR');
                    const formattedAIB = parseInt(serviceInfo.tva_fcfa).toLocaleString('fr-FR');
                    const formattedTTC = parseInt(serviceInfo.total_ttc_fcfa).toLocaleString('fr-FR');
                    
                    document.getElementById('summary-montant-ht').textContent = `${formattedHT} FCFA`;
                    document.getElementById('summary-tva').textContent = `${formattedAIB} FCFA`;
                    document.getElementById('summary-total').textContent = `${formattedTTC} FCFA`;
                } else {
                    document.getElementById('summary-montant-ht').textContent = '--';
                    document.getElementById('summary-tva').textContent = '--';
                    document.getElementById('summary-total').textContent = '--';
                }
            }
            
            function updatePaymentSummary(paymentMethod) {
                const paymentMethods = {
                    'carte': 'Carte bancaire',
                    'paypal': 'PayPal',
                    'virement': 'Virement bancaire',
                    'mobile': 'Mobile Money'
                };
                
                document.getElementById('summary-paiement').textContent = 
                    paymentMethods[paymentMethod] || 'Non spécifiée';
            }
            
            if (defaultStep > 1) {
                updateSteps(defaultStep);
            }
        });
    </script>
</body>
</html>