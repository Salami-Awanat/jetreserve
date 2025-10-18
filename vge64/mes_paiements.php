<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: connexion.php');
    exit;
}

// Récupérer tous les paiements de l'utilisateur
try {
    $stmt = $pdo->prepare("
        SELECT p.*, r.id_reservation, v.depart, v.arrivee, v.date_depart
        FROM paiements p
        JOIN reservations r ON p.id_reservation = r.id_reservation
        JOIN vols v ON r.id_vol = v.id_vol
        WHERE r.id_user = ?
        ORDER BY p.date_paiement DESC
    ");
    $stmt->execute([$_SESSION['id_user']]);
    $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $paiements = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Paiements - JetReserve</title>
    <!-- Inclure les mêmes styles que index2.php -->
</head>
<body>
    <!-- Même structure que index2.php -->
    <div class="container">
        <h1>Mes Paiements</h1>
        
        <?php if (empty($paiements)): ?>
            <div class="alert alert-info">
                Vous n'avez effectué aucun paiement.
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vol</th>
                        <th>Montant</th>
                        <th>Méthode</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paiements as $paiement): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($paiement['date_paiement'])); ?></td>
                        <td><?php echo htmlspecialchars($paiement['depart']); ?> → <?php echo htmlspecialchars($paiement['arrivee']); ?></td>
                        <td><?php echo number_format($paiement['montant'], 2, ',', ' '); ?>€</td>
                        <td><?php echo ucfirst($paiement['mode_paiement']); ?></td>
                        <td><span class="status status-<?php echo $paiement['statut']; ?>"><?php echo ucfirst($paiement['statut']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>