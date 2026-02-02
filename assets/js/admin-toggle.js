// admin-toggle.js
document.addEventListener('DOMContentLoaded', function() {
    // Bouton hamburger
    const hamburgerBtn = document.querySelector('.hamburger-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileOverlay = document.querySelector('.mobile-overlay');
    const closeMobileMenu = document.querySelector('.close-mobile-menu');
    
    // Ouvrir le menu mobile
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function() {
            mobileMenu.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Fermer le menu mobile
    if (closeMobileMenu) {
        closeMobileMenu.addEventListener('click', function() {
            mobileMenu.classList.remove('open');
            document.body.style.overflow = '';
        });
    }
    
    // Fermer en cliquant sur l'overlay
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', function() {
            mobileMenu.classList.remove('open');
            document.body.style.overflow = '';
        });
    }
    
    // Fermer en appuyant sur Echap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenu.classList.contains('open')) {
            mobileMenu.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
    
    // Activer les liens actifs dans le menu
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.sidebar-nav a, .mobile-nav a');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
        
        // Supprimer la classe active des autres liens
        link.addEventListener('click', function() {
            navLinks.forEach(otherLink => otherLink.classList.remove('active'));
            this.classList.add('active');
            
            // Fermer le menu mobile après un clic
            if (mobileMenu.classList.contains('open')) {
                mobileMenu.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    });
});