<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: connexion.php');
    exit;
}

// Récupérer toutes les réservations de l'utilisateur
try {
    $stmt = $pdo->prepare("
        SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, 
               v.numero_vol, c.nom_compagnie, c.code_compagnie,
               GROUP_CONCAT(CONCAT(sa.position, sa.rang) SEPARATOR ', ') as sieges
        FROM reservations r
        JOIN vols v ON r.id_vol = v.id_vol
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie
        LEFT JOIN reservation_sieges rs ON r.id_reservation = rs.id_reservation
        LEFT JOIN sieges_avion sa ON rs.id_siege = sa.id_siege
        WHERE r.id_user = ?
        GROUP BY r.id_reservation
        ORDER BY r.date_reservation DESC
    ");
    $stmt->execute([$_SESSION['id_user']]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $reservations = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations - JetReserve</title>
    <!-- Inclure les mêmes styles que index2.php -->
</head>
<body>
    <!-- Même structure que index2.php -->
    <div class="container">
        <h1>Mes Réservations</h1>
        
        <?php if (empty($reservations)): ?>
            <div class="alert alert-info">
                Vous n'avez aucune réservation.
            </div>
        <?php else: ?>
            <?php foreach ($reservations as $reservation): ?>
            <div class="reservation-card">
                <div class="reservation-header">
                    <h3><?php echo htmlspecialchars($reservation['depart']); ?> → <?php echo htmlspecialchars($reservation['arrivee']); ?></h3>
                    <span class="status status-<?php echo $reservation['statut']; ?>">
                        <?php echo ucfirst($reservation['statut']); ?>
                    </span>
                </div>
                <div class="reservation-details">
                    <p><strong>Vol:</strong> <?php echo htmlspecialchars($reservation['code_compagnie'] . $reservation['numero_vol']); ?></p>
                    <p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?></p>
                    <p><strong>Passagers:</strong> <?php echo $reservation['nombre_passagers']; ?></p>
                    <p><strong>Sièges:</strong> <?php echo $reservation['sieges'] ?? 'Non assignés'; ?></p>
                    <p><strong>Prix total:</strong> <?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€</p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>