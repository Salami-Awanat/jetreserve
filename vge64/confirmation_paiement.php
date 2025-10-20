<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: connexion.php');
    exit;
}

if (!isset($_GET['id_reservation'])) {
    header('Location: mes_reservations.php');
    exit;
}

$id_reservation = $_GET['id_reservation'];
$id_user = $_SESSION['id_user'];

// Récupérer les détails de la réservation confirmée
try {
    $stmt = $pdo->prepare("
        SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, v.numero_vol,
               c.nom_compagnie, p.mode_paiement, p.date_paiement
        FROM reservations r
        JOIN vols v ON r.id_vol = v.id_vol
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie
        JOIN paiements p ON r.id_reservation = p.id_reservation
        WHERE r.id_reservation = ? AND r.id_user = ? AND r.statut = 'confirmé'
    ");
    $stmt->execute([$id_reservation, $id_user]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        header('Location: mes_reservations.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: mes_reservations.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Paiement - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style1.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        .confirmation-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 40px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <!-- Header (identique) -->
    <header>
        <div class="container">
            <div class="header-top">
                <a href="../index.php" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-2"></i>
                            <?php echo $_SESSION['prenom'] ?? 'Client'; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index1.php"><i class="fas fa-home me-2"></i>Accueil client</a></li>
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../index.php">
                                <i class="fas fa-power-off me-2"></i>Déconnexion
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container mt-4">
        <div class="confirmation-container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1 class="text-success mb-3">Paiement Confirmé !</h1>
            <p class="lead mb-4">Votre réservation a été confirmée avec succès.</p>

            <div class="confirmation-card">
                <h4 class="mb-4">Détails de votre réservation</h4>
                
                <div class="row text-start">
    <div class="col-12 mb-2">
        <strong>Référence de réservation:</strong> #<?php echo $reservation['id_reservation']; ?>
    </div>
    <div class="col-12 mb-2">
        <strong>Vol:</strong> <?php echo htmlspecialchars($reservation['depart']); ?> → 
        <?php echo htmlspecialchars($reservation['arrivee']); ?>
    </div>
    <div class="col-12 mb-2">
        <strong>Date de départ:</strong> <?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?>
    </div>
    <div class="col-12 mb-2">
        <strong>Compagnie:</strong> <?php echo htmlspecialchars($reservation['nom_compagnie']); ?>
    </div>
    <div class="col-12 mb-2">
        <strong>Mode de paiement:</strong> <?php echo ucfirst($reservation['mode_paiement']); ?>
    </div>
    <div class="col-12 mb-2">
        <strong>Date du paiement:</strong> <?php echo date('d/m/Y H:i', strtotime($reservation['date_paiement'])); ?>
    </div>
    <div class="col-12 mb-3">
        <strong>Montant payé:</strong> <span class="text-primary fw-bold"><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?> €</span>
    </div>
</div>

                <div class="d-grid gap-2 mt-4">
                    <a href="mes_reservations.php" class="btn btn-primary">
                        <i class="fas fa-list me-2"></i>Voir mes réservations
                    </a>
                    <a href="../index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i>Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>