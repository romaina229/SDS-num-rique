<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Variables pour le formulaire
$success = false;
$error = '';
$form_data = [];

// Traitement du formulaire de contact
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération et nettoyage des données
    $form_data['nom'] = htmlspecialchars(trim($_POST['nom'] ?? ''));
    $form_data['email'] = htmlspecialchars(trim($_POST['email'] ?? ''));
    $form_data['telephone'] = htmlspecialchars(trim($_POST['telephone'] ?? ''));
    $form_data['entreprise'] = htmlspecialchars(trim($_POST['entreprise'] ?? ''));
    $form_data['sujet'] = htmlspecialchars(trim($_POST['sujet'] ?? ''));
    $form_data['message'] = htmlspecialchars(trim($_POST['message'] ?? ''));
    
    // Validation
    $errors = [];
    
    if (empty($form_data['nom'])) {
        $errors[] = "Le nom est obligatoire.";
    }
    
    if (empty($form_data['email'])) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    
    if (empty($form_data['sujet'])) {
        $errors[] = "Le sujet est obligatoire.";
    }
    
    if (empty($form_data['message'])) {
        $errors[] = "Le message est obligatoire.";
    }
    
    // Si aucune erreur, procéder
    if (empty($errors)) {
        try {
            // Générer un numéro de référence
            $reference = 'CONTACT-' . date('Ymd') . '-' . strtoupper(uniqid());
            
            // Insérer dans la base de données
            $query = "INSERT INTO contacts (
                reference, nom, email, telephone, entreprise, 
                sujet, message, date_creation, statut
            ) VALUES (
                :reference, :nom, :email, :telephone, :entreprise, 
                :sujet, :message, NOW(), 'nouveau'
            )";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':reference', $reference);
            $stmt->bindParam(':nom', $form_data['nom']);
            $stmt->bindParam(':email', $form_data['email']);
            $stmt->bindParam(':telephone', $form_data['telephone']);
            $stmt->bindParam(':entreprise', $form_data['entreprise']);
            $stmt->bindParam(':sujet', $form_data['sujet']);
            $stmt->bindParam(':message', $form_data['message']);
            
            if ($stmt->execute()) {
                $success = true;
                
                // Envoyer email de notification à l'admin
                $to_admin = 'liferopro@gmail.com'; // Remplacer par votre email
                $subject_admin = "Nouveau message de contact - " . $reference;
                $message_admin = "
                    Nouveau message de contact reçu via le site Shalom Digital Solutions.\n\n
                    Référence: $reference\n
                    Nom: {$form_data['nom']}\n
                    Email: {$form_data['email']}\n
                    Téléphone: {$form_data['telephone']}\n
                    Entreprise: {$form_data['entreprise']}\n
                    Sujet: {$form_data['sujet']}\n\n
                    Message:\n{$form_data['message']}\n\n
                    Connectez-vous à l'admin pour gérer ce message.
                ";
                
                // Envoyer email de confirmation au visiteur
                $to_visitor = $form_data['email'];
                $subject_visitor = "Confirmation de votre message - Shalom Digital Solutions";
                $message_visitor = "
                    Bonjour {$form_data['nom']},\n\n
                    Merci de nous avoir contacté. Nous avons bien reçu votre message.\n\n
                    Détails de votre demande :\n
                    Référence: $reference\n
                    Sujet: {$form_data['sujet']}\n
                    Date: " . date('d/m/Y à H:i') . "\n\n
                    Notre équipe traitera votre demande dans les plus brefs délais.\n
                    Nous vous répondrons dans un délai maximum de 48 heures.\n\n
                    Cordialement,\n
                    L'équipe Shalom Digital Solutions\n
                    liferopro@gmail.com\n
                    (+229) 01 69 35 17 66
                ";
                
                // En production, décommentez ces lignes
               
                mail($to_admin, $subject_admin, $message_admin);
                mail($to_visitor, $subject_visitor, $message_visitor);
                
                
                // Réinitialiser le formulaire
                $form_data = [];
                
            } else {
                $error = "Une erreur est survenue lors de l'envoi de votre message. Veuillez réessayer.";
            }
            
        } catch (PDOException $e) {
            $error = "Erreur technique : " . $e->getMessage();
        }
        
    } else {
        $error = implode("<br>", $errors);
    }
}

// Récupérer les catégories de sujets depuis la base (optionnel)
$sujets = [
    'Demande de devis',
    'Projet personnalisé',
    'Collaboration',
    'Renseignements généraux',
    'Reservation de service',
    'Informations sur les services',
    'Support technique',
    'Partenariat',
    'Réclamation',
    'Autre'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="assets/images/Faviconds.png">
    <title>Contact - Shalom Digital Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        <?php include 'assets/css/style.css'; ?>
        <?php include 'assets/css/contact.css'; ?>
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
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="contact.php" class="active">Contact</a></li>
                    <li><a href="commande.php" class="btn">Commander</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Section Hero -->
    <section class="contact-hero">
        <div class="container">
            <h1>Contactez-nous</h1>
            <p>Nous sommes là pour répondre à toutes vos questions et vous accompagner dans vos projets numériques.</p>
        </div>
    </section>

    <!-- Contenu principal -->
    <section class="contact-container">
        <div class="contact-content">
            
            <!-- Côté gauche : Formulaire -->
            <div class="contact-form-section">
                <div class="form-header">
                    <h2><i class="fas fa-paper-plane"></i> Envoyez-nous un message</h2>
                    <p>Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais.</p>
                </div>
                
                <?php if($success): ?>
                <div class="alert alert-success success-animation">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <h4 style="margin: 0 0 10px 0;">Message envoyé avec succès !</h4>
                        <p style="margin: 0;">Nous avons bien reçu votre message et vous répondrons dans les 48 heures. Un email de confirmation vous a été envoyé.</p>
                    </div>
                </div>
                <?php elseif($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <h4 style="margin: 0 0 10px 0;">Erreur lors de l'envoi</h4>
                        <p style="margin: 0;"><?php echo $error; ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="contact-form" id="contactForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom complet <span class="required">*</span></label>
                            <input type="text" id="nom" name="nom" class="form-control" required 
                                   value="<?php echo $form_data['nom'] ?? ''; ?>"
                                   placeholder="Votre nom et prénom">
                        </div>
                        
                        <div class="form-group">
                            <label for="entreprise">Entreprise</label>
                            <input type="text" id="entreprise" name="entreprise" class="form-control"
                                   value="<?php echo $form_data['entreprise'] ?? ''; ?>"
                                   placeholder="Nom de votre entreprise">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-control" required
                                   value="<?php echo $form_data['email'] ?? ''; ?>"
                                   placeholder="votre@email.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" class="form-control"
                                   value="<?php echo $form_data['telephone'] ?? ''; ?>"
                                   placeholder="(+229) XX XX XX XX">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="sujet">Sujet <span class="required">*</span></label>
                        <select id="sujet" name="sujet" class="form-control" required>
                            <option value="">-- Sélectionnez un sujet --</option>
                            <?php foreach($sujets as $sujet): ?>
                            <option value="<?php echo $sujet; ?>" 
                                <?php echo ($form_data['sujet'] ?? '') == $sujet ? 'selected' : ''; ?>>
                                <?php echo $sujet; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Votre message <span class="required">*</span></label>
                        <textarea id="message" name="message" class="form-control" required
                                  placeholder="Décrivez votre projet, posez vos questions..."><?php echo $form_data['message'] ?? ''; ?></textarea>
                        <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                            <small style="color: var(--text-light);">Minimum 20 caractères</small>
                            <span id="charCount">0/2000</span>
                        </div>
                    </div>
                    
                    <!-- Protection anti-spam -->
                    <div class="form-group" style="display: none;">
                        <label for="website">Site web</label>
                        <input type="text" id="website" name="website" class="form-control">
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span id="submitText">Envoyer le message</span>
                        <span id="submitSpinner" class="spinner" style="display: none;"></span>
                    </button>
                    
                    <p style="text-align: center; margin-top: 20px; color: var(--text-light); font-size: 0.9rem;">
                        <i class="fas fa-shield-alt"></i> Vos données sont protégées et ne seront jamais partagées.
                    </p>
                </form>
            </div>
            
            <!-- Côté droit : Informations -->
            <div class="contact-info-section">
                
                <!-- Coordonnées -->
                <div class="info-card">
                    <h3><i class="fas fa-address-book"></i> Nos coordonnées</h3>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Adresse</h4>
                            <p>Abomey-Calavi, Bénin<br>
                            <small>Gbègnigan en face du bar Temple du Son</small></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h4>Téléphone</h4>
                            <p>
                                <a href="tel:+2290169351766">(+229) 01 69 35 17 66</a><br>
                                <small>Du lundi au vendredi, 9h-18h</small>
                            </p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email</h4>
                            <p>
                                <a href="mailto:liferopro@gmail.com">liferopro@gmail.com</a><br>
                                <a href="mailto:romainakpo86@gmail.com">romainakpo86@gmail.com</a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h4>Réseaux sociaux</h4>
                            <div style="display: flex; gap: 15px; margin-top: 10px;">
                                <a href="https://www.facebook.com/?ref=homescreenpwa" target="_blank" style="color: #1877F2;">
                                    <i class="fab fa-facebook fa-2x"></i>
                                </a>
                                <a href="https://twitter.com/liferopro" target="_blank" style="color: #1DA1F2;">
                                    <i class="fab fa-twitter fa-2x"></i>
                                </a>
                                <a href="https://www.linkedin.com/in/romain-akpo-2ab8802a8" target="_blank" style="color: #0A66C2;">
                                    <i class="fab fa-linkedin fa-2x"></i>
                                </a>
                                <a href="https://wa.me/2290194592567" target="_blank" style="color: #25D366;">
                                    <i class="fab fa-whatsapp fa-2x"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Horaires -->
                <div class="info-card">
                    <h3><i class="fas fa-calendar-alt"></i> Horaires d'ouverture</h3>
                    
                    <div class="schedule-item">
                        <span class="day">Lundi - Vendredi</span>
                        <span class="hours">9h00 - 18h00</span>
                    </div>
                    
                    <div class="schedule-item">
                        <span class="day">Samedi</span>
                        <span class="hours">09h00 - 13h00</span>
                    </div>
                    
                    <div class="schedule-item">
                        <span class="day">Dimanche</span>
                        <span class="hours closed">Fermé</span>
                    </div>
                    
                    <div style="margin-top: 25px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <p style="margin: 0; color: var(--text-light); font-size: 0.9rem;">
                            <i class="fas fa-info-circle"></i> Les rendez-vous en dehors des horaires sont possibles sur demande.
                        </p>
                    </div>
                </div>
                
                <!-- FAQ -->
                <div class="info-card">
                    <h3><i class="fas fa-question-circle"></i> Questions fréquentes</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            <span>Quel est le délai de réponse ?</span>
                            <i class="fas fa-chevron-down faq-toggle"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Nous nous engageons à répondre à toutes les demandes dans un délai maximum de 48 heures ouvrables. Pour les urgences, contactez-nous par téléphone.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            <span>Proposez-vous des consultations gratuites ?</span>
                            <i class="fas fa-chevron-down faq-toggle"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Oui, nous offrons une première consultation gratuite de 30 minutes pour discuter de votre projet et évaluer vos besoins.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            <span>Comment se passe le suivi après la commande ?</span>
                            <i class="fas fa-chevron-down faq-toggle"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Chaque client bénéficie d'un accompagnement personnalisé avec des points d'étape réguliers et un support dédié pendant toute la durée du projet.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Carte -->
                <div class="info-card">
                    <h3><i class="fas fa-map"></i> Localisation</h3>
                    <div class="map-container">
                        <div class="map-placeholder">
                            <i class="fas fa-map-marked-alt"></i>
                            <p>Abomey-Calavi, Bénin</p>
                            <small>Cliquez pour agrandir</small>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <?php include 'includes/footer.php'; ?>
    </footer>

    <script>
        // Gestion du formulaire
        document.addEventListener('DOMContentLoaded', function() {
            const contactForm = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            const messageTextarea = document.getElementById('message');
            const charCount = document.getElementById('charCount');
            
            // Compteur de caractères
            messageTextarea.addEventListener('input', function() {
                const length = this.value.length;
                charCount.textContent = `${length}/2000`;
                
                if (length > 2000) {
                    charCount.style.color = '#dc3545';
                    this.style.borderColor = '#dc3545';
                } else if (length < 20) {
                    charCount.style.color = '#ffc107';
                    this.style.borderColor = '#ffc107';
                } else {
                    charCount.style.color = '#28a745';
                    this.style.borderColor = '#28a745';
                }
            });
            
            // Initialiser le compteur
            messageTextarea.dispatchEvent(new Event('input'));
            
            // Validation avant soumission
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validation côté client
                const nom = document.getElementById('nom').value.trim();
                const email = document.getElementById('email').value.trim();
                const sujet = document.getElementById('sujet').value;
                const message = document.getElementById('message').value.trim();
                const website = document.getElementById('website').value;
                
                let errors = [];
                
                if (!nom) errors.push("Le nom est obligatoire.");
                if (!email) errors.push("L'email est obligatoire.");
                if (!sujet) errors.push("Le sujet est obligatoire.");
                if (message.length < 20) errors.push("Le message doit contenir au moins 20 caractères.");
                if (message.length > 2000) errors.push("Le message ne peut pas dépasser 2000 caractères.");
                
                // Protection anti-spam (honeypot)
                if (website) {
                    console.log('Spam détecté');
                    return false;
                }
                
                if (errors.length > 0) {
                    alert(errors.join('\n'));
                    return false;
                }
                
                // Désactiver le bouton et afficher le spinner
                submitBtn.disabled = true;
                submitText.textContent = 'Envoi en cours...';
                submitSpinner.style.display = 'inline-block';
                
                // Soumettre le formulaire
                setTimeout(() => {
                    this.submit();
                }, 1000);
            });
            
            // Gestion des FAQ
            function toggleFAQ(element) {
                const answer = element.nextElementSibling;
                const toggle = element.querySelector('.faq-toggle');
                
                answer.classList.toggle('show');
                toggle.classList.toggle('rotated');
            }
            
            // Initialiser les FAQ
            document.querySelectorAll('.faq-question').forEach(question => {
                const answer = question.nextElementSibling;
                if (answer.textContent.trim().length > 0) {
                    // Déjà géré par toggleFAQ
                }
            });
            
            // Animation de la carte (simulée)
            const mapPlaceholder = document.querySelector('.map-placeholder');
            if (mapPlaceholder) {
                mapPlaceholder.addEventListener('click', function() {
                    this.innerHTML = `
                        <i class="fas fa-map" style="font-size: 4rem; margin-bottom: 20px;"></i>
                        <h3 style="margin: 0 0 10px 0;">Abomey-Calavi, Bénin</h3>
                        <p style="margin: 0 0 10px 0; font-size: 0.9rem;">
                            Gbègnigan en face du bar Temple du Son<br>
                            Proximité aéroport
                        </p>
                        <a href="https://maps.google.com/?q=Abomey-Calavi+Bénin" 
                           target="_blank" 
                           style="color: white; text-decoration: underline;">
                            Ouvrir dans Google Maps
                        </a>
                    `;
                });
            }
            
            // Auto-sélection du sujet si présent dans l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const sujetFromUrl = urlParams.get('sujet');
            if (sujetFromUrl) {
                const sujetSelect = document.getElementById('sujet');
                for (let option of sujetSelect.options) {
                    if (option.value.includes(sujetFromUrl)) {
                        option.selected = true;
                        break;
                    }
                }
            }
            
            // Ajouter un préfixe automatique au téléphone
            const telephoneInput = document.getElementById('telephone');
            telephoneInput.addEventListener('focus', function() {
                if (!this.value) {
                    this.value = '(+229) ';
                }
            });
            
            telephoneInput.addEventListener('blur', function() {
                if (this.value === '(+229) ') {
                    this.value = '';
                }
            });
        });
        
        // Animation au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observer les cartes d'information
        document.querySelectorAll('.info-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
                <?php include 'assets/js/script.js'; ?>
    </script>
</body>
</html>