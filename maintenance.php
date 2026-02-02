<?php
// maintenance.php
$maintenance_mode = true; // Mettre à false pour désactiver la maintenance
$reouverture_date = "15 janvier 2026";
$reouverture_heure = "09:00";
$contact_urgence = "+229 01 69 35 17 66";
$email_urgence = "liferopro@gmail.com";

if ($maintenance_mode) {
    http_response_code(503); // Service Temporairement Indisponible
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shalom Digital Solutions- Maintenance en cours</title>
    <style>
   /*le style se trouve dans l'assets/css/maintenance.css*/
   <?php include 'assets/css/maintenance.css'; ?>
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="maintenance-container">
        <div class="logo-maintenance">
            <i class="fas fa-code logo-icon"></i>
            <div class="logo-text">Shalom Digital <span>Solutions</span></div>
        </div>
        
        <div class="maintenance-icon">
            <i class="fas fa-tools"></i>
        </div>
        
        <h1>Maintenance en cours</h1>
        
        <div class="message">
            Notre site est actuellement en cours de maintenance pour améliorer votre expérience. 
            Nous travaillons dur pour revenir avec une version améliorée du site.
        </div>
        
        <div class="info-box">
            <div class="info-item">
                <i class="fas fa-calendar-alt info-icon"></i>
                <span>Réouverture prévue le : <strong><?php echo $reouverture_date; ?></strong></span>
            </div>
            <div class="info-item">
                <i class="fas fa-clock info-icon"></i>
                <span>À partir de : <strong><?php echo $reouverture_heure; ?></strong></span>
            </div>
            <div class="info-item">
                <i class="fas fa-hard-hat info-icon"></i>
                <span>Nous améliorons notre plateforme pour mieux vous servir</span>
            </div>
        </div>
        
        <div class="countdown">
            <div class="countdown-title">Temps restant avant réouverture</div>
            <div class="countdown-timer" id="countdown">
                <div class="countdown-item">
                    <span class="countdown-value" id="days">00</span>
                    <span class="countdown-label">Jours</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-value" id="hours">00</span>
                    <span class="countdown-label">Heures</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-value" id="minutes">00</span>
                    <span class="countdown-label">Minutes</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-value" id="seconds">00</span>
                    <span class="countdown-label">Secondes</span>
                </div>
            </div>
        </div>
        
        <div class="contact-info">
            <div class="contact-title">Contact d'urgence</div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <a href="tel:<?php echo $contact_urgence; ?>" class="contact-link"><?php echo $contact_urgence; ?></a>
            </div>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <a href="mailto:<?php echo $email_urgence; ?>" class="contact-link"><?php echo $email_urgence; ?></a>
            </div>
            <div class="contact-item">
                <i class="fas fa-info-circle"></i>
                <span>Uniquement pour les urgences importantes</span>
            </div>
        </div>
        
        <div class="progress-bar">
            <div class="progress"></div>
        </div>
    </div>
    
    <script>
        // Date et heure de réouverture
        const reouvertureDate = new Date("<?php echo $reouverture_date; ?> <?php echo $reouverture_heure; ?>").getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const timeLeft = reouvertureDate - now;
            
            if (timeLeft < 0) {
                document.getElementById("countdown").innerHTML = "<div style='font-size: 1.5rem;'>Le site est en cours de réouverture!</div>";
                return;
            }
            
            // Calcul des jours, heures, minutes, secondes
            const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
            const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
            
            // Mise à jour de l'affichage
            document.getElementById("days").textContent = days.toString().padStart(2, '0');
            document.getElementById("hours").textContent = hours.toString().padStart(2, '0');
            document.getElementById("minutes").textContent = minutes.toString().padStart(2, '0');
            document.getElementById("seconds").textContent = seconds.toString().padStart(2, '0');
        }
        
        // Mettre à jour le compte à rebours toutes les secondes
        setInterval(updateCountdown, 1000);
        updateCountdown(); // Appel initial
    </script>
</body>
</html>
<?php
    exit(); // Arrêter l'exécution du script
}
// Si la maintenance est désactivée, continuer vers le site normal
?>