<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: vge64/connexion.php');
    exit;
}

// Vérifier l'ID de réservation
if (!isset($_GET['id_reservation'])) {
    header('Location: index.php');
    exit;
}

$id_reservation = intval($_GET['id_reservation']);

// Récupérer les détails complets de la réservationa
try {
    // Détails de base de la réservation
    $sql = "
        SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, v.prix as prix_vol,
               c.nom_compagnie, c.code_compagnie, v.numero_vol,
               a.modele as avion_modele,
               p.date_paiement, p.mode_paiement, p.statut as statut_paiement,
               u.prenom, u.nom, u.email, u.telephone
        FROM reservations r
        JOIN vols v ON r.id_vol = v.id_vol
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie
        JOIN avions a ON v.id_avion = a.id_avion
        LEFT JOIN paiements p ON r.id_reservation = p.id_reservation
        JOIN users u ON r.id_user = u.id_user
        WHERE r.id_reservation = ? AND r.id_user = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_reservation, $_SESSION['id_user']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        throw new Exception("Réservation non trouvée");
    }

    // Récupérer les sièges réservés
    $sql_sieges = "
        SELECT sa.position, sa.rang, sa.supplement_prix, sa.classe
        FROM reservation_sieges rs
        JOIN sieges_avion sa ON rs.id_siege = sa.id_siege
        WHERE rs.id_reservation = ?
        ORDER BY sa.rang, sa.position
    ";
    $stmt_sieges = $pdo->prepare($sql_sieges);
    $stmt_sieges->execute([$id_reservation]);
    $sieges = $stmt_sieges->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les options de bagage
    $sql_bagages = "
        SELECT ob.nom_option, ob.description, ob.poids_max, rb.quantite, rb.prix_applique
        FROM reservation_bagages rb
        JOIN options_bagage ob ON rb.id_option = ob.id_option
        WHERE rb.id_reservation = ?
    ";
    $stmt_bagages = $pdo->prepare($sql_bagages);
    $stmt_bagages->execute([$id_reservation]);
    $bagages = $stmt_bagages->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si l'email a été envoyé
    $sql_email = "
        SELECT statut, date_envoi 
        FROM emails 
        WHERE id_user = ? AND type = 'confirmation' 
        ORDER BY date_envoi DESC 
        LIMIT 1
    ";
    $stmt_email = $pdo->prepare($sql_email);
    $stmt_email->execute([$_SESSION['id_user']]);
    $email_status = $stmt_email->fetch(PDO::FETCH_ASSOC);

    // Calculer le détail du prix
    $prix_base_vol = $reservation['prix_vol'] * $reservation['nombre_passagers'];
    $supplements_sieges = 0;
    foreach ($sieges as $siege) {
        $supplements_sieges += $siege['supplement_prix'];
    }
    $supplements_bagages = 0;
    foreach ($bagages as $bagage) {
        $supplements_bagages += $bagage['prix_applique'] * $bagage['quantite'];
    }

    // Calculer la durée du vol
    $date_depart = new DateTime($reservation['date_depart']);
    $date_arrivee = new DateTime($reservation['date_arrivee']);
    $interval = $date_depart->diff($date_arrivee);
    $duree_vol = $interval->format('%hh %imin');

    // Déterminer la porte d'embarquement (aléatoire pour l'exemple)
    $portes = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    $porte_embarquement = $portes[array_rand($portes)];

} catch (Exception $e) {
    error_log("Erreur confirmation réservation: " . $e->getMessage());
    header('Location: index.php?error=reservation_not_found');
    exit;
}

// Générer un code QR factice
$qr_data = "JetReserve|REF:" . $id_reservation . "|" . 
           $reservation['depart'] . "-" . $reservation['arrivee'] . "|" .
           date('Y-m-d', strtotime($reservation['date_depart']));
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_data);
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
    :root {
        --primary: #1e2942;
        --primary-dark: #060f22;
        --secondary: #6c757d;
        --success: #28a745;
        --info: #17a2b8;
        --warning: #ffc107;
        --danger: #dc3545;
        --light: #f8f9fa;
        --dark: #343a40;
        --white: #ffffff;
        --gray: #6c757d;
        --border: #dee2e6;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
        --jetreserve-red: #dc3545;  /* Couleur rouge de JetReserve */
        --jetreserve-blue: #1e2942; /* Couleur bleue de JetReserve */
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        line-height: 1.6;
        color: var(--dark);
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Header - Style JetReserve */
    header {
        background: var(--white);
        box-shadow: var(--shadow);
        position: relative;
        z-index: 1000;
    }

    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
    }

    .logo {
        font-size: 2rem;
        font-weight: 700;
        color: var(--jetreserve-blue);
        text-decoration: none;
        position: relative;
    }

    .logo span {
        color: var(--jetreserve-red);
    }

    .auth-buttons {
        display: flex;
        gap: 1rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        cursor: pointer;
    }

    .btn-primary {
        background: var(--jetreserve-red);
        color: var(--white);
        border-color: var(--jetreserve-red);
    }

    .btn-primary:hover {
        background: #c82333;
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }

    .btn-outline {
        background: transparent;
        color: var(--jetreserve-blue);
        border-color: var(--jetreserve-blue);
    }

    .btn-outline:hover {
        background: var(--jetreserve-blue);
        color: var(--white);
        transform: translateY(-2px);
    }

    .btn-success {
        background: var(--success);
        color: var(--white);
        border-color: var(--success);
    }

    .btn-success:hover {
        background: #218838;
        transform: translateY(-2px);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    /* Confirmation Container */
    .confirmation-container {
        max-width: 1000px;
        margin: 2rem auto;
        background: var(--white);
        border-radius: 20px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        border: 2px solid var(--jetreserve-red);
    }

    /* Confirmation Header */
    .confirmation-header {
        background: linear-gradient(135deg, var(--jetreserve-blue) 0%, var(--primary-dark) 100%);
        color: var(--white);
        text-align: center;
        padding: 3rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .confirmation-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: var(--jetreserve-red);
        animation: bounce 1s ease-in-out;
    }

    .confirmation-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--white);
    }

    .confirmation-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        color: var(--white);
    }

    /* Confetti avec couleurs JetReserve */
    #confetti-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .confetti {
        position: absolute;
        width: 10px;
        height: 10px;
        opacity: 0;
        animation: confetti-fall 5s linear forwards;
    }

    @keyframes confetti-fall {
        0% {
            transform: translateY(-100px) rotate(0deg);
            opacity: 1;
        }
        100% {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-10px);
        }
        60% {
            transform: translateY(-5px);
        }
    }

    /* Confirmation Body */
    .confirmation-body {
        padding: 2rem;
    }

    /* Payment Success */
    .payment-success {
        background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
        border: 2px solid var(--success);
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        margin-bottom: 2rem;
    }

    /* Ticket Styles avec couleurs JetReserve */
    .ticket-container {
        background: var(--white);
        border-radius: 15px;
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 2rem;
        border: 2px solid var(--jetreserve-blue);
    }

    .ticket-header {
        background: linear-gradient(135deg, var(--jetreserve-blue) 0%, var(--jetreserve-red) 100%);
        color: var(--white);
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ticket-airline {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .airline-logo {
        width: 50px;
        height: 50px;
        background: var(--white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: var(--jetreserve-red);
        border: 2px solid var(--jetreserve-red);
    }

    .ticket-qr {
        text-align: center;
    }

    .qr-code {
        width: 120px;
        height: 120px;
        background: var(--white);
        border-radius: 10px;
        padding: 10px;
        border: 2px solid var(--jetreserve-blue);
    }

    .ticket-body {
        padding: 2rem;
    }

    .flight-info {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 2rem;
        align-items: center;
        margin-bottom: 2rem;
    }

    .departure, .arrival {
        text-align: center;
    }

    .airport-code {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--jetreserve-red);
        margin-bottom: 0.5rem;
    }

    .airport-name {
        color: var(--jetreserve-blue);
        font-size: 0.9rem;
        font-weight: 500;
    }

    .flight-duration {
        text-align: center;
        position: relative;
    }

    .duration-line {
        height: 2px;
        background: var(--jetreserve-blue);
        position: relative;
        margin: 1rem 0;
    }

    .duration-line::before {
        content: '✈';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: var(--white);
        padding: 0 1rem;
        color: var(--jetreserve-red);
        font-size: 1.2rem;
    }

    /* Passenger & Details Grid */
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .detail-card {
        background: var(--light);
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid var(--jetreserve-red);
    }

    .detail-card h4 {
        color: var(--jetreserve-blue);
        margin-bottom: 1rem;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border);
    }

    .detail-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    /* Price Breakdown */
    .price-breakdown {
        background: var(--light);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid var(--jetreserve-blue);
    }

    .price-breakdown h4 {
        color: var(--jetreserve-blue);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .price-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border);
    }

    .price-total {
        border-top: 2px solid var(--jetreserve-red);
        padding-top: 0.75rem;
        margin-top: 0.75rem;
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--jetreserve-blue);
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid var(--light);
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .status-confirmed {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-paid {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    /* Seat Display */
    .seat-display {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--light);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        margin: 0.25rem;
        font-size: 0.875rem;
        border: 1px solid var(--jetreserve-blue);
    }

    .seat-premium {
        background: #fff3cd;
        border: 1px solid var(--jetreserve-red);
        color: var(--jetreserve-blue);
    }

    /* Baggage Tags */
    .baggage-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--jetreserve-blue);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        margin: 0.25rem;
        font-size: 0.875rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .confirmation-title {
            font-size: 2rem;
        }
        
        .flight-info {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .ticket-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .details-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
        
        .confirmation-header {
            padding: 2rem 1rem;
        }
    }

    @media print {
        .no-print {
            display: none !important;
        }
        
        .confirmation-container {
            box-shadow: none;
            margin: 0;
            border: none;
        }
        
        .action-buttons {
            display: none;
        }
        
        body {
            background: white;
        }
        
        .confirmation-header {
            background: #f8f9fa !important;
            color: var(--jetreserve-blue) !important;
        }
        
        .confirmation-icon {
            color: var(--jetreserve-red) !important;
        }
    }

    /* Icônes et embellissements */
    .icon-primary {
        color: var(--jetreserve-red);
    }

    .icon-secondary {
        color: var(--jetreserve-blue);
    }

    /* Message de remerciement */
    .thank-you-message {
        text-align: center;
        background: linear-gradient(135deg, #fff5f5 0%, #f8f9fa 100%);
        border-radius: 15px;
        padding: 2rem;
        margin: 2rem 0;
        border: 2px solid var(--jetreserve-red);
    }

    .thank-you-message h3 {
        color: var(--jetreserve-blue);
        margin-bottom: 1rem;
    }

    .thank-you-message p {
        color: var(--secondary);
        margin-bottom: 0.5rem;
    }
</style>
    
</head>
<body>
    <header class="no-print">
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
            <!-- En-tête de confirmation -->
            <div class="confirmation-header">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="confirmation-title">Paiement Confirmé !</h1>
                <p class="confirmation-subtitle">Votre réservation est maintenant confirmée</p>
                
                <!-- Confettis animés -->
                <div id="confetti-container"></div>
            </div>
            
            <div class="confirmation-body">
                <!-- Message de succès -->
                <div style="text-align: center; margin-bottom: 2rem;">
                    <h2 style="color: var(--success); margin-bottom: 1rem;">
                        <i class="fas fa-check-circle"></i> Félicitations <?php echo htmlspecialchars($reservation['prenom']); ?> !
                    </h2>
                    <p style="font-size: 1.1rem; color: var(--secondary);">
                        Votre paiement a été traité avec succès et votre réservation est confirmée.
                    </p>
                </div>

                <!-- Statut de l'email -->
                <div class="payment-success">
                    <div style="font-size: 2rem; color: #28a745; margin-bottom: 1rem;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 style="color: #28a745; margin-bottom: 1rem;">Email de Confirmation</h3>
                    <?php if ($email_status && $email_status['statut'] === 'envoyé'): ?>
                        <p style="margin-bottom: 1rem; font-size: 1.1rem;">
                            Un email de confirmation a été envoyé à 
                            <strong><?php echo htmlspecialchars($reservation['email']); ?></strong>
                        </p>
                        <p style="margin: 0; font-size: 1rem;">
                            Envoyé le: <strong><?php echo date('d/m/Y à H:i', strtotime($email_status['date_envoi'])); ?></strong>
                        </p>
                    <?php else: ?>
                        <p style="margin-bottom: 1rem; font-size: 1.1rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                            L'email de confirmation n'a pas pu être envoyé.
                        </p>
                        <p style="margin: 0; font-size: 1rem;">
                            Vous pouvez télécharger cette page comme preuve de réservation.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Le reste de votre code de confirmation existant -->
                <!-- [Garder tout le reste de votre code HTML existant] -->
                
            </div>
        </div>
    </div>

    <script>
        // Générer des confettis
        function createConfetti() {
            const container = document.getElementById('confetti-container');
            const colors = ['#f39c12', '#e74c3c', '#3498db', '#2ecc71', '#9b59b6'];
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 5 + 's';
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = confetti.style.width;
                container.appendChild(confetti);
            }
        }

        // Lancer les confettis au chargement
        document.addEventListener('DOMContentLoaded', createConfetti);
    </script>
</body>
</html>