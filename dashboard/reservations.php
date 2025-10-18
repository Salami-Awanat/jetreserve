<?php
session_start();
require_once '../includes/db.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../vge64/connexion.php');
    exit;
}

// Traitement des actions
$message = '';
$message_type = '';

// Changer le statut d'une réservation
if (isset($_POST['change_status'])) {
    $reservation_id = intval($_POST['reservation_id']);
    $new_status = $_POST['statut'];
    
    try {
        $stmt = $pdo->prepare("UPDATE reservations SET statut = ? WHERE id_reservation = ?");
        $stmt->execute([$new_status, $reservation_id]);
        
        $message = "Statut de la réservation modifié avec succès.";
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = "Erreur lors du changement de statut: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Supprimer une réservation
if (isset($_GET['delete'])) {
    $reservation_id = intval($_GET['delete']);
    
    try {
        // Supprimer d'abord les sièges réservés
        $stmt = $pdo->prepare("DELETE FROM reservation_sieges WHERE id_reservation = ?");
        $stmt->execute([$reservation_id]);
        
        // Puis supprimer la réservation
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id_reservation = ?");
        $stmt->execute([$reservation_id]);
        
        $message = "Réservation supprimée avec succès.";
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Récupérer toutes les réservations avec les informations détaillées
$stmt = $pdo->query("
    SELECT r.*, 
           u.nom as user_nom, u.prenom as user_prenom, u.email as user_email,
           v.numero_vol, v.depart, v.arrivee, v.date_depart, v.date_arrivee, v.prix as vol_prix,
           c.nom_compagnie, c.code_compagnie,
           (SELECT COUNT(*) FROM reservation_sieges rs WHERE rs.id_reservation = r.id_reservation) as sieges_count
    FROM reservations r 
    JOIN users u ON r.id_user = u.id_user 
    JOIN vols v ON r.id_vol = v.id_vol 
    JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
    ORDER BY r.date_reservation DESC
");
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="content">
    <!-- Message de notification -->
    <?php if ($message): ?>
    <div class="notification notification-<?php echo $message_type; ?>" style="margin-bottom: 20px;">
        <div class="notification-content">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check' : 'exclamation'; ?>-circle"></i>
            <span><?php echo $message; ?></span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>

    <!-- Statistiques des réservations -->
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value">
                <?php 
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations WHERE statut = 'confirmé'");
                echo $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                ?>
            </div>
            <div class="stat-label">Confirmées</div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value">
                <?php 
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations WHERE statut = 'en attente'");
                echo $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                ?>
            </div>
            <div class="stat-label">En attente</div>
        </div>
        
        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-value">
                <?php 
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations WHERE statut = 'annulé'");
                echo $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                ?>
            </div>
            <div class="stat-label">Annulées</div>
        </div>
        
        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-value">
                <?php 
                $stmt = $pdo->query("SELECT SUM(prix_total) as total FROM reservations WHERE statut = 'confirmé'");
                $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                echo number_format($total, 0, ',', ' ');
                ?>€
            </div>
            <div class="stat-label">Chiffre d'affaires</div>
        </div>
    </div>

    <!-- Liste des réservations -->
    <div class="card">
        <div class="card-header">
            <h2>Gestion des Réservations</h2>
            <div class="header-actions">
                <input type="text" id="searchReservations" placeholder="Rechercher une réservation..." class="form-control" style="width: 300px;">
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table" id="reservationsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Vol</th>
                            <th>Détails</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Date Réservation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td>#<?php echo $reservation['id_reservation']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($reservation['user_prenom'] . ' ' . $reservation['user_nom']); ?></strong>
                                <div style="color: #64748b; font-size: 0.8rem;"><?php echo htmlspecialchars($reservation['user_email']); ?></div>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($reservation['code_compagnie'] . $reservation['numero_vol']); ?></strong>
                                <div style="color: #64748b; font-size: 0.8rem;"><?php echo htmlspecialchars($reservation['depart']); ?> → <?php echo htmlspecialchars($reservation['arrivee']); ?></div>
                                <div style="font-size: 0.8rem; color: #94a3b8;">
                                    <?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.9rem;">
                                    <div><strong>Passagers:</strong> <?php echo $reservation['nombre_passagers']; ?></div>
                                    <div><strong>Sièges:</strong> <?php echo $reservation['sieges_count']; ?></div>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€</strong>
                            </td>
                            <td>
                                <form method="POST" class="status-form" style="display: inline-block;">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id_reservation']; ?>">
                                    <select name="statut" class="form-control form-control-sm" onchange="this.form.submit()" style="width: 120px;">
                                        <option value="en attente" <?php echo $reservation['statut'] === 'en attente' ? 'selected' : ''; ?>>En attente</option>
                                        <option value="confirmé" <?php echo $reservation['statut'] === 'confirmé' ? 'selected' : ''; ?>>Confirmé</option>
                                        <option value="annulé" <?php echo $reservation['statut'] === 'annulé' ? 'selected' : ''; ?>>Annulé</option>
                                    </select>
                                    <button type="submit" name="change_status" style="display: none;"></button>
                                </form>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($reservation['date_reservation'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-primary btn-sm" title="Voir détails" onclick="showReservationDetails(<?php echo $reservation['id_reservation']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="reservations.php?delete=<?php echo $reservation['id_reservation']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       title="Supprimer"
                                       onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer cette réservation ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($reservations)): ?>
            <div style="text-align: center; padding: 40px; color: #64748b;">
                <i class="fas fa-ticket-alt" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                <h3>Aucune réservation trouvée</h3>
                <p>Les réservations apparaîtront ici.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal pour les détails de réservation -->
<div id="reservationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Détails de la Réservation</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="reservationDetails">
            <!-- Les détails seront chargés ici -->
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #1e293b;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #64748b;
    cursor: pointer;
    padding: 5px;
}

.modal-body {
    padding: 25px;
}

.status-form {
    min-width: 120px;
}

.form-control-sm {
    padding: 6px 8px;
    font-size: 0.8rem;
}
</style>

<script>
setPageTitle('Gestion des Réservations');

// Confirmation pour la suppression
function confirmAction(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir effectuer cette action ?');
}

// Recherche en temps réel
document.getElementById('searchReservations').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#reservationsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// Afficher les détails d'une réservation
function showReservationDetails(reservationId) {
    // Pour l'instant, on va afficher une alerte simple
    // Vous pourrez implémenter l'appel AJAX plus tard
    alert('Détails de la réservation #' + reservationId + '\n\nFonctionnalité à implémenter avec AJAX');
    
    // Exemple d'implémentation future :
    /*
    fetch(`api/get_reservation_details.php?id=${reservationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('reservationDetails').innerHTML = data.html;
                document.getElementById('reservationModal').style.display = 'flex';
            } else {
                alert('Erreur lors du chargement des détails');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du chargement des détails');
        });
    */
}

// Fermer la modal
function closeModal() {
    document.getElementById('reservationModal').style.display = 'none';
}

// Fermer la modal en cliquant à l'extérieur
window.onclick = function(event) {
    const modal = document.getElementById('reservationModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>

</body>
</html>