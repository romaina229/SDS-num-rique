<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$article = null;

if($article_id > 0) {
    $query = "SELECT * FROM blog_articles WHERE id = :id AND (statut = 'published' OR :is_admin = 1)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $article_id);
    $is_admin = isset($_SESSION['admin']) && $_SESSION['admin'] ? 1 : 0;
    $stmt->bindParam(':is_admin', $is_admin);
    $stmt->execute();
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Incrémenter le compteur de vues (à implémenter)
    if($article) {
         $query = "UPDATE blog_articles SET vues = vues + 1 WHERE id = :id";
         $stmt = $db->prepare($query);
         $stmt->bindParam(':id', $article_id);
         $stmt->execute();
    }
}

if(!$article) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit();
}
?>
<!-- Structure HTML similaire à blog.php mais pour un article individuel -->