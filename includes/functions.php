<?php
// includes/functions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =====================================================
   DATABASE
===================================================== */

function getDBConnection() {
    static $db = null;

    if ($db === null) {
        try {
            $db = new PDO(
                "mysql:host=sql202.infinityfree.com;dbname=if0_40950975_sds;charset=utf8mb4",
                "if0_40950975",
                "Shalom20262001",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    }

    return $db;
}

/* =====================================================
   AUTH / ADMIN
===================================================== */

function isAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

/* =====================================================
   UTILITAIRES
===================================================== */

function redirect($url, $statusCode = 303) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

function escape($data) {
    if (is_array($data)) {
        return array_map('escape', $data);
    }
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

function formatPrice($price, $currency = 'FCFA') {
    return number_format((float)$price, 0, ',', ' ') . ' ' . $currency;
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidPhone($phone) {
    return preg_match('/^\+?[0-9]{8,15}$/', $phone);
}

function generateOrderNumber() {
    return 'LFP-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function getInitials($name) {
    if (empty($name)) return '??';

    $initials = '';
    foreach (explode(' ', $name) as $word) {
        if ($word !== '') {
            $initials .= strtoupper($word[0]);
        }
    }
    return substr($initials, 0, 2);
}

/* =====================================================
   SERVICES
===================================================== */

function getServicesByCategory($category = null) {
    $db = getDBConnection();

    $sql = "SELECT * FROM services";
    $params = [];

    if ($category) {
        $sql .= " WHERE categorie = :categorie";
        $params['categorie'] = $category;
    }

    $sql .= " ORDER BY popular DESC, id ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/* =====================================================
   COMMANDES
===================================================== */

function getCommandeById($id) {
    $db = getDBConnection();

    $stmt = $db->prepare("
        SELECT c.*, s.categorie
        FROM commandes c
        LEFT JOIN services s ON c.service_id = s.id
        WHERE c.id = :id
    ");

    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

function updateCommandeStatus($id, $status) {
    $db = getDBConnection();

    $stmt = $db->prepare("
        UPDATE commandes
        SET statut = :statut
        WHERE id = :id
    ");

    return $stmt->execute([
        'statut' => $status,
        'id' => $id
    ]);
}

/* =====================================================
   CSRF
===================================================== */

function generateCsrfToken() {
    $_SESSION['csrf_tokens'] ??= [];

    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$token] = time();

    foreach ($_SESSION['csrf_tokens'] as $t => $time) {
        if (time() - $time > 3600) {
            unset($_SESSION['csrf_tokens'][$t]);
        }
    }

    return $token;
}

function validateCsrfToken($token) {
    if (empty($_SESSION['csrf_tokens'][$token])) {
        return false;
    }

    if (time() - $_SESSION['csrf_tokens'][$token] > 3600) {
        unset($_SESSION['csrf_tokens'][$token]);
        return false;
    }

    unset($_SESSION['csrf_tokens'][$token]);
    return true;
}

/* =====================================================
   FORMULAIRES
===================================================== */

function validateForm($data, $rules) {
    $errors = [];

    foreach ($rules as $field => $rule) {
        $value = trim($data[$field] ?? '');

        if (!empty($rule['required']) && $value === '') {
            $errors[$field] = "Le champ $field est requis";
            continue;
        }

        if (!empty($rule['email']) && $value && !isValidEmail($value)) {
            $errors[$field] = "Adresse email invalide";
        }

        if (!empty($rule['phone']) && $value && !isValidPhone($value)) {
            $errors[$field] = "Numéro de téléphone invalide";
        }

        if (!empty($rule['min_length']) && strlen($value) < $rule['min_length']) {
            $errors[$field] = "Minimum {$rule['min_length']} caractères";
        }

        if (!empty($rule['max_length']) && strlen($value) > $rule['max_length']) {
            $errors[$field] = "Maximum {$rule['max_length']} caractères";
        }
    }

    return $errors;
}

/* =====================================================
   PAGINATION
===================================================== */

function paginate($totalItems, $perPage, $currentPage, $urlPattern) {
    $totalPages = max(1, ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));

    $pages = [];
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $start + 4);

    for ($i = $start; $i <= $end; $i++) {
        $pages[] = [
            'number' => $i,
            'url' => str_replace('{page}', $i, $urlPattern),
            'is_current' => $i === $currentPage
        ];
    }

    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'pages' => $pages
    ];
}

/* =====================================================
   UPLOADS & LOGS
===================================================== */

function handleFileUpload($file, $allowedTypes = ['image/jpeg','image/png','image/gif','application/pdf'], $maxSize = 5242880) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'errors' => ['Erreur upload']];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'errors' => ['Fichier trop volumineux']];
    }

    $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']);
    if (!in_array($mime, $allowedTypes)) {
        return ['success' => false, 'errors' => ['Type non autorisé']];
    }

    $name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $file['name']);
    $path = __DIR__ . '/../uploads/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return ['success' => false, 'errors' => ['Erreur déplacement']];
    }

    return ['success' => true, 'filename' => $name, 'path' => $path];
}

function logError($message, $context = []) {
    error_log(date('Y-m-d H:i:s') . " - $message " . json_encode($context) . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
}


// Ajoutez ces fonctions à la fin du fichier functions.php

/**
 * Formater les bytes en unités lisibles
 */
function formatBytes($bytes, $precision = 2) {
    if ($bytes == 0) return '0 Bytes';
    
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    
    return number_format($bytes / pow($k, $i), $precision) . ' ' . $sizes[$i];
}

/**
 * Logger une action système
 */
function logSystemAction($type, $message, $user_id = null) {
    $db = getDBConnection();
    
    $sql = "INSERT INTO system_logs (type, message, user_id, user_ip, user_agent) 
            VALUES (:type, :message, :user_id, :user_ip, :user_agent)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':type' => $type,
        ':message' => $message,
        ':user_id' => $user_id,
        ':user_ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu'
    ]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Vérifier si le mode maintenance est activé
 */
function isMaintenanceMode() {
    $maintenance_file = __DIR__ . '/../.maintenance';
    return file_exists($maintenance_file);
}

/**
 * Obtenir les informations système
 */
function getSystemInfo() {
    $info = [];
    
    // Informations PHP
    $info['php_version'] = phpversion();
    $info['php_os'] = PHP_OS;
    $info['php_sapi'] = PHP_SAPI;
    
    // Extensions
    $info['extensions'] = get_loaded_extensions();
    
    // Configuration PHP
    $info['memory_limit'] = ini_get('memory_limit');
    $info['max_execution_time'] = ini_get('max_execution_time');
    $info['upload_max_filesize'] = ini_get('upload_max_filesize');
    $info['post_max_size'] = ini_get('post_max_size');
    
    // Informations serveur
    $info['server_software'] = $_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu';
    $info['server_name'] = $_SERVER['SERVER_NAME'] ?? 'Inconnu';
    
    // Informations base de données
    try {
        $db = getDBConnection();
        $stmt = $db->query("SELECT VERSION() as version");
        $info['mysql_version'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        $info['mysql_version'] = 'Erreur de connexion';
    }
    
    // Utilisation mémoire
    $info['memory_usage'] = memory_get_usage(true);
    $info['memory_peak'] = memory_get_peak_usage(true);
    
    // Espace disque
    $info['disk_free_space'] = disk_free_space(__DIR__);
    $info['disk_total_space'] = disk_total_space(__DIR__);
    
    return $info;
}