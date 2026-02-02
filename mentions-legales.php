<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="assets/images/Logosds.png">
    <title>Mentions Légales - Shalom Digital Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        <?php include 'assets/css/style.css'; ?>
        <?php include 'assets/css/mentions-legales.css'; ?>
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
                    <li><a href="index.php#services">Services</a></li>
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="commande.php" class="btn">Commander</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Contenu -->
    <div class="legal-header">
        <div class="container">
            <h1>Mentions Légales</h1>
            <p>Informations légales de Shalom Digital Solutions conformément à la législation béninoise</p>
        </div>
    </div>
    
    <div class="container legal-container">
        <div class="legal-content">
            
            <!-- Section 1: Informations sur l'entreprise -->
            <section class="legal-section">
                <h2><i class="fas fa-building"></i> 1. Informations sur l'entreprise</h2>
                
                <table class="info-table">
                    <tr>
                        <td>Dénomination sociale</td>
                        <td>Shalom Digital Solutions</td>
                    </tr>
                    <tr>
                        <td>Forme juridique</td>
                        <td>Entreprise Individuelle (EI)</td>
                    </tr>
                    <tr>
                        <td>Siège social</td>
                        <td>Cotonou, Bénin</td>
                    </tr>
                    <tr>
                        <td>Année de création</td>
                        <td>2021</td>
                    </tr>
                    <tr>
                        <td>Registre de Commerce</td>
                        <td>En cours d'immatriculation</td>
                    </tr>
                    <tr>
                        <td>Numéro d'identification fiscale</td>
                        <td>En cours d'obtention</td>
                    </tr>
                    <tr>
                        <td>Activité principale</td>
                        <td>Services numériques et développement web</td>
                    </tr>
                </table>
            </section>
            
            <!-- Section 2: Contact -->
            <section class="legal-section">
                <h2><i class="fas fa-address-card"></i> 2. Coordonnées de contact</h2>
                
                <table class="info-table">
                    <tr>
                        <td>Email</td>
                        <td>liferopro@gmail.com</td>
                    </tr>
                    <tr>
                        <td>Téléphone</td>
                        <td>(+229) 01 69 35 17</td>
                    </tr>
                    <tr>
                        <td>Site web</td>
                        <td>http://shalomviepro.kesug.com</td>
                    </tr>
                </table>
                
                <div class="highlight-box">
                    <p><strong>Heures de contact :</strong> Du lundi au vendredi, de 9h00 à 18h00 (GMT+1)</p>
                </div>
            </section>
            
            <!-- Section 3: Hébergement -->
            <section class="legal-section">
                <h2><i class="fas fa-server"></i> 3. Hébergement du site</h2>
                
                <table class="info-table">
                    <tr>
                        <td>Hébergeur</td>
                        <td>IONOS SARL</td>
                    </tr>
                    <tr>
                        <td>Adresse</td>
                        <td>7 place de la Gare, 57200 Sarreguemines, France</td>
                    </tr>
                    <tr>
                        <td>Téléphone</td>
                        <td>+33 9 70 80 89 11</td>
                    </tr>
                    <tr>
                        <td>Site web</td>
                        <td>www.ionos.fr</td>
                    </tr>
                </table>
            </section>
            
            <!-- Section 4: Propriété intellectuelle -->
            <section class="legal-section">
                <h2><i class="fas fa-copyright"></i> 4. Propriété intellectuelle</h2>
                
                <p>L'ensemble des éléments constitutifs du site <strong>shalomviepro.com</strong>, notamment :</p>
                
                <ul>
                    <li>Les textes, articles et contenus rédactionnels</li>
                    <li>Les images, illustrations et photographies</li>
                    <li>Les logos, marques et éléments graphiques</li>
                    <li>La structure générale du site et son design</li>
                    <li>Les logiciels, applications et bases de données</li>
                </ul>
                
                <p>Sont la propriété exclusive de <strong>Shalom Digital Solutions</strong> ou de ses partenaires et sont protégés par les lois relatives à la propriété intellectuelle en vigueur au Bénin et par les conventions internationales.</p>
                
                <div class="highlight-box">
                    <p><strong>Toute reproduction, représentation, modification, publication, adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite, sauf autorisation écrite préalable de Shalom Digital Solutions.</strong></p>
                </div>
            </section>
            
            <!-- Section 5: Protection des données personnelles -->
            <section class="legal-section">
                <h2><i class="fas fa-shield-alt"></i> 5. Protection des données personnelles</h2>
                
                <p>Shalom Digital Solutions s'engage à protéger la vie privée de ses utilisateurs conformément à la <strong>Loi n°2009-09 du 22 mai 2009 portant sur la protection des données à caractère personnel en République du Bénin</strong>.</p>
                
                <h3>Données collectées</h3>
                <p>Nous collectons uniquement les données nécessaires à la fourniture de nos services :</p>
                <ul>
                    <li>Nom et prénom</li>
                    <li>Adresse email</li>
                    <li>Numéro de téléphone</li>
                    <li>Nom de l'entreprise (si applicable)</li>
                    <li>Informations relatives à votre projet</li>
                </ul>
                
                <h3>Finalités du traitement</h3>
                <ul>
                    <li>Traitement des commandes et demandes de devis</li>
                    <li>Envoi de communications commerciales (avec consentement)</li>
                    <li>Amélioration de nos services</li>
                    <li>Conformité légale et réglementaire</li>
                </ul>
                
                <h3>Vos droits</h3>
                <p>Conformément à la loi béninoise, vous disposez des droits suivants :</p>
                <ol>
                    <li>Droit d'accès à vos données</li>
                    <li>Droit de rectification</li>
                    <li>Droit à l'effacement ("droit à l'oubli")</li>
                    <li>Droit à la limitation du traitement</li>
                    <li>Droit à la portabilité des données</li>
                    <li>Droit d'opposition</li>
                </ol>
                
                <p>Pour exercer ces droits, contactez-nous à : <strong>liferopro@gmail.com</strong></p>
            </section>
            
            <!-- Section 6: Cookies -->
            <section class="legal-section">
                <h2><i class="fas fa-cookie-bite"></i> 6. Politique relative aux cookies</h2>
                
                <p>Notre site utilise des cookies pour :</p>
                
                <ul>
                    <li><strong>Cookies essentiels :</strong> Nécessaires au fonctionnement du site</li>
                    <li><strong>Cookies analytiques :</strong> Pour analyser l'audience et améliorer le site</li>
                    <li><strong>Cookies de préférences :</strong> Pour mémoriser vos choix (langue, etc.)</li>
                </ul>
                
                <p>Vous pouvez configurer votre navigateur pour refuser les cookies. Certaines fonctionnalités du site pourraient alors ne plus être disponibles.</p>
            </section>
            
            <!-- Section 7: Responsabilité -->
            <section class="legal-section">
                <h2><i class="fas fa-balance-scale"></i> 7. Limitation de responsabilité</h2>
                
                <p>Shalom Digital Solutions s'efforce d'assurer l'exactitude et la mise à jour des informations publiées sur ce site. Cependant, nous ne pouvons garantir :</p>
                
                <ul>
                    <li>L'exactitude, la précision ou l'exhaustivité des informations fournies</li>
                    <li>L'absence de modifications apportées par des tiers (piratage, virus)</li>
                    <li>L'accessibilité permanente et ininterrompue du site</li>
                </ul>
                
                <p>Shalom Digital Solutions décline toute responsabilité pour :</p>
                
                <ol>
                    <li>Les dommages directs ou indirects résultant de l'utilisation du site</li>
                    <li>Les dommages résultant de l'impossibilité d'utiliser le site</li>
                    <li>Les erreurs ou omissions dans le contenu du site</li>
                    <li>L'utilisation qui pourrait être faite des informations présentes sur le site</li>
                </ol>
            </section>
            
            <!-- Section 8: Droit applicable et juridiction -->
            <section class="legal-section">
                <h2><i class="fas fa-gavel"></i> 8. Droit applicable et juridiction compétente</h2>
                
                <p>Les présentes mentions légales sont régies par le <strong>droit béninois</strong>. En cas de litige, les tribunaux de <strong>Cotonou</strong> seront seuls compétents.</p>
                
                <div class="highlight-box">
                    <p><strong>Article 7 de la Loi n°2017-20 du 20 avril 2017 portant Code du numérique en République du Bénin :</strong> Les litiges relatifs aux services de la société de l'information sont soumis à la juridiction des tribunaux béninois lorsque le consommateur a sa résidence habituelle au Bénin.</p>
                </div>
            </section>
            
            <!-- Section 9: Modifications -->
            <section class="legal-section">
                <h2><i class="fas fa-edit"></i> 9. Modifications des mentions légales</h2>
                
                <p>Shalom Digital Solutions se réserve le droit de modifier à tout moment les présentes mentions légales. Les utilisateurs sont invités à les consulter régulièrement.</p>
                
                <p><strong>Dernière mise à jour :</strong> <?php echo date('d/m/Y'); ?></p>
            </section>
            
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
        // Impression des mentions légales
        document.addEventListener('DOMContentLoaded', function() {
            const printBtn = document.createElement('button');
            printBtn.innerHTML = '<i class="fas fa-print"></i> Imprimer';
            printBtn.className = 'btn';
            printBtn.style.position = 'fixed';
            printBtn.style.bottom = '30px';
            printBtn.style.right = '30px';
            printBtn.style.zIndex = '1000';
            printBtn.onclick = function() {
                window.print();
            };
            
            document.body.appendChild(printBtn);
        });
    </script>
</body>
</html>