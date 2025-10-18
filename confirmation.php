<?php
session_start();
require_once 'includes/db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: vge64/connexion.php');
    exit;
}

$reservation_id = $_GET['reservation_id'] ?? null;
$payment_id = $_GET['payment_id'] ?? null;

if (!$reservation_id) {
    header('Location: index.php');
    exit;
}

// Récupérer les détails de la réservation confirmée
try {
    $sql = "SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, 
                   v.numero_vol, c.nom_compagnie, c.code_compagnie, a.modele as avion_modele,
                   p.montant as montant_paye, p.mode_paiement, p.date_paiement
            FROM reservations r
            JOIN vols v ON r.id_vol = v.id_vol
            JOIN compagnies c ON v.id_compagnie = c.id_compagnie
            JOIN avions a ON v.id_avion = a.id_avion
            LEFT JOIN paiements p ON p.id_reservation = r.id_reservation AND p.statut = 'réussi'
            WHERE r.id_reservation = ? AND r.id_user = ? AND r.statut = 'confirmé'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reservation_id, $_SESSION['user_id']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        header('Location: index.php?error=reservation_non_trouvee');
        exit;
    }
    
    // Récupérer les sièges
    $sql_sieges = "SELECT sa.rang, sa.position, sa.classe
                   FROM reservation_sieges rs 
                   JOIN sieges_avion sa ON rs.id_siege = sa.id_siege 
                   WHERE rs.id_reservation = ?";
    $stmt_sieges = $pdo->prepare($sql_sieges);
    $stmt_sieges->execute([$reservation_id]);
    $sieges = $stmt_sieges->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Erreur récupération confirmation: " . $e->getMessage());
    header('Location: index.php?error=erreur_base_donnees');
    exit;
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
            margin-bottom: 30px;
        }

        .confirmation-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            text-align: center;
            margin-bottom: 30px;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
        }

        .details-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 5px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-outline {
            border: 2px solid #2563eb;
            color: #2563eb;
            background: transparent;
        }

        .btn-outline:hover {
            background: #2563eb;
            color: white;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .info-item {
            text-align: left;
        }

        .info-label {
            font-weight: 600;
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .info-value {
            color: #1e293b;
            font-size: 1.1rem;
        }

        .seat-badge {
            background: #2563eb;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin: 5px;
        }

        .boarding-pass {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }

        .boarding-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
        }

        .boarding-qr {
            background: white;
            padding: 10px;
            border-radius: 8px;
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="index.php" style="font-size: 1.8rem; font-weight: 700; text-decoration: none; color: #1e293b;">
                    Jet<span style="color: #2563eb;">Reserve</span>
                </a>
                <div>
                    <span style="color: #64748b;">Bonjour, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?></span>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Carte de confirmation -->
        <div class="confirmation-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h1 style="color: #10b981; margin-bottom: 10px;">Paiement Confirmé !</h1>
            <p style="color: #64748b; margin-bottom: 20px;">
                Votre réservation a été confirmée. Un email de confirmation a été envoyé à votre adresse.
            </p>
            <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <strong>Numéro de réservation :</strong> 
                <span style="color: #2563eb; font-size: 1.2rem;">JTR<?php echo str_pad($reservation_id, 6, '0', STR_PAD_LEFT); ?></span>
            </div>
        </div>

        <!-- Détails du vol -->
        <div class="details-card">
            <h2 style="margin-bottom: 20px; color: #1e293b;">Détails de votre vol</h2>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Compagnie aérienne</div>
                    <div class="info-value"><?php echo htmlspecialchars($reservation['nom_compagnie']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Numéro de vol</div>
                    <div class="info-value"><?php echo htmlspecialchars($reservation['code_compagnie'] . $reservation['numero_vol']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Itinéraire</div>
                    <div class="info-value"><?php echo htmlspecialchars($reservation['depart']); ?> → <?php echo htmlspecialchars($reservation['arrivee']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date de départ</div>
                    <div class="info-value"><?php echo date('d/m/Y à H:i', strtotime($reservation['date_depart'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Passagers</div>
                    <div class="info-value"><?php echo $reservation['nombre_passagers']; ?> personne(s)</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Montant payé</div>
                    <div class="info-value"><?php echo number_format($reservation['montant_paye'] ?? $reservation['prix_total'] + 34, 2, ',', ' '); ?> €</div>
                </div>
            </div>

            <!-- Sièges assignés -->
            <?php if (!empty($sieges)): ?>
            <div style="margin-top: 20px;">
                <div class="info-label">Sièges assignés</div>
                <div style="margin-top: 10px;">
                    <?php foreach ($sieges as $siege): ?>
                        <div class="seat-badge">
                            <i class="fas fa-chair"></i> 
                            <?php echo $siege['position'] . $siege['rang']; ?> 
                            (<?php echo htmlspecialchars($siege['classe']); ?>)
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Passe d'embarquement simulée -->
        <div class="boarding-pass">
            <div class="boarding-header">
                <div>
                    <h3 style="margin: 0; color: white;">PASSE D'EMBARQUEMENT</h3>
                    <p style="margin: 5px 0 0 0; opacity: 0.9;">JetReserve</p>
                </div>
                <div class="boarding-qr">
                    QR CODE<br>JTR<?php echo str_pad($reservation_id, 6, '0', STR_PAD_LEFT); ?>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div>
                    <div style="opacity: 0.9; font-size: 0.9rem;">Passager</div>
                    <div style="font-weight: bold;"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?></div>
                </div>
                <div>
                    <div style="opacity: 0.9; font-size: 0.9rem;">Vol</div>
                    <div style="font-weight: bold;"><?php echo htmlspecialchars($reservation['code_compagnie'] . $reservation['numero_vol']); ?></div>
                </div>
                <div>
                    <div style="opacity: 0.9; font-size: 0.9rem;">Siège</div>
                    <div style="font-weight: bold;"><?php echo !empty($sieges) ? $sieges[0]['position'] . $sieges[0]['rang'] : 'À assigner'; ?></div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div>
                    <div style="opacity: 0.9; font-size: 0.9rem;">Départ</div>
                    <div style="font-weight: bold;"><?php echo htmlspecialchars($reservation['depart']); ?></div>
                    <div style="font-size: 0.9rem;"><?php echo date('H:i', strtotime($reservation['date_depart'])); ?></div>
                </div>
                <div>
                    <div style="opacity: 0.9; font-size: 0.9rem;">Arrivée</div>
                    <div style="font-weight: bold;"><?php echo htmlspecialchars($reservation['arrivee']); ?></div>
                    <div style="font-size: 0.9rem;"><?php echo date('H:i', strtotime($reservation['date_arrivee'])); ?></div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimer la confirmation
            </button>
        </div>
    </div>

    <script>
        // Auto-redirection après 30 secondes d'inactivité
        let inactivityTime = function () {
            let time;
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;

            function redirect() {
                window.location.href = 'index.php';
            }

            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(redirect, 30000); // 30 secondes
            }
        };
        inactivityTime();
    </script>
</body>
</html>