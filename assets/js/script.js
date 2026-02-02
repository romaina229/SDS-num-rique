// assets/js/script.js
document.addEventListener('DOMContentLoaded', function() {
    // Menu mobile
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const nav = document.querySelector('nav');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            nav.classList.toggle('active');
            this.innerHTML = nav.classList.contains('active') ? 
                '<i class="fas fa-times"></i>' : 
                '<i class="fas fa-bars"></i>';
        });
    }
    
    // Fermer le menu en cliquant sur un lien
    document.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', function() {
            nav.classList.remove('active');
            if (mobileMenuBtn) {
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    });
    
    // Navigation entre les catégories de services
    const tabButtons = document.querySelectorAll('.tab-btn');
    const serviceCategories = document.querySelectorAll('.services-category');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            tabButtons.forEach(btn => btn.classList.remove('active'));
            
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            
            // Cacher toutes les catégories
            serviceCategories.forEach(category => {
                category.classList.remove('active');
            });
            
            // Afficher la catégorie correspondante
            const tabId = this.getAttribute('data-tab');
            document.getElementById(`${tabId}-services`).classList.add('active');
            
            // Animation de scroll
            document.getElementById('services').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        });
    });
    
    // Navigation depuis le footer
    document.querySelectorAll('.footer-column a[data-tab]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tabId = this.getAttribute('data-tab');
            
            // Activer le bon bouton d'onglet
            tabButtons.forEach(btn => btn.classList.remove('active'));
            document.querySelector(`.tab-btn[data-tab="${tabId}"]`).classList.add('active');
            
            // Afficher la catégorie
            serviceCategories.forEach(category => {
                category.classList.remove('active');
            });
            document.getElementById(`${tabId}-services`).classList.add('active');
            
            // Scroller vers la section services
            document.getElementById('services').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        });
    });
    
    // Animation au défilement
    const header = document.querySelector('header');
    const scrollToTopBtn = document.createElement('div');
    scrollToTopBtn.className = 'scroll-to-top';
    scrollToTopBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
    document.body.appendChild(scrollToTopBtn);
    
    window.addEventListener('scroll', function() {
        // Header shadow
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Scroll to top button
        if (window.scrollY > 300) {
            scrollToTopBtn.classList.add('visible');
        } else {
            scrollToTopBtn.classList.remove('visible');
        }
        
        // Animation des éléments au scroll
        animateOnScroll();
    });
    
    // Bouton scroll to top
    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Animation des éléments
    function animateOnScroll() {
        const elements = document.querySelectorAll('.service-card, .pricing-card, .about-image');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.2;
            
            if (elementPosition < screenPosition) {
                element.classList.add('fade-in-up');
            }
        });
    }
    
    // Initialiser l'animation
    animateOnScroll();
    
    // Gestion des alertes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '<i class="fas fa-times"></i>';
        closeBtn.style.cssText = `
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            opacity: 0.7;
        `;
        closeBtn.addEventListener('mouseenter', () => closeBtn.style.opacity = '1');
        closeBtn.addEventListener('mouseleave', () => closeBtn.style.opacity = '0.7');
        closeBtn.addEventListener('click', () => alert.style.display = 'none');
        
        alert.style.position = 'relative';
        alert.appendChild(closeBtn);
        
        // Auto-hide après 5 secondes
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.style.display = 'none', 500);
        }, 5000);
    });
    
    // Smooth scroll pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href === '#') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Validation des formulaires
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('error');
                    
                    let errorMsg = field.parentElement.querySelector('.error-message');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.textContent = 'Ce champ est requis';
                        field.parentElement.appendChild(errorMsg);
                    }
                    errorMsg.style.display = 'block';
                } else {
                    field.classList.remove('error');
                    const errorMsg = field.parentElement.querySelector('.error-message');
                    if (errorMsg) errorMsg.style.display = 'none';
                }
            });
            
            // Validation email
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (field.value && !isValidEmail(field.value)) {
                    valid = false;
                    field.classList.add('error');
                    
                    let errorMsg = field.parentElement.querySelector('.error-message');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.textContent = 'Adresse email invalide';
                        field.parentElement.appendChild(errorMsg);
                    }
                    errorMsg.style.display = 'block';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                
                // Scroll vers le premier champ invalide
                const firstError = form.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstError.focus();
                }
            }
        });
    });
    
    // Fonction de validation d'email
    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Animation de compteur pour les statistiques (si présentes)
    const counters = document.querySelectorAll('.counter');
    if (counters.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = +counter.getAttribute('data-target');
                    const duration = 2000; // 2 secondes
                    const step = target / (duration / 16); // 60fps
                    let current = 0;
                    
                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            counter.textContent = target.toLocaleString();
                            clearInterval(timer);
                        } else {
                            counter.textContent = Math.floor(current).toLocaleString();
                        }
                    }, 16);
                    
                    observer.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });
        
        counters.forEach(counter => observer.observe(counter));
    }
    
    // Tooltips
    const tooltips = document.querySelectorAll('.tooltip');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const tooltipText = this.querySelector('.tooltip-text');
            if (tooltipText) {
                tooltipText.style.visibility = 'visible';
                tooltipText.style.opacity = '1';
            }
        });
        
        tooltip.addEventListener('mouseleave', function() {
            const tooltipText = this.querySelector('.tooltip-text');
            if (tooltipText) {
                tooltipText.style.visibility = 'hidden';
                tooltipText.style.opacity = '0';
            }
        });
    });
    
    // Gestion des modales
    const modalTriggers = document.querySelectorAll('[data-modal]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Fermer les modales
    document.querySelectorAll('.close-modal, .modal-backdrop').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Fermer les modales avec ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.active').forEach(modal => {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    });
    
    // Initialiser les animations
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });

});

