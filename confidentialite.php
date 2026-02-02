<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="assets/images/Faviconsds.png">
    <title>Politique de Confidentialité - Shalom Digital Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        <?php include 'assets/css/style.css'; ?>
        <?php include 'assets/css/confidentialite.css'; ?>
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">
                <img src="assets/images/Logosds.png" alt="logo site" style="width: 50px; height: 50px; margin-right: 10px; vertical-align: middle;">
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
    <div class="privacy-header">
        <div class="container">
            <h1>Politique de Confidentialité</h1>
            <p>Comment Shalom Digital Solutions protège vos données personnelles</p>
            <div class="effective-date">
                <i class="fas fa-calendar-alt"></i>
                Dernière mise à jour : <?php echo date('d/m/Y'); ?>
            </div>
        </div>
    </div>
    
    <div class="container privacy-container">
        <div class="privacy-content">
            
            <!-- Table des matières -->
            <nav class="toc">
                <h3><i class="fas fa-list"></i> Table des matières</h3>
                <ul>
                    <li><a href="#section1"><i class="fas fa-chevron-right"></i> 1. Introduction et engagement</a></li>
                    <li><a href="#section2"><i class="fas fa-chevron-right"></i> 2. Données que nous collectons</a></li>
                    <li><a href="#section3"><i class="fas fa-chevron-right"></i> 3. Finalités du traitement</a></li>
                    <li><a href="#section4"><i class="fas fa-chevron-right"></i> 4. Base légale du traitement</a></li>
                    <li><a href="#section5"><i class="fas fa-chevron-right"></i> 5. Partage des données</a></li>
                    <li><a href="#section6"><i class="fas fa-chevron-right"></i> 6. Sécurité des données</a></li>
                    <li><a href="#section7"><i class="fas fa-chevron-right"></i> 7. Conservation des données</a></li>
                    <li><a href="#section8"><i class="fas fa-chevron-right"></i> 8. Vos droits</a></li>
                    <li><a href="#section9"><i class="fas fa-chevron-right"></i> 9. Cookies</a></li>
                    <li><a href="#section10"><i class="fas fa-chevron-right"></i> 10. Modifications</a></li>
                </ul>
            </nav>
            
            <!-- Section 1 -->
            <section id="section1" class="privacy-section">
                <div class="section-header">
                    <div class="section-number">1</div>
                    <h2>Introduction et engagement</h2>
                </div>
                
                <p>Chez <strong>Shalom Digital Solutions</strong>, nous considérons la protection de vos données personnelles comme une priorité absolue. Cette politique de confidentialité explique comment nous collectons, utilisons, partageons et protégeons vos informations personnelles conformément à la <strong>Loi n°2009-09 du 22 mai 2009 portant sur la protection des données à caractère personnel en République du Bénin</strong>.</p>
                
                <p>En utilisant nos services, vous acceptez les pratiques décrites dans cette politique. Si vous avez des questions concernant cette politique, veuillez nous contacter aux coordonnées indiquées à la fin de ce document.</p>
                
                <div class="highlight">
                    <p><strong>Notre engagement :</strong> Nous nous engageons à ne jamais vendre, louer ou échanger vos données personnelles avec des tiers à des fins commerciales sans votre consentement explicite.</p>
                </div>
            </section>
            
            <!-- Section 2 -->
            <section id="section2" class="privacy-section">
                <div class="section-header">
                    <div class="section-number">2</div>
                    <h2>Données que nous collectons</h2>
                </div>
                
                <h3>2.1 Données fournies directement par vous</h3>
                <p>Lorsque vous :</p>
                <ul>
                    <li>Passez une commande sur notre site</li>
                    <li>Demandez un devis</li>
                    <li>Vous inscrivez à notre newsletter</li>
                    <li>Nous contactez via le formulaire de contact</li>
                    <li>Postulez à une offre d'emploi</li>
                </ul>
                <p>Nous pouvons collecter :</p>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Catégorie de données</th>
                            <th>Exemples</th>
                            <th>Obligatoire</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Identité</td>
                            <td>Nom, prénom, civilité</td>
                            <td>✓ Pour les commandes</td>
                        </tr>
                        <tr>
                            <td>Coordonnées</td>
                            <td>Email, téléphone, adresse</td>
                            <td>✓ Pour les commandes</td>
                        </tr>
                        <tr>
                            <td>Professionnel</td>
                            <td>Nom de l'entreprise, secteur</td>
                            <td>Optionnel</td>
                        </tr>
                        <tr>
                            <td>Projet</td>
                            <td>Description, besoins, budget</td>
                            <td>✓ Pour les devis</td>
                        </tr>
                        <tr>
                            <td>Paiement</td>
                            <td>Méthode de paiement, numéro de commande</td>
                            <td>✓ Pour les transactions</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>2.2 Données collectées automatiquement</h3>
                <p>Lors de votre navigation sur notre site :</p>
                <ul>
                    <li><strong>Données techniques :</strong> Adresse IP, type de navigateur, système d'exploitation</li>
                    <li><strong>Données de navigation :</strong> Pages visitées, temps de visite, provenance</li>
                    <li><strong>Données de localisation :</strong> Pays, ville (approximative)</li>
                </ul>
                
                <h3>2.3 Données provenant de tiers</h3>
                <p>Nous pouvons recevoir des informations de :</p>
                <ul>
                    <li>Partenaires commerciaux (avec votre consentement)</li>
                    <li>Outils d'analyse (Google Analytics)</li>
                    <li>Réseaux sociaux (si vous interagissez avec nos pages)</li>
                </ul>
            </section>
            
            <!-- Section 3 -->
            <section id="section3" class="privacy-section">
                <div class="section-header">
                    <div class="section-number">3</div>
                    <h2>Finalités du traitement</h2>
                </div>
                
                <p>Nous utilisons vos données personnelles pour les finalités suivantes :</p>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Finalité</th>
                            <th>Données utilisées</th>
                            <th>Base légale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Exécution des commandes</td>
                            <td>Identité, coordonnées, paiement</td>
                            <td>Exécution du contrat</td>
                        </tr>
                        <tr>
                            <td>Service client</td>
                            <td>Coordonnées, historique</td>
                            <td>Intérêt légitime</td>
                        </tr>
                        <tr>
                            <td>Marketing (avec consentement)</td>
                            <td>Email, préférences</td>
                            <td>Consentement</td>
                        </tr>
                        <tr>
                            <td>Analyse et amélioration</td>
                            <td>Données de navigation</td>
                            <td>Intérêt légitime</td>
                        </tr>
                        <tr>
                            <td>Conformité légale</td>
                            <td>Données nécessaires</td>
                            <td>Obligation légale</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="highlight">
                    <h4><i class="fas fa-bullhorn"></i> Marketing et communications</h4>
                    <p>Nous n'envoyons des communications marketing que si vous avez donné votre consentement explicite. Vous pouvez vous désabonner à tout moment en cliquant sur le lien de désabonnement présent dans chaque email ou en nous contactant directement.</p>
                </div>
            </section>
            
            <!-- Section 4 -->
            <section id="section4" class="privacy-section">
                <div class="section-header">
                    <div class="section-number">4</div>
                    <h2>Base légale du traitement</h2>
                </div>
                
                <p>Conformément à la loi béninoise, nous traitons vos données sur l'une des bases légales suivantes :</p>
                
                <ol>
                    <li><strong>Exécution d'un contrat :</strong> Lorsque nous devons traiter vos données pour exécuter un contrat (ex : commande de service).</li>
                    <li><strong>Consentement :</strong> Lorsque vous avez donné votre consentement explicite (ex : newsletter).</li>
                    <li><strong>Intérêt légitime :</strong> Lorsque le traitement est nécessaire pour nos intérêts légitimes (ex : prévention de la fraude).</li>
                    <li><strong>Obligation légale :</strong> Lorsque nous devons respecter une obligation légale (ex : conservation des factures).</li>
                </ol>
            </section>
            
            <!-- Section 5 -->
            <section id="section5" class="privacy-section">
                <div class="section-header">
                    <div class="section-number">5</div>
                    <h2>Partage des données</h2>
                </div>
                
                <p>Nous ne partageons vos données personnelles qu'avec :</p>
                
                <h3>5.1 Prestataires de services</h3>
                <ul>
                    <li><strong>Hébergeur :</strong> Pour l'hébergement de notre site web</li>
                    <li><strong>Processeur de paiement :</strong> Pour le traitement des transactions</li>
                    <li><strong>Service d'emailing :</strong> Pour l'envoi de communications</li>
                    <li><strong>Outils d'analyse :</strong> Pour l'analyse de l'audience</li>
                </ul>
                
                <p>Tous nos prestataires sont sélectionnés avec soin et sont contractuellement obligés de protéger vos données.</p>
                
                <h3>5.2 Autorités légales</h3>
                <p>Nous pouvons être amenés à divulguer vos informations si la loi l'exige ou si nous pensons de bonne foi que cette action est nécessaire pour :</p>
                <ul>
                    <li>Se conformer à une obligation légale</li>
                    <li>Protéger les droits ou la propriété de Shalom Digital Solutions</li>
                    <li>Prévenir ou enquêter sur d'éventuelles infractions</li>
                    <li>Assurer la sécurité personnelle des utilisateurs</li>
                </ul>
                
                <div class="highlight">
                    <p><strong>Transfert international :</strong> La plupart de nos prestataires sont situés au Bénin ou dans l'Union Européenne. En cas de transfert en dehors du Bénin, nous veillons à ce que des garanties appropriées soient en place.</p>
                </div>
            </section>
            
            <!-- Section 6 -->
            <section id="section6" class="privacy-section">
                <div class="section-header">
                    <div class="section-number">6</div>
                    <h2>Sécurité des données</h2>
                </div>
                
                <p>Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles appropriées pour protéger vos données contre :</p>
                
                <ul>
                    <li>Accès non autorisé</li>
                    <li>Altération ou divulgation</li>
                    <li>Destruction accidentelle</li>
                    <li>Perte fortuite</li>
                </ul>
                
                <h3>Mesures de sécurité mises en place :</h3>
                
                <table class="data-table">
                    <tbody>
                        <tr>
                            <td><i class="fas fa-lock"></i> Chiffrement SSL</td>
                            <td>Toutes les données transitent de manière sécurisée</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-shield-alt"></i> Pare-feu</td>
                            <td>Protection contre les accès non autorisés</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-key"></i> Authentification forte</td>
                            <td>Pour l'accès aux données sensibles</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-history"></i> Sauvegardes régulières</td>
                            <td>Pour prévenir la perte de données</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-user-shield"></i> Formation du personnel</td>
                            <td>Sensibilisation à la protection des données</td>
                        </tr>
                    </tbody>
                </table>
                
                <p><strong>Malgré nos efforts, aucune méthode de transmission sur Internet ou de stockage électronique n'est sûre à 100%. Nous ne pouvons donc garantir une sécurité absolue.</strong></p>
            </section>
            
            <!-- Section 7 -->
            <section id="section7" class="privacy-section">
                <div class="section-header">
                    <div class="section-number">7</div>
                    <h2>Conservation des données</h2>
                </div>
                
                <p>Nous conservons vos données personnelles uniquement aussi longtemps que nécessaire aux finalités pour lesquelles elles ont été collectées :</p>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type de données</th>
                            <th>Durée de conservation</th>
                            <th>Justification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Données de commande</td>
                            <td>10 ans</td>
                            <td>Obligation légale (facturation)</td>
                        </tr>
                        <tr>
                            <td>Données de contact (sans commande)</td>
                            <td>3 ans</td>
                            <td>Intérêt légitime</td>
                        </tr>
                        <tr>
                            <td>Données de navigation</td>
                            <td>13 mois</td>
                            <td>Recommandation CNIL</td>
                        </tr>
                        <tr>
                            <td>Newsletter</td>
                            <td>Jusqu'au désabonnement</td>
                            <td>Consentement</td>
                        </tr>
                        <tr>
                            <td>Données archivées</td>
                            <td>Durée légale</td>
                            <td>Conformité réglementaire</td>
                        </tr>
                    </tbody>
                </table>
                
                <p>À l'expiration de ces délais, vos données sont soit supprimées, soit anonymisées pour des analyses statistiques.</p>
            </section>
            
            <!-- Section 8 -->
            <section id="section8" class="privacy-section">
                <div class="section-header">
                    <div class="section-number">8</div>
                    <h2>Vos droits</h2>
                </div>
                
                <p>Conformément à la loi béninoise sur la protection des données, vous disposez des droits suivants :</p>
                
                <div class="highlight">
                    <h4><i class="fas fa-user-check"></i> Liste de vos droits</h4>
                    <ol>
                        <li><strong>Droit d'accès :</strong> Obtenir confirmation que nous traitons vos données et y accéder.</li>
                        <li><strong>Droit de rectification :</strong> Faire corriger des données inexactes ou incomplètes.</li>
                        <li><strong>Droit à l'effacement :</strong> Faire supprimer vos données ("droit à l'oubli").</li>
                        <li><strong>Droit à la limitation :</strong> Limiter le traitement de vos données.</li>
                        <li><strong>Droit à la portabilité :</strong> Recevoir vos données dans un format structuré.</li>
                        <li><strong>Droit d'opposition :</strong> Vous opposer au traitement pour des raisons légitimes.</li>
                        <li><strong>Droit de retirer votre consentement :</strong> À tout moment, pour les traitements basés sur le consentement.</li>
                        <li><strong>Droit de définir des directives :</strong> Concernant le sort de vos données après votre décès.</li>
                    </ol>
                </div>
                
                <h3>Comment exercer vos droits ?</h3>
                <p>Pour exercer vos droits, envoyez votre demande à :</p>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <p><strong>Délégué à la Protection des Données (DPD)</strong><br>
                    Email : <strong>liferopro@gmail.com</strong><br>
                    Adresse postale : Cotonou, Bénin</p>
                </div>
                
                <p><strong>Délai de réponse :</strong> Nous nous engageons à répondre à votre demande dans un délai d'un mois maximum. Ce délai peut être prolongé de deux mois en raison de la complexité de la demande ou du nombre de demandes.</p>
                
                <p><strong>Identification :</strong> Pour traiter votre demande, nous devons vérifier votre identité. Veuillez joindre une copie de votre pièce d'identité à votre demande.</p>
                
                <p><strong>Gratuité :</strong> L'exercice de vos droits est gratuit, sauf si la demande est manifestement infondée ou excessive.</p>
            </section>
            
            <!-- Section 9 -->
            <section id="section9" class="privacy-section">
                <div class="section-header">
                    <div class="section-number">9</div>
                    <h2>Cookies et technologies similaires</h2>
                </div>
                
                <p>Notre site utilise des cookies pour améliorer votre expérience de navigation. Pour plus d'informations, consultez notre <a href="cookies.php">Politique relative aux cookies</a>.</p>
                
                <h3>Types de cookies utilisés :</h3>
                
                <ul>
                    <li><strong>Cookies essentiels :</strong> Nécessaires au fonctionnement du site</li>
                    <li><strong>Cookies de performance :</strong> Pour analyser l'usage du site</li>
                    <li><strong>Cookies de fonctionnalité :</strong> Pour mémoriser vos préférences</li>
                    <li><strong>Cookies de ciblage :</strong> Pour les publicités personnalisées (uniquement avec consentement)</li>
                </ul>
                
                <p>Vous pouvez gérer vos préférences concernant les cookies via les paramètres de votre navigateur.</p>
            </section>
            
            <!-- Section 10 -->
            <section id="section10" class="privacy-section">
                <div class="section-header">
                    <div class="section-number">10</div>
                    <h2>Modifications de cette politique</h2>
                </div>
                
                <p>Nous pouvons mettre à jour cette politique de confidentialité périodiquement pour refléter :</p>
                
                <ul>
                    <li>Les changements dans nos pratiques de traitement des données</li>
                    <li>Les évolutions législatives ou réglementaires</li>
                    <li>Les nouvelles fonctionnalités de nos services</li>
                </ul>
                
                <p>Nous vous informerons des modifications importantes par email (si nous avons votre consentement) ou via une notification visible sur notre site.</p>
                
                <p><strong>Nous vous encourageons à consulter régulièrement cette page pour rester informé de notre politique de confidentialité.</strong></p>
                
                <div style="text-align: center; margin-top: 30px;">
                    <p><i class="fas fa-info-circle"></i> Pour toute question concernant cette politique de confidentialité, contactez-nous :</p>
                </div>
            </section>
            
            <!-- Boîte de contact -->
            <div class="contact-box">
                <h3>Contactez notre Délégué à la Protection des Données</h3>
                <p><i class="fas fa-envelope"></i> Email : <a href="mailto:liferopro@gmail.com">liferopro@gmail.com</a></p>
                <p><i class="fas fa-clock"></i> Délai de réponse : Sous 72 heures</p>
                <p><i class="fas fa-exclamation-triangle"></i> Pour les réclamations, vous pouvez également contacter l'Autorité Béninoise de Protection des Données Personnelles</p>
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
        // Navigation fluide dans la table des matières
        document.querySelectorAll('.toc a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Ajout d'un bouton pour imprimer
        const printBtn = document.createElement('button');
        printBtn.innerHTML = '<i class="fas fa-print"></i> Imprimer cette politique';
        printBtn.className = 'btn';
        printBtn.style.position = 'fixed';
        printBtn.style.bottom = '30px';
        printBtn.style.right = '30px';
        printBtn.style.zIndex = '1000';
        printBtn.onclick = () => window.print();
        document.body.appendChild(printBtn);
        
        // Animation des sections
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.privacy-section').forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(section);
        });
    </script>
</body>
</html>