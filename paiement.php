<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vol_id = intval($_POST['vol_id']);
    $sieges_ids = $_POST['sieges'] ?? [];
    
    if (empty($sieges_ids)) {
        header('Location: seat_selection.php?vol_id=' . $vol_id);
        exit;
    }
    
    // Récupérer les informations du vol
    $stmt = $pdo->prepare("SELECT v.*, c.nom_compagnie FROM vols v 
                          JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
                          WHERE v.id_vol = ?");
    $stmt->execute([$vol_id]);
    $vol = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vol) {
        die("Vol non trouvé");
    }
    
    // Récupérer les informations des sièges sélectionnés
    $placeholders = str_repeat('?,', count($sieges_ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM sieges_avion WHERE id_siege IN ($placeholders)");
    $stmt->execute($sieges_ids);
    $sieges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculer le prix total
    $prix_base = $vol['prix'] * count($sieges_ids);
    $supplements = 0;
    foreach ($sieges as $siege) {
        $supplements += $siege['supplement_prix'];
    }
    $frais_service = 9.00;
    $taxes = 25.00;
    $total_final = $prix_base + $supplements + $frais_service + $taxes;
    
} else {
    header('Location: vols.php');
    exit;
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
    <style>
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .payment-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }
        
        .payment-form, .order-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
        }
        
        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .price-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 18px;
            color: #1e293b;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #e2e8f0;
        }
        
        .flight-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .selected-seats {
            margin: 15px 0;
        }
        
        .seat-badge {
            background: #2563eb;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Finaliser votre réservation</h1>
        
        <div class="payment-container">
            <div class="payment-form">
                <h2>Informations de paiement</h2>
                <form action="process_payment.php" method="POST">
                    <div class="form-group">
                        <label>Numéro de carte</label>
                        <input type="text" class="form-control" name="card_number" placeholder="1234 5678 9012 3456" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Date d'expiration</label>
                            <input type="text" class="form-control" name="expiry_date" placeholder="MM/AA" required>
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" class="form-control" name="cvv" placeholder="123" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Nom du titulaire</label>
                        <input type="text" class="form-control" name="card_holder" required>
                    </div>
                    
                    <!-- Champs cachés -->
                    <input type="hidden" name="vol_id" value="<?php echo $vol_id; ?>">
                    <input type="hidden" name="sieges" value="<?php echo htmlspecialchars(json_encode($sieges_ids)); ?>">
                    <input type="hidden" name="total_amount" value="<?php echo $total_final; ?>">
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-lock"></i> Payer <?php echo $total_final; ?>€
                    </button>
                </form>
            </div>
            
            <div class="order-summary">
                <h2>Récapitulatif</h2>
                <div class="flight-info">
                    <h4><?php echo htmlspecialchars($vol['depart']); ?> → <?php echo htmlspecialchars($vol['arrivee']); ?></h4>
                    <p><strong><?php echo htmlspecialchars($vol['nom_compagnie']); ?></strong></p>
                    <p><?php echo date('d M Y, H:i', strtotime($vol['date_depart'])); ?></p>
                    <p>Classe: <?php echo htmlspecialchars($vol['classe']); ?></p>
                </div>
                
                <div class="selected-seats">
                    <strong>Sièges sélectionnés:</strong><br>
                    <?php foreach ($sieges as $siege): ?>
                        <span class="seat-badge"><?php echo $siege['rang'] . $siege['position']; ?></span>
                    <?php endforeach; ?>
                </div>
                
                <div class="price-breakdown">
                    <div class="price-item">
                        <span>Vol x<?php echo count($sieges_ids); ?></span>
                        <span><?php echo $prix_base; ?>€</span>
                    </div>
                    <?php if ($supplements > 0): ?>
                    <div class="price-item">
                        <span>Supplements sièges</span>
                        <span><?php echo $supplements; ?>€</span>
                    </div>
                    <?php endif; ?>
                    <div class="price-item">
                        <span>Frais de service</span>
                        <span><?php echo $frais_service; ?>€</span>
                    </div>
                    <div class="price-item">
                        <span>Taxes aéroport</span>
                        <span><?php echo $taxes; ?>€</span>
                    </div>
                    <div class="price-total">
                        <span>Total</span>
                        <span><?php echo $total_final; ?>€</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>