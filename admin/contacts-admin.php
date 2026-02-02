<?php
// admin/contacts-admin.php
session_start();
if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Récupérer les messages
$query = "SELECT * FROM contacts ORDER BY date_creation DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="assets/images/Logosds.png">
    <title>Gestion des Contacts - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/contact-admin.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Messages de contact <span class="contact-count">(<?php echo count($contacts); ?>)</span></h1>
        </div>

        <div class="controls">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher un contact...">
            </div>
            
            <div class="filters">
                <div class="filter-group">
                    <label for="filterStatus"><i class="fas fa-filter"></i> Statut</label>
                    <select id="filterStatus">
                        <option value="all">Tous les statuts</option>
                        <option value="nouveau">Nouveau</option>
                        <option value="lu">Lu</option>
                        <option value="en_cours">En cours</option>
                        <option value="repondu">Répondu</option>
                        <option value="archive">Archivé</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filterDate"><i class="fas fa-calendar"></i> Date</label>
                    <select id="filterDate">
                        <option value="all">Toutes les dates</option>
                        <option value="today">Aujourd'hui</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                        <option value="older">Plus ancien</option>
                    </select>
                </div>
            </div>
        </div>

        <?php if(empty($contacts)): ?>
        <div class="empty-state">
            <i class="far fa-envelope-open"></i>
            <h2>Aucun message de contact</h2>
            <p>Vous n'avez reçu aucun message pour le moment.</p>
        </div>
        <?php else: ?>
        
        <div class="table-container">
            <table id="contactsTable">
                <thead>
                    <tr>
                        <th data-sort="reference">Référence <i class="fas fa-sort"></i></th>
                        <th data-sort="nom">Nom <i class="fas fa-sort"></i></th>
                        <th data-sort="email">Email <i class="fas fa-sort"></i></th>
                        <th data-sort="sujet">Sujet <i class="fas fa-sort"></i></th>
                        <th data-sort="date">Date <i class="fas fa-sort"></i></th>
                        <th data-sort="statut">Statut <i class="fas fa-sort"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($contacts as $contact): ?>
                    <tr class="contact-row" data-status="<?php echo $contact['statut']; ?>" data-date="<?php echo $contact['date_creation']; ?>">
                        <td class="reference"><?php echo $contact['reference']; ?></td>
                        <td class="nom"><?php echo htmlspecialchars($contact['nom']); ?></td>
                        <td class="email"><?php echo htmlspecialchars($contact['email']); ?></td>
                        <td class="sujet"><?php echo htmlspecialchars($contact['sujet']); ?></td>
                        <td class="date" data-date="<?php echo $contact['date_creation']; ?>">
                            <?php echo date('d/m/Y H:i', strtotime($contact['date_creation'])); ?>
                        </td>
                        <td class="statut">
                            <span class="badge badge-<?php echo $contact['statut']; ?>">
                                <?php echo $contact['statut']; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="contact-view.php?id=<?php echo $contact['id']; ?>" class="btn btn-view">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php endif; ?>

        <div class="info-message">
            <i class="fas fa-info-circle"></i>
            <p>Cliquez sur les en-têtes de colonne pour trier le tableau. Utilisez la barre de recherche pour filtrer les résultats.</p>
        </div>
    </div>

    <script>
        // Fonctionnalités JavaScript pour la recherche et le tri
        document.addEventListener('DOMContentLoaded', function() {
            // Recherche en temps réel
            const searchInput = document.getElementById('searchInput');
            const contactRows = document.querySelectorAll('.contact-row');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                contactRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
            
            // Filtrage par statut
            const filterStatus = document.getElementById('filterStatus');
            filterStatus.addEventListener('change', function() {
                const status = this.value;
                
                contactRows.forEach(row => {
                    if (status === 'all') {
                        row.style.display = '';
                    } else {
                        const rowStatus = row.getAttribute('data-status');
                        row.style.display = rowStatus === status ? '' : 'none';
                    }
                });
            });
            
            // Tri des colonnes
            const headers = document.querySelectorAll('th[data-sort]');
            headers.forEach(header => {
                header.addEventListener('click', function() {
                    const sortBy = this.getAttribute('data-sort');
                    sortTable(sortBy);
                });
            });
            
            function sortTable(sortBy) {
                const tbody = document.querySelector('tbody');
                const rows = Array.from(contactRows);
                
                rows.sort((a, b) => {
                    const aValue = a.querySelector(`.${sortBy}`).textContent;
                    const bValue = b.querySelector(`.${sortBy}`).textContent;
                    
                    // Pour les dates
                    if (sortBy === 'date') {
                        const aDate = new Date(a.querySelector('.date').getAttribute('data-date'));
                        const bDate = new Date(b.querySelector('.date').getAttribute('data-date'));
                        return aDate - bDate;
                    }
                    
                    // Pour les autres colonnes
                    return aValue.localeCompare(bValue);
                });
                
                // Inverser l'ordre si déjà trié
                if (tbody.getAttribute('data-sorted') === sortBy) {
                    rows.reverse();
                    tbody.removeAttribute('data-sorted');
                } else {
                    tbody.setAttribute('data-sorted', sortBy);
                }
                
                // Replacer les lignes triées
                rows.forEach(row => tbody.appendChild(row));
            }
        });
    </script>
</body>
</html>