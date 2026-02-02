// assets/js/commande.js
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let selectedService = null;
    let selectedPayment = null;
    let currentStep = 1;
    const totalSteps = 4;
    
    // Initialiser les étapes
    const steps = document.querySelectorAll('.step');
    const pages = document.querySelectorAll('.form-page');
    const progressBar = document.querySelector('.progress');
    
    // Fonction pour mettre à jour la barre de progression
    function updateProgress() {
        if (progressBar) {
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            progressBar.style.width = `${progress}%`;
        }
    }
    
    // Fonction pour naviguer entre les étapes
    function goToStep(stepNumber) {
        // Validation avant de passer à l'étape suivante
        if (stepNumber > currentStep) {
            if (!validateStep(currentStep)) {
                return;
            }
        }
        
        // Mettre à jour l'étape actuelle
        currentStep = stepNumber;
        
        // Mettre à jour les indicateurs d'étape
        steps.forEach((step, index) => {
            step.classList.remove('active', 'completed');
            if (index + 1 < stepNumber) {
                step.classList.add('completed');
            } else if (index + 1 === stepNumber) {
                step.classList.add('active');
            }
        });
        
        // Afficher la page correspondante
        pages.forEach(page => {
            page.classList.remove('active');
        });
        
        const currentPage = document.getElementById(`page${stepNumber}`);
        if (currentPage) {
            currentPage.classList.add('active');
            
            // Animation
            currentPage.style.animation = 'none';
            setTimeout(() => {
                currentPage.style.animation = 'fadeIn 0.5s';
            }, 10);
        }
        
        // Mettre à jour la barre de progression
        updateProgress();
        
        // Actions spécifiques à chaque étape
        switch(stepNumber) {
            case 3:
                updateOrderSummary();
                break;
            case 4:
                updateConfirmation();
                break;
        }
        
        // Scroll vers le haut de la section
        const orderSection = document.getElementById('commander');
        if (orderSection) {
            orderSection.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }
    }
    
    // Validation des étapes
    function validateStep(step) {
        switch(step) {
            case 1:
                if (!selectedService) {
                    showAlert('Veuillez sélectionner un service', 'error');
                    return false;
                }
                break;
                
            case 2:
                const requiredFields = document.querySelectorAll('#page2 [required]');
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
                const emailField = document.getElementById('email');
                if (emailField && emailField.value && !isValidEmail(emailField.value)) {
                    valid = false;
                    emailField.classList.add('error');
                    
                    let errorMsg = emailField.parentElement.querySelector('.error-message');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.textContent = 'Adresse email invalide';
                        emailField.parentElement.appendChild(errorMsg);
                    }
                    errorMsg.style.display = 'block';
                }
                
                if (!valid) {
                    showAlert('Veuillez corriger les erreurs dans le formulaire', 'error');
                    return false;
                }
                break;
                
            case 3:
                if (!selectedPayment) {
                    showAlert('Veuillez sélectionner une méthode de paiement', 'error');
                    return false;
                }
                
                if (selectedPayment === 'carte') {
                    const cardFields = document.querySelectorAll('#card-form [required]');
                    let cardValid = true;
                    
                    cardFields.forEach(field => {
                        if (!field.value.trim()) {
                            cardValid = false;
                            field.classList.add('error');
                        } else {
                            field.classList.remove('error');
                        }
                    });
                    
                    if (!cardValid) {
                        showAlert('Veuillez remplir tous les champs de la carte bancaire', 'error');
                        return false;
                    }
                }
                break;
        }
        
        return true;
    }
    
    // Fonction de validation d'email
    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Sélection des services
    const serviceOptions = document.querySelectorAll('.service-option');
    serviceOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Retirer la sélection précédente
            serviceOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Ajouter la sélection
            this.classList.add('selected');
            
            // Mettre à jour le service sélectionné
            selectedService = {
                id: this.getAttribute('data-service'),
                name: this.querySelector('h4').textContent,
                price: parseFloat(this.getAttribute('data-price')) || 0,
                euros: parseFloat(this.getAttribute('data-euros')) || 0,
                duree: this.getAttribute('data-duree')
            };
            
            // Mettre à jour le champ caché
            const serviceIdInput = document.getElementById('service_id');
            if (serviceIdInput) {
                serviceIdInput.value = selectedService.id;
            }
            
            // Animation de sélection
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
        });
    });
    
    // Sélection des méthodes de paiement
    const paymentOptions = document.querySelectorAll('.payment-option');
    const paymentMethodInput = document.getElementById('methode_paiement');
    const cardForm = document.getElementById('card-form');
    
    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Retirer la sélection précédente
            paymentOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Ajouter la sélection
            this.classList.add('selected');
            
            // Mettre à jour la méthode de paiement
            selectedPayment = this.getAttribute('data-payment');
            
            // Mettre à jour le champ caché
            if (paymentMethodInput) {
                paymentMethodInput.value = selectedPayment;
            }
            
            // Afficher/masquer le formulaire de carte
            if (cardForm) {
                if (selectedPayment === 'carte') {
                    cardForm.classList.add('active');
                } else {
                    cardForm.classList.remove('active');
                }
            }
            
            // Mettre à jour le récapitulatif
            updateOrderSummary();
            
            // Animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
        });
    });
    
    // Mettre à jour le récapitulatif de commande
    function updateOrderSummary() {
        const summaryService = document.getElementById('summary-service');
        const summaryDuree = document.getElementById('summary-duree');
        const summaryMontant = document.getElementById('summary-montant');
        const summaryPaiement = document.getElementById('summary-paiement');
        const summaryTotal = document.getElementById('summary-total');
        
        if (selectedService) {
            if (summaryService) summaryService.textContent = selectedService.name;
            if (summaryDuree) summaryDuree.textContent = selectedService.duree;
            if (summaryMontant) {
                summaryMontant.textContent = `${formatPrice(selectedService.price)} FCFA (${formatPrice(selectedService.euros)} €)`;
            }
            if (summaryTotal) summaryTotal.textContent = `${formatPrice(selectedService.price)} FCFA`;
        }
        
        if (selectedPayment) {
            if (summaryPaiement) {
                const paymentNames = {
                    'carte': 'Carte bancaire',
                    'paypal': 'PayPal',
                    'virement': 'Virement bancaire',
                    'mobile': 'Mobile Money'
                };
                summaryPaiement.textContent = paymentNames[selectedPayment] || selectedPayment;
            }
        }
    }
    
    // Formater les prix
    function formatPrice(price) {
        return new Intl.NumberFormat('fr-FR').format(price);
    }
    
    // Mettre à jour la page de confirmation
    function updateConfirmation() {
        const confirmationService = document.getElementById('confirmation-service');
        const confirmationMontant = document.getElementById('confirmation-montant');
        const confirmationPaiement = document.getElementById('confirmation-paiement');
        const clientEmail = document.getElementById('client-email');
        const orderNumber = document.getElementById('order-number');
        const orderDate = document.getElementById('order-date');
        
        if (selectedService) {
            if (confirmationService) confirmationService.textContent = selectedService.name;
            if (confirmationMontant) {
                confirmationMontant.textContent = `${formatPrice(selectedService.price)} FCFA (${formatPrice(selectedService.euros)} €)`;
            }
        }
        
        if (selectedPayment) {
            if (confirmationPaiement) {
                const paymentNames = {
                    'carte': 'Carte bancaire',
                    'paypal': 'PayPal',
                    'virement': 'Virement bancaire',
                    'mobile': 'Mobile Money'
                };
                confirmationPaiement.textContent = paymentNames[selectedPayment] || selectedPayment;
            }
        }
        
        // Informations du client
        const emailField = document.getElementById('email');
        if (emailField && clientEmail) {
            clientEmail.textContent = emailField.value;
        }
        
        // Générer un numéro de commande
        if (orderNumber) {
            const date = new Date();
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            orderNumber.textContent = `LFP-${year}${month}${day}-${random}`;
        }
        
        // Date de commande
        if (orderDate) {
            const date = new Date();
            const options = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            orderDate.textContent = date.toLocaleDateString('fr-FR', options);
        }
    }
    
    // Navigation avec les boutons Suivant/Précédent
    document.querySelectorAll('.next-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const nextStep = parseInt(this.getAttribute('data-next'));
            goToStep(nextStep);
        });
    });
    
    document.querySelectorAll('.prev-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const prevStep = parseInt(this.getAttribute('data-prev'));
            goToStep(prevStep);
        });
    });
    
    // Validation en temps réel
    const formFields = document.querySelectorAll('#page2 .form-control');
    formFields.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            this.classList.remove('error');
            const errorMsg = this.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.style.display = 'none';
        });
    });
    
    function validateField(field) {
        const value = field.value.trim();
        
        if (field.hasAttribute('required') && !value) {
            field.classList.add('error');
            showFieldError(field, 'Ce champ est requis');
            return false;
        }
        
        if (field.type === 'email' && value && !isValidEmail(value)) {
            field.classList.add('error');
            showFieldError(field, 'Adresse email invalide');
            return false;
        }
        
        if (field.id === 'telephone' && value && !isValidPhone(value)) {
            field.classList.add('error');
            showFieldError(field, 'Numéro de téléphone invalide');
            return false;
        }
        
        return true;
    }
    
    function isValidPhone(phone) {
        // Validation simple de téléphone
        const re = /^[+]?[0-9\s\-\(\)]{8,20}$/;
        return re.test(phone);
    }
    
    function showFieldError(field, message) {
        let errorMsg = field.parentElement.querySelector('.error-message');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'error-message';
            field.parentElement.appendChild(errorMsg);
        }
        errorMsg.textContent = message;
        errorMsg.style.display = 'block';
    }
    
    // Affichage des alertes
    function showAlert(message, type = 'info') {
        // Créer l'alerte
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            ${message}
            <button class="alert-close"><i class="fas fa-times"></i></button>
        `;
        
        // Style de l'alerte
        alert.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            min-width: 300px;
            max-width: 400px;
            animation: slideIn 0.3s;
        `;
        
        // Ajouter au body
        document.body.appendChild(alert);
        
        // Fermer l'alerte
        const closeBtn = alert.querySelector('.alert-close');
        closeBtn.addEventListener('click', () => {
            alert.style.animation = 'slideOut 0.3s';
            setTimeout(() => alert.remove(), 300);
        });
        
        // Auto-fermeture après 5 secondes
        setTimeout(() => {
            if (alert.parentNode) {
                alert.style.animation = 'slideOut 0.3s';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }
    
    // Animation pour les alertes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    // Simulation de traitement de paiement
    const confirmOrderBtn = document.getElementById('confirm-order');
    if (confirmOrderBtn) {
        confirmOrderBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validation de l'étape 3
            if (!validateStep(3)) {
                return;
            }
            
            // Désactiver le bouton et montrer le loader
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="loader"></span> Traitement en cours...';
            this.disabled = true;
            
            // Simuler un délai de traitement
            setTimeout(() => {
                // Passer à l'étape 4
                goToStep(4);
                
                // Réactiver le bouton (pour le cas où on retournerait en arrière)
                this.innerHTML = originalText;
                this.disabled = false;
                
                // Montrer un message de succès
                showAlert('Votre commande a été enregistrée avec succès !', 'success');
                
                // Envoyer les données au serveur (simulation)
                submitOrderData();
            }, 2000);
        });
    }
    
    // Soumission des données de commande (simulation)
    function submitOrderData() {
        const formData = new FormData(document.getElementById('order-form'));
        
        // Simulation d'envoi AJAX
        fetch('commande.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Commande enregistrée:', data);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
    }
    
    // Formatage automatique des champs
    const phoneField = document.getElementById('telephone');
    if (phoneField) {
        phoneField.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            
            if (value.length > 2) {
                value = value.replace(/^(\d{2})/, '$1 ');
            }
            if (value.length > 6) {
                value = value.replace(/^(\d{2}) (\d{2})/, '$1 $2 ');
            }
            if (value.length > 9) {
                value = value.replace(/^(\d{2}) (\d{2}) (\d{2})/, '$1 $2 $3 ');
            }
            
            this.value = value;
        });
    }
    
    const cardNumberField = document.getElementById('card-number');
    if (cardNumberField) {
        cardNumberField.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            value = value.replace(/(.{4})/g, '$1 ').trim();
            this.value = value.substring(0, 19);
        });
    }
    
    const cardExpiryField = document.getElementById('card-expiry');
    if (cardExpiryField) {
        cardExpiryField.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            this.value = value.substring(0, 5);
        });
    }
    
    const cardCvcField = document.getElementById('card-cvc');
    if (cardCvcField) {
        cardCvcField.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 3);
        });
    }
    
    // Initialiser la barre de progression
    updateProgress();
    
    // Sélectionner automatiquement le premier service si aucun n'est sélectionné
    if (!selectedService && serviceOptions.length > 0) {
        serviceOptions[0].click();
    }
    
    // Initialiser les animations
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true
        });
    }
});