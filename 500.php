<?php
// 500.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur serveur - Shalom Digital Solutions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            margin: 0;
        }
        
        .error-container {
            background-color: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .error-icon {
            font-size: 100px;
            color: #e74c3c;
            margin-bottom: 30px;
            animation: shake 0.5s infinite alternate;
        }
        
        @keyframes shake {
            from { transform: translateX(-5px); }
            to { transform: translateX(5px); }
        }
        
        h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .message {
            font-size: 1.2rem;
            color: #555;
            line-height: 1.6;
            margin-bottom: 40px;
        }
        
        .technical-info {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
            font-family: monospace;
            font-size: 0.9rem;
            color: #666;
            border-left: 5px solid #e74c3c;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--secondary);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            margin: 10px;
        }
        
        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .btn-home {
            background-color: var(--primary);
        }
        
        .btn-contact {
            background-color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-server"></i>
        </div>
        
        <h1>500 - Erreur serveur</h1>
        
        <div class="message">
            Une erreur interne du serveur s'est produite. 
            Notre équipe technique a été notifiée et travaille à résoudre le problème.
        </div>
        
        <div class="technical-info">
            <strong>Que faire :</strong><br>
            1. Actualisez la page après quelques minutes<br>
            2. Vérifiez votre connexion internet<br>
            3. Réessayez plus tard<br>
            4. Contactez-nous si le problème persiste
        </div>
        
        <div>
            <a href="index.php" class="btn btn-home">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
            <a href="index.php#contact" class="btn btn-contact">
                <i class="fas fa-phone"></i> Contact d'urgence
            </a>
        </div>
    </div>
</body>
</html>