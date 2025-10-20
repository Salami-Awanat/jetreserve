<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: connexion.php');
    exit;
}

// Vérifier si l'ID de réservation est passé en paramètre
if (!isset($_GET['id_reservation'])) {
    header('Location: mes_reservations.php');
    exit;
}

$id_reservation = $_GET['id_reservation'];
$id_user = $_SESSION['id_user'];
// Vérifier que la réservation est bien en attente de paiement
try {
    $stmt = $pdo->prepare("
        SELECT r.*, v.depart, v.arrivee, v.date_depart, v.numero_vol, 
               c.nom_compagnie, u.nom, u.prenom, u.email
        FROM reservations r
        JOIN vols v ON r.id_vol = v.id_vol
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie
        JOIN utilisateurs u ON r.id_user = u.id_user
        WHERE r.id_reservation = ? AND r.id_user = ? AND r.statut = 'en attente'
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
// Récupérer les détails de la réservation
try {
    $stmt = $pdo->prepare("
        SELECT r.*, v.depart, v.arrivee, v.date_depart, v.numero_vol, 
               c.nom_compagnie, u.nom, u.prenom, u.email
        FROM reservations r
        JOIN vols v ON r.id_vol = v.id_vol
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie
        JOIN utilisateurs u ON r.id_user = u.id_user
        WHERE r.id_reservation = ? AND r.id_user = ? AND r.statut = 'en attente'
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

// Traitement du formulaire de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_carte = str_replace(' ', '', $_POST['numero_carte']);
    $date_expiration = $_POST['date_expiration'];
    $cvv = $_POST['cvv'];
    $titulaire_carte = $_POST['titulaire_carte'];
    $mode_paiement = 'carte'; // Vous pouvez ajouter un select pour choisir le mode
    $mode_paiement = $_POST['mode_paiement'];
    // Validation basique
    $errors = [];
    
    if (strlen($numero_carte) !== 16 || !is_numeric($numero_carte)) {
        $errors[] = "Numéro de carte invalide";
    }
    
    if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $date_expiration)) {
        $errors[] = "Date d'expiration invalide";
    }
    
    if (strlen($cvv) !== 3 || !is_numeric($cvv)) {
        $errors[] = "CVV invalide";
    }
    
    if (empty($titulaire_carte)) {
        $errors[] = "Nom du titulaire requis";
    }
    
    // Si pas d'erreurs, traiter le paiement
    if (empty($errors)) {
        try {
            // Commencer une transaction
            $pdo->beginTransaction();
            
            // Mettre à jour le statut de la réservation
            $stmt = $pdo->prepare("
                UPDATE reservations 
                SET statut = 'confirmé'
                WHERE id_reservation = ?
            ");
            $stmt->execute([$id_reservation]);
            
            // Enregistrer les détails du paiement
            $stmt = $pdo->prepare("
                INSERT INTO paiements 
                (id_reservation, montant, mode_paiement, statut, date_paiement)
                VALUES (?, ?, ?, 'accepté', NOW())
            ");
            $stmt->execute([$id_reservation, $reservation['prix_total'], $mode_paiement]);
            
            // Valider la transaction
            $pdo->commit();
            
            // Rediriger vers la page de confirmation
            header('Location: confirmation_paiement.php?id_reservation=' . $id_reservation);
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Erreur lors du traitement du paiement: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style1.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .payment-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .payment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .card-input {
            position: relative;
        }
        .card-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body>
    <!-- Header (identique à mes_reservations.php) -->
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
        <div class="payment-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title">
                    <i class="fas fa-credit-card me-2"></i>
                    Paiement de la réservation
                </h1>
                <a href="mes_reservations.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            <!-- Résumé de la réservation -->
            <div class="payment-summary">
                <h5>Résumé de votre réservation</h5>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <strong>Vol:</strong> <?php echo htmlspecialchars($reservation['depart']); ?> → 
                        <?php echo htmlspecialchars($reservation['arrivee']); ?><br>
                        <strong>Compagnie:</strong> <?php echo htmlspecialchars($reservation['nom_compagnie']); ?><br>
                        <strong>Numéro de vol:</strong> <?php echo htmlspecialchars($reservation['numero_vol']); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Date de départ:</strong> <?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?><br>
                        <strong>Passagers:</strong> <?php echo $reservation['nombre_passagers']; ?> personne(s)<br>
                        <strong>Montant total:</strong> <span class="text-primary fw-bold"><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?> €</span>
                    </div>
                </div>
            </div>

            <!-- Messages d'erreur -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulaire de paiement -->
            <div class="payment-card">
                <h4 class="mb-4">Informations de paiement</h4>
                
                <form method="POST" id="paymentForm">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="numero_carte" class="form-label">Numéro de carte</label>
                            <div class="card-input">
                                <input type="text" class="form-control" id="numero_carte" name="numero_carte" 
                                       placeholder="1234 5678 9012 3456" maxlength="19" required>
                                <i class="fab fa-cc-visa card-icon"></i>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="date_expiration" class="form-label">Date d'expiration</label>
                            <input type="text" class="form-control" id="date_expiration" name="date_expiration" 
                                   placeholder="MM/AA" maxlength="5" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" name="cvv" 
                                   placeholder="123" maxlength="3" required>
                        </div>
                        
                        <div class="col-12 mb-4">
                            <label for="titulaire_carte" class="form-label">Nom du titulaire</label>
                            <input type="text" class="form-control" id="titulaire_carte" name="titulaire_carte" 
                                   placeholder="M. DUPONT Jean" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-lock me-2"></i>
                            Payer <?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?> €
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-lock me-1"></i>
                            Paiement sécurisé SSL
                        </small>
                    </div>
                    <div class="col-12 mb-3">
    <label for="mode_paiement" class="form-label">Mode de paiement</label>
    <select class="form-select" id="mode_paiement" name="mode_paiement" required>
        <option value="carte">Carte bancaire</option>
        <option value="paypal">PayPal</option>
        <option value="virement">Virement bancaire</option>
    </select>
</div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Formatage du numéro de carte
        $('#numero_carte').on('input', function() {
            let value = $(this).val().replace(/\s/g, '').replace(/\D/g, '');
            let formatted = '';
            
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formatted += ' ';
                }
                formatted += value[i];
            }
            
            $(this).val(formatted.substring(0, 19));
        });

        // Formatage de la date d'expiration
        $('#date_expiration').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            $(this).val(value.substring(0, 5));
        });

        // Validation du formulaire
        $('#paymentForm').on('submit', function(e) {
            let numeroCarte = $('#numero_carte').val().replace(/\s/g, '');
            let dateExpiration = $('#date_expiration').val();
            let cvv = $('#cvv').val();
            
            if (numeroCarte.length !== 16) {
                alert('Le numéro de carte doit contenir 16 chiffres');
                e.preventDefault();
                return false;
            }
            
            if (!/^\d{2}\/\d{2}$/.test(dateExpiration)) {
                alert('Format de date d\'expiration invalide (MM/AA)');
                e.preventDefault();
                return false;
            }
            
            if (cvv.length !== 3) {
                alert('Le CVV doit contenir 3 chiffres');
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>