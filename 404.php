<?php
// 404.php
$page_title = "Page non trouvée - Shalom DigitalPro";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #333;
        }
        
        .error-container {
            background-color: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }
        
        .error-container:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--secondary), var(--accent));
        }
        
        .error-icon {
            font-size: 100px;
            color: var(--accent);
            margin-bottom: 30px;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-20px);}
            60% {transform: translateY(-10px);}
        }
        
        h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            color: rgba(52, 152, 219, 0.1);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
        }
        
        .message {
            font-size: 1.2rem;
            color: #555;
            line-height: 1.6;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }
        
        .actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--secondary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
        }
        
        .btn-secondary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #1a252f;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(44, 62, 80, 0.3);
        }
        
        .btn-accent {
            background-color: var(--accent);
            color: white;
        }
        
        .btn-accent:hover {
            background-color: #c0392b;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(231, 76, 60, 0.3);
        }
        
        .search-box {
            margin: 30px 0;
            position: relative;
            z-index: 1;
        }
        
        .search-box input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #ddd;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .search-box button {
            position: absolute;
            right: 5px;
            top: 5px;
            background-color: var(--secondary);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .search-box button:hover {
            background-color: #2980b9;
        }
        
        @media (max-width: 768px) {
            .error-container {
                padding: 40px 20px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .error-code {
                font-size: 6rem;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .error-icon {
                font-size: 80px;
            }
            
            .error-code {
                font-size: 5rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h1>Page non trouvée</h1>
        
        <div class="message">
            Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
            Elle a peut-être été supprimée, son nom a changé ou elle est temporairement indisponible.
        </div>
        
        <div class="search-box">
            <form action="index.php" method="get">
                <input type="text" name="search" placeholder="Que recherchez-vous ?">
                <button type="submit">Rechercher</button>
            </form>
        </div>
        
        <div class="actions">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
            <a href="index.php#services" class="btn btn-secondary">
                <i class="fas fa-cogs"></i> Nos services
            </a>
            <a href="index.php#contact" class="btn btn-accent">
                <i class="fas fa-envelope"></i> Nous contacter
            </a>
        </div>
    </div>
    
    <script>
        // Mettre le focus sur le champ de recherche
        document.querySelector('input[name="search"]').focus();
        
        // Animation supplémentaire
        const errorIcon = document.querySelector('.error-icon');
        setInterval(() => {
            errorIcon.style.transform = 'rotate(5deg)';
            setTimeout(() => {
                errorIcon.style.transform = 'rotate(-5deg)';
            }, 100);
        }, 2000);
    </script>
</body>
</html>