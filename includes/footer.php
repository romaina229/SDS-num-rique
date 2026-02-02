<?php
// includes/footer.php
$current_year = date('Y');
?>
    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="footer-columns">
                <div class="footer-column">
                    <h3>Shalom Digital Solutions</h3>
                    <p>Votre partenaire en solutions numériques.<br>Nous accompagnons les professionnels, organisations, ONG et projets dans la création, la gestion et l’optimisation de leurs outils numériques.</p>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/?ref=homescreenpwa" class="facebook" target="_blank" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/liferopro" class="twitter" target="_blank" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/in/romain-akpo-2ab8802a8" class="linkedin" target="_blank" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://instagram.com/liferopro" class="instagram" target="_blank" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://wa.me/22994592567" class="whatsapp" target="_blank" title="WhatsApp" style="background-color: #25D366;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="index.php#services" data-tab="web">Sites Web</a></li>
                        <li><a href="index.php#services" data-tab="excel">Gestion et analyse de données</a></li>
                        <li><a href="index.php#services" data-tab="survey">Collecte de Données</a></li>
                        <li><a href="index.php#services" data-tab="formation">Formations</a></li>
                        <li><a href="index.php#tarifs">Tarifs Web</a></li>
                        <li><a href="commande.php">Commander</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Liens rapides</h3>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="index.php#apropos">À propos</a></li>
                        <li><a href="index.php#services">Services</a></li>
                        <li><a href="index.php#tarifs">Tarifs</a></li>
                        <li><a href="commande.php">Commander</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="cookie.php">Politique des cookies</a></li>
                        <li><a href="mentions-legales.php">Mentions légales</a></li>
                        <li><a href="confidentialite.php">Politique de confidentialité</a></li>
                        <li><a href="admin/login.php">Administration</a>
                        </li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul class="contact-info">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Abomey-Calavi, Bénin</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <a href="tel:+2290169351766">+229 01 69 35 17 66</a>
                        </li>
                        <li>
                            <i class="fas fa-mobile-alt"></i>
                            <a href="tel:+2290194592567">+229 01 94 59 25 67</a>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:liferopro@gmail.com">liferopro@gmail.com</a>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Lun-Ven: 09h-18h<br>Sam: 9h-13h</span>
                        </li>
                    </ul>
                    
                    <a href="contact.php" class="btn" style="margin-top: 15px; display: inline-block;">
                        <i class="fas fa-paper-plane"></i> Nous écrire
                    </a>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?php echo $current_year; ?> Shalom Digital Solutions. Tous droits réservés.</p>
                <p style="font-size: 0.8rem; margin-top: 5px;">
                    Développé avec <i class="fas fa-heart" style="color: #e74c3c;"></i> au Bénin
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // Initialiser AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>
    
    <!-- Scripts spécifiques à la page -->
    
    
    <!-- Analytics -->
    <script>
        // Google Analytics
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
        
        ga('create', 'UA-XXXXX-Y', 'auto');
        ga('send', 'pageview');
    </script>
    
    <!-- Chat en direct (exemple) -->
    <script>
        // Intégration du chat
        window.intercomSettings = {
            app_id: "your_app_id",
            name: "Visiteur",
            email: "visiteur@example.com",
            created_at: Date.now()
        };
    </script>
    <script>
        (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/your_app_id';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
    </script>
    
</body>
</html>