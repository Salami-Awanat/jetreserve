<?php
require_once 'includes/db.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: vge64/connexion.php');
    exit;
}

// Récupérer l'ID de la réservation
$id_reservation = isset($_GET['id_reservation']) ? intval($_GET['id_reservation']) : 0;

if ($id_reservation <= 0) {
    header('Location: index.php');
    exit;
}

// Récupérer les détails de la réservation
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
        WHERE r.id_reservation = ? AND r.id_user = ?
        GROUP BY r.id_reservation
    ");
    $stmt->execute([$id_reservation, $_SESSION['id_user']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération de la réservation: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Utilisez le même CSS que la page reservation.php */
        :root {
            --primary: #2c3e50;
            --secondary: #7f8c8d;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #3498db;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --white: #ffffff;
            --gray: #7f8c8d;
            --light-gray: #f9fafb;
            --border-color: #dee2e6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-gray);
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Header Styles identique */
        header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }
        
        .logo span {
            color: var(--danger);
        }
        
        .confirmation-container {
            max-width: 800px;
            margin: 2rem auto;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            text-align: center;
            padding: 3rem;
        }
        
        .confirmation-icon {
            font-size: 4rem;
            color: var(--success);
            margin-bottom: 1rem;
        }
        
        .confirmation-title {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .reservation-details {
            background: var(--light);
            padding: 2rem;
            border-radius: 8px;
            margin: 2rem 0;
            text-align: left;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <a href="vge64/index2.php" class="btn btn-outline">Mon compte</a>
                    <a href="vge64/deconnexion.php" class="btn btn-primary">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="confirmation-container">
            <div class="confirmation-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="confirmation-title">Réservation confirmée !</h1>
            <p>Votre réservation a été enregistrée avec succès. Voici le récapitulatif :</p>
            
            <div class="reservation-details">
                <div class="detail-item">
                    <strong>Numéro de réservation:</strong>
                    <span>#JR<?php echo str_pad($reservation['id_reservation'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Vol:</strong>
                    <span><?php echo htmlspecialchars($reservation['depart']); ?> → <?php echo htmlspecialchars($reservation['arrivee']); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Date de départ:</strong>
                    <span><?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Compagnie:</strong>
                    <span><?php echo htmlspecialchars($reservation['nom_compagnie']); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Sièges:</strong>
                    <span><?php echo htmlspecialchars($reservation['sieges']); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Passagers:</strong>
                    <span><?php echo htmlspecialchars($reservation['nombre_passagers']); ?></span>
                </div>
                <div class="detail-item" style="border-bottom: none; font-size: 1.2rem;">
                    <strong>Total payé:</strong>
                    <span style="color: var(--primary); font-weight: 600;">
                        <?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€
                    </span>
                </div>
            </div>
            
            <p>Un email de confirmation a été envoyé à votre adresse email.</p>
            
            <div class="action-buttons">
                <a href="vge64/mes_reservations.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> Voir mes réservations
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</body>
</html>