<?php
session_start();

// Gestion des préférences de cookies
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cookie_preferences'])) {
    // Définir les préférences dans un cookie
    $cookieConsent = [
        'analytics' => isset($_POST['analytics']) ? 'accepted' : 'refused',
        'marketing' => isset($_POST['marketing']) ? 'accepted' : 'refused',
        'functional' => isset($_POST['functional']) ? 'accepted' : 'refused',
        'timestamp' => time()
    ];
    
    // Créer un cookie valide 1 an
    setcookie('shalomvie_cookie_consent', json_encode($cookieConsent), time() + 365 * 24 * 3600, '/', '', true, true);
    
    // Rediriger pour éviter le renvoi du formulaire
    header('Location: cookies.php?success=1');
    exit();
}

// Vérifier si l'utilisateur a déjà donné son consentement
$cookieConsent = isset($_COOKIE['shalomvie_cookie_consent']) ? json_decode($_COOKIE['shalomvie_cookie_consent'], true) : null;
$consentGiven = $cookieConsent !== null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="assets/images/Faviconsds.png">
    <title>Politique des Cookies - Shalom Digital Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        <?php include 'assets/css/style.css'; ?>
        <?php include 'assets/css/cookies.css'; ?>
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
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="commande.php" class="btn">Commander</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Bannière de consentement aux cookies -->
    <div class="cookie-banner" id="cookieBanner">
        <div class="cookie-banner-content">
            <div class="cookie-banner-text">
                <p><strong>Ce site utilise des cookies</strong> pour améliorer votre expérience de navigation, analyser l'audience et vous proposer des contenus personnalisés. En continuant votre navigation, vous acceptez leur utilisation. <a href="cookies.php" style="color: white; text-decoration: underline;">En savoir plus</a></p>
            </div>
            <div class="cookie-banner-actions">
                <button class="btn btn-cookie-reject-all" id="rejectAllCookies">Refuser</button>
                <button class="btn btn-cookie-settings" id="cookieSettingsBtn">Personnaliser</button>
                <button class="btn btn-cookie-accept-all" id="acceptAllCookies">Accepter</button>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="cookies-header">
        <div class="container">
            <h1>Politique de Cookies</h1>
            <p>Comment Shalom Digital Solutions utilise les cookies et technologies similaires</p>
            <div class="last-updated">
                <i class="fas fa-calendar-alt"></i>
                Dernière mise à jour : <?php echo date('d/m/Y'); ?>
            </div>
        </div>
    </div>
    
    <div class="container cookies-container">
        <div class="cookies-content">
            
            <?php if(isset($_GET['success'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                Vos préférences de cookies ont été enregistrées avec succès !
            </div>
            <?php endif; ?>
            
            <!-- Table des matières -->
            <nav class="toc">
                <h3><i class="fas fa-list"></i> Navigation rapide</h3>
                <ul>
                    <li><a href="#section1"><i class="fas fa-chevron-right"></i> 1. Qu'est-ce qu'un cookie ?</a></li>
                    <li><a href="#section2"><i class="fas fa-chevron-right"></i> 2. Types de cookies utilisés</a></li>
                    <li><a href="#section3"><i class="fas fa-chevron-right"></i> 3. Liste détaillée des cookies</a></li>
                    <li><a href="#section4"><i class="fas fa-chevron-right"></i> 4. Comment gérer les cookies ?</a></li>
                    <li><a href="#section5"><i class="fas fa-chevron-right"></i> 5. Cookies tiers</a></li>
                    <li><a href="#section6"><i class="fas fa-chevron-right"></i> 6. Mises à jour de cette politique</a></li>
                </ul>
            </nav>
            
            <!-- Section 1 -->
            <section id="section1" class="cookie-section">
                <div class="section-header">
                    <div class="section-number">1</div>
                    <h2>Qu'est-ce qu'un cookie ?</h2>
                </div>
                
                <p>Un cookie est un petit fichier texte stocké sur votre ordinateur ou appareil mobile lorsque vous visitez un site web. Les cookies sont largement utilisés pour faire fonctionner les sites web de manière efficace, ainsi que pour fournir des informations aux propriétaires du site.</p>
                
                <div class="info-box">
                    <h4><i class="fas fa-info-circle"></i> À quoi servent les cookies ?</h4>
                    <ul>
                        <li><strong>Mémoriser vos préférences</strong> (langue, région, etc.)</li>
                        <li><strong>Améliorer la sécurité</strong> de votre session</li>
                        <li><strong>Analyser l'audience</strong> et les performances du site</li>
                        <li><strong>Personnaliser votre expérience</strong> en fonction de vos intérêts</li>
                        <li><strong>Permettre les fonctionnalités</strong> du site (panier d'achat, etc.)</li>
                    </ul>
                </div>
                
                <p>Les cookies ne sont pas des programmes et ne peuvent pas exécuter de code. Ils ne contiennent pas d'informations personnelles identifiables, sauf si vous les avez fournies volontairement (par exemple, en remplissant un formulaire).</p>
            </section>
            
            <!-- Section 2 -->
            <section id="section2" class="cookie-section">
                <div class="section-header">
                    <div class="section-number">2</div>
                    <h2>Types de cookies utilisés</h2>
                </div>
                
                <p>Nous utilisons différents types de cookies sur notre site :</p>
                
                <div class="cookie-category">
                    <div class="category-header">
                        <h4><span class="cookie-type cookie-type-essential">Essentiels</span> Cookies nécessaires</h4>
                    </div>
                    <div class="category-content">
                        <p>Ces cookies sont indispensables au fonctionnement du site. Ils permettent la navigation et l'utilisation des fonctionnalités de base.</p>
                        <p><strong>Exemples :</strong> Cookies de session, authentification, sécurité.</p>
                        <p><em>Ces cookies ne peuvent pas être désactivés.</em></p>
                    </div>
                </div>
                
                <div class="cookie-category">
                    <div class="category-header">
                        <h4><span class="cookie-type cookie-type-functional">Fonctionnels</span> Cookies de préférences</h4>
                    </div>
                    <div class="category-content">
                        <p>Ces cookies permettent au site de mémoriser vos choix et préférences pour améliorer votre expérience.</p>
                        <p><strong>Exemples :</strong> Langue, région, taille de texte.</p>
                        <p><em>Vous pouvez accepter ou refuser ces cookies.</em></p>
                    </div>
                </div>
                
                <div class="cookie-category">
                    <div class="category-header">
                        <h4><span class="cookie-type cookie-type-analytics">Analytiques</span> Cookies de performance</h4>
                    </div>
                    <div class="category-content">
                        <p>Ces cookies nous aident à comprendre comment les visiteurs interagissent avec notre site, en collectant des informations anonymes.</p>
                        <p><strong>Exemples :</strong> Pages visitées, temps passé, provenance.</p>
                        <p><em>Ces cookies sont optionnels mais nous aident à améliorer le site.</em></p>
                    </div>
                </div>
                
                <div class="cookie-category">
                    <div class="category-header">
                        <h4><span class="cookie-type cookie-type-marketing">Marketing</span> Cookies de ciblage</h4>
                    </div>
                    <div class="category-content">
                        <p>Ces cookies suivent votre navigation pour vous proposer des publicités pertinentes selon vos centres d'intérêt.</p>
                        <p><strong>Exemples :</strong> Cookies publicitaires, réseaux sociaux.</p>
                        <p><em>Nous n'utilisons pas de cookies marketing sans votre consentement explicite.</em></p>
                    </div>
                </div>
                
                <div class="warning-box">
                    <h4><i class="fas fa-exclamation-triangle"></i> Important</h4>
                    <p>Le refus des cookies non essentiels n'affectera pas votre capacité à utiliser notre site, mais certaines fonctionnalités pourraient ne pas être disponibles.</p>
                </div>
            </section>
            
            <!-- Section 3 -->
            <section id="section3" class="cookie-section">
                <div class="section-header">
                    <div class="section-number">3</div>
                    <h2>Liste détaillée des cookies</h2>
                </div>
                
                <p>Voici la liste complète des cookies utilisés sur notre site :</p>
                
                <table class="cookies-table">
                    <thead>
                        <tr>
                            <th>Nom du cookie</th>
                            <th>Fournisseur</th>
                            <th>Type</th>
                            <th>Durée</th>
                            <th>Finalité</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>PHPSESSID</strong></td>
                            <td>Shalom Digital Solutions</td>
                            <td><span class="cookie-type cookie-type-essential">Essentiel</span></td>
                            <td>Session</td>
                            <td>Maintien de votre session de navigation</td>
                        </tr>
                        <tr>
                            <td><strong>shalomvie_cookie_consent</strong></td>
                            <td>Shalom Digital Solutions</td>
                            <td><span class="cookie-type cookie-type-essential">Essentiel</span></td>
                            <td>1 an</td>
                            <td>Mémorise vos préférences concernant les cookies</td>
                        </tr>
                        <tr>
                            <td><strong>_ga</strong></td>
                            <td>Google Analytics</td>
                            <td><span class="cookie-type cookie-type-analytics">Analytique</span></td>
                            <td>2 ans</td>
                            <td>Distinction des utilisateurs</td>
                        </tr>
                        <tr>
                            <td><strong>_gid</strong></td>
                            <td>Google Analytics</td>
                            <td><span class="cookie-type cookie-type-analytics">Analytique</span></td>
                            <td>24 heures</td>
                            <td>Distinction des utilisateurs</td>
                        </tr>
                        <tr>
                            <td><strong>_gat</strong></td>
                            <td>Google Analytics</td>
                            <td><span class="cookie-type cookie-type-analytics">Analytique</span></td>
                            <td>1 minute</td>
                            <td>Limitation du taux de requêtes</td>
                        </tr>
                        <tr>
                            <td><strong>preferred_lang</strong></td>
                            <td>Shalom Digital Solutions</td>
                            <td><span class="cookie-type cookie-type-functional">Fonctionnel</span></td>
                            <td>1 an</td>
                            <td>Mémorise votre langue préférée</td>
                        </tr>
                        <tr>
                            <td><strong>user_theme</strong></td>
                            <td>Shalom DigitalSolutions</td>
                            <td><span class="cookie-type cookie-type-functional">Fonctionnel</span></td>
                            <td>1 an</td>
                            <td>Mémorise votre thème d'affichage (clair/sombre)</td>
                        </tr>
                        <tr>
                            <td><strong>cart_session</strong></td>
                            <td>Shalom Digital Solutions</td>
                            <td><span class="cookie-type cookie-type-essential">Essentiel</span></td>
                            <td>7 jours</td>
                            <td>Mémorise votre panier d'achat</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="info-box">
                    <h4><i class="fas fa-clock"></i> Durée de conservation</h4>
                    <p>Les cookies de session sont automatiquement supprimés lorsque vous fermez votre navigateur. Les cookies persistants restent sur votre appareil jusqu'à leur expiration ou jusqu'à ce que vous les supprimiez manuellement.</p>
                </div>
            </section>
            
            <!-- Section 4 -->
            <section id="section4" class="cookie-section">
                <div class="section-header">
                    <div class="section-number">4</div>
                    <h2>Comment gérer les cookies ?</h2>
                </div>
                
                <h3>4.1 Gestion via notre interface</h3>
                <p>Vous pouvez personnaliser vos préférences à tout moment en utilisant le formulaire ci-dessous :</p>
                
                <div class="preferences-form">
                    <h3><i class="fas fa-sliders-h"></i> Personnaliser vos préférences</h3>
                    
                    <form method="POST" action="" id="cookiePreferencesForm">
                        <input type="hidden" name="cookie_preferences" value="1">
                        
                        <!-- Catégorie Essentielle (obligatoire) -->
                        <div class="cookie-category">
                            <div class="category-header">
                                <h4><span class="cookie-type cookie-type-essential">Essentiels</span> Cookies nécessaires</h4>
                                <label class="switch">
                                    <input type="checkbox" checked disabled>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="category-content">
                                <p>Ces cookies sont indispensables au fonctionnement du site. Ils ne peuvent pas être désactivés.</p>
                            </div>
                        </div>
                        
                        <!-- Catégorie Fonctionnels -->
                        <div class="cookie-category">
                            <div class="category-header">
                                <h4><span class="cookie-type cookie-type-functional">Fonctionnels</span> Cookies de préférences</h4>
                                <label class="switch">
                                    <input type="checkbox" name="functional" <?php echo ($cookieConsent && $cookieConsent['functional'] == 'accepted') || !$consentGiven ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="category-content">
                                <p>Ces cookies permettent de mémoriser vos choix (langue, thème, etc.) pour améliorer votre expérience.</p>
                            </div>
                        </div>
                        
                        <!-- Catégorie Analytiques -->
                        <div class="cookie-category">
                            <div class="category-header">
                                <h4><span class="cookie-type cookie-type-analytics">Analytiques</span> Cookies de performance</h4>
                                <label class="switch">
                                    <input type="checkbox" name="analytics" <?php echo ($cookieConsent && $cookieConsent['analytics'] == 'accepted') || !$consentGiven ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="category-content">
                                <p>Ces cookies nous aident à comprendre comment le site est utilisé, afin de l'améliorer.</p>
                            </div>
                        </div>
                        
                        <!-- Catégorie Marketing -->
                        <div class="cookie-category">
                            <div class="category-header">
                                <h4><span class="cookie-type cookie-type-marketing">Marketing</span> Cookies de ciblage</h4>
                                <label class="switch">
                                    <input type="checkbox" name="marketing" <?php echo ($cookieConsent && $cookieConsent['marketing'] == 'accepted') ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="category-content">
                                <p>Ces cookies permettent d'afficher des publicités pertinentes en fonction de vos centres d'intérêt.</p>
                            </div>
                        </div>
                        
                        <div class="cookie-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Enregistrer mes préférences
                            </button>
                            <button type="button" class="btn" onclick="acceptAllCookies()">
                                <i class="fas fa-check"></i> Tout accepter
                            </button>
                            <button type="button" class="btn btn-danger" onclick="rejectAllCookies()">
                                <i class="fas fa-times"></i> Tout refuser (sauf essentiels)
                            </button>
                        </div>
                    </form>
                </div>
                
                <h3>4.2 Gestion via votre navigateur</h3>
                <p>Vous pouvez également gérer les cookies directement dans les paramètres de votre navigateur :</p>
                
                <table class="cookies-table">
                    <thead>
                        <tr>
                            <th>Navigateur</th>
                            <th>Comment faire ?</th>
                            <th>Lien d'aide</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Google Chrome</strong></td>
                            <td>Paramètres → Confidentialité et sécurité → Cookies</td>
                            <td><a href="https://support.google.com/chrome/answer/95647" target="_blank">Aide Chrome</a></td>
                        </tr>
                        <tr>
                            <td><strong>Mozilla Firefox</strong></td>
                            <td>Préférences → Vie privée et sécurité → Cookies</td>
                            <td><a href="https://support.mozilla.org/fr/kb/cookies" target="_blank">Aide Firefox</a></td>
                        </tr>
                        <tr>
                            <td><strong>Safari</strong></td>
                            <td>Préférences → Confidentialité → Cookies</td>
                            <td><a href="https://support.apple.com/fr-fr/guide/safari/sfri11471/mac" target="_blank">Aide Safari</a></td>
                        </tr>
                        <tr>
                            <td><strong>Microsoft Edge</strong></td>
                            <td>Paramètres → Confidentialité, recherche et services → Cookies</td>
                            <td><a href="https://support.microsoft.com/fr-fr/microsoft-edge/supprimer-les-cookies-dans-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank">Aide Edge</a></td>
                        </tr>
                        <tr>
                            <td><strong>Opera</strong></td>
                            <td>Paramètres → Confidentialité et sécurité → Cookies</td>
                            <td><a href="https://help.opera.com/fr/latest/web-preferences/#cookies" target="_blank">Aide Opera</a></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="info-box">
                    <h4><i class="fas fa-trash-alt"></i> Supprimer les cookies existants</h4>
                    <p>Si vous supprimez les cookies, vos préférences seront perdues et vous devrez à nouveau les définir lors de votre prochaine visite.</p>
                </div>
            </section>
            
            <!-- Section 5 -->
            <section id="section5" class="cookie-section">
                <div class="section-header">
                    <div class="section-number">5</div>
                    <h2>Cookies tiers</h2>
                </div>
                
                <p>Notre site peut intégrer des services tiers qui utilisent leurs propres cookies :</p>
                
                <h3>Google Analytics</h3>
                <p>Nous utilisons Google Analytics pour analyser l'audience de notre site. Les données collectées sont anonymisées.</p>
                <p><strong>Pour désactiver Google Analytics :</strong> <a href="https://tools.google.com/dlpage/gaoptout" target="_blank">Téléchargez le module complémentaire</a></p>
                
                <h3>Services de paiement</h3>
                <p>Lorsque vous effectuez un paiement, nos prestataires (PayPal, Stripe, etc.) peuvent utiliser des cookies pour sécuriser la transaction.</p>
                
                <h3>Réseaux sociaux</h3>
                <p>Les boutons de partage (Facebook, Twitter, LinkedIn) peuvent déposer des cookies si vous êtes connecté à ces plateformes.</p>
                
                <div class="warning-box">
                    <h4><i class="fas fa-external-link-alt"></i> Sites tiers</h4>
                    <p>Notre politique de cookies ne s'applique pas aux sites tiers vers lesquels nous pouvons créer des liens. Nous vous encourageons à lire leurs politiques de confidentialité.</p>
                </div>
            </section>
            
            <!-- Section 6 -->
            <section id="section6" class="cookie-section">
                <div class="section-header">
                    <div class="section-number">6</div>
                    <h2>Mises à jour de cette politique</h2>
                </div>
                
                <p>Nous pouvons mettre à jour cette politique de cookies pour refléter :</p>
                
                <ul>
                    <li>Les changements dans notre utilisation des cookies</li>
                    <li>Les évolutions législatives (RGPD, loi béninoise)</li>
                    <li>L'ajout de nouvelles fonctionnalités sur notre site</li>
                    <li>L'intégration de nouveaux services tiers</li>
                </ul>
                
                <p>La date de dernière mise à jour est indiquée en haut de cette page. En continuant à utiliser notre site après une modification, vous acceptez la nouvelle politique.</p>
                
                <div class="info-box">
                    <h4><i class="fas fa-bell"></i> Notification des changements</h4>
                    <p>Pour les modifications importantes, nous vous informerons via une notification visible sur notre site ou par email si vous avez accepté les cookies de fonctionnalité.</p>
                </div>
            </section>
            
            <!-- Boutons d'action -->
            <div class="cookie-actions-page">
                <button class="manage-cookies-btn" onclick="scrollToPreferences()">
                    <i class="fas fa-sliders-h"></i>
                    Modifier mes préférences
                </button>
                <button class="manage-cookies-btn" onclick="deleteAllCookies()" style="background: var(--danger);">
                    <i class="fas fa-trash-alt"></i>
                    Supprimer tous les cookies
                </button>
            </div>
            
            <!-- Bouton retour -->
            <a href="index.php" class="back-to-home">
                <i class="fas fa-arrow-left"></i>
                Retour à l'accueil
            </a>
            
        </div>
    </div>

    <!-- Footer -->
    <footer id="contact">
        <?php include 'includes/footer.php'; ?>
    </footer>

    <script>
        <?php include 'assets/js/script.js'; ?>
    </script>

    <script>
        // Fonctions pour la gestion des cookies
        function setCookiePreferences(preferences) {
            const cookieConsent = {
                analytics: preferences.analytics ? 'accepted' : 'refused',
                marketing: preferences.marketing ? 'accepted' : 'refused',
                functional: preferences.functional ? 'accepted' : 'refused',
                timestamp: new Date().getTime()
            };
            
            // Définir le cookie
            document.cookie = `shalomvie_cookie_consent=${JSON.stringify(cookieConsent)}; max-age=${365 * 24 * 3600}; path=/; samesite=strict`;
            
            // Masquer la bannière
            document.getElementById('cookieBanner').style.display = 'none';
            
            // Appliquer les préférences
            applyCookiePreferences(cookieConsent);
            
            // Afficher un message de confirmation
            alert('Vos préférences ont été enregistrées !');
        }
        
        function acceptAllCookies() {
            setCookiePreferences({
                analytics: true,
                marketing: true,
                functional: true
            });
            
            // Soumettre le formulaire
            document.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach(checkbox => {
                checkbox.checked = true;
            });
            document.getElementById('cookiePreferencesForm').submit();
        }
        
        function rejectAllCookies() {
            setCookiePreferences({
                analytics: false,
                marketing: false,
                functional: false
            });
            
            // Soumettre le formulaire
            document.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('cookiePreferencesForm').submit();
        }
        
        function applyCookiePreferences(preferences) {
            // Désactiver Google Analytics si refusé
            if (preferences.analytics === 'refused') {
                window['ga-disable-UA-XXXXX-Y'] = true;
                if (window.dataLayer) {
                    window.dataLayer.push({
                        'event': 'cookie_consent_update',
                        'analytics': 'refused'
                    });
                }
            }
            
            // Désactiver les scripts marketing si refusé
            if (preferences.marketing === 'refused') {
                // Code pour désactiver les scripts marketing
                console.log('Cookies marketing désactivés');
            }
        }
        
        function scrollToPreferences() {
            document.querySelector('#section4').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
        
        function deleteAllCookies() {
            if (confirm('Êtes-vous sûr de vouloir supprimer tous les cookies ? Cela vous déconnectera de tous les sites.')) {
                // Supprimer le cookie de consentement
                document.cookie = "shalomvie_cookie_consent=; max-age=0; path=/";
                
                // Rediriger pour actualiser
                window.location.reload();
            }
        }
        
        // Gestionnaires d'événements pour la bannière
        document.addEventListener('DOMContentLoaded', function() {
            const acceptBtn = document.getElementById('acceptAllCookies');
            const rejectBtn = document.getElementById('rejectAllCookies');
            const settingsBtn = document.getElementById('cookieSettingsBtn');
            
            if (acceptBtn) {
                acceptBtn.addEventListener('click', acceptAllCookies);
            }
            
            if (rejectBtn) {
                rejectBtn.addEventListener('click', rejectAllCookies);
            }
            
            if (settingsBtn) {
                settingsBtn.addEventListener('click', function() {
                    document.getElementById('cookieBanner').style.display = 'none';
                    scrollToPreferences();
                });
            }
            
            // Vérifier et appliquer les préférences existantes
            const cookieConsent = getCookie('shalomvie_cookie_consent');
            if (cookieConsent) {
                try {
                    const preferences = JSON.parse(cookieConsent);
                    applyCookiePreferences(preferences);
                } catch (e) {
                    console.error('Erreur de parsing des préférences cookies:', e);
                }
            }
        });
        
        // Fonction utilitaire pour lire les cookies
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }
        
        // Navigation fluide dans la table des matières
        document.querySelectorAll('.toc a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Animation des sections
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.cookie-section').forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(section);
        });
        
        // Gestion de l'affichage/fermeture des catégories
        document.querySelectorAll('.category-header').forEach(header => {
            header.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('i');
                
                if (content.style.maxHeight) {
                    content.style.maxHeight = null;
                    if (icon) icon.className = 'fas fa-chevron-down';
                } else {
                    content.style.maxHeight = content.scrollHeight + 'px';
                    if (icon) icon.className = 'fas fa-chevron-up';
                }
            });
        });
        
        // Initialiser la hauteur des contenus de catégorie
        document.querySelectorAll('.category-content').forEach(content => {
            content.style.maxHeight = content.scrollHeight + 'px';
            content.style.overflow = 'hidden';
            content.style.transition = 'max-height 0.3s ease';
        });
                <?php include 'assets/js/script.js'; ?>
    </script>
</body>
</html>