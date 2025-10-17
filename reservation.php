<?php
session_start();
require_once 'includes/db.php';
require_once 'classes/Vol.php';

// Vérifier si les paramètres sont présents
if (!isset($_GET['airline']) || !isset($_GET['price'])) {
    header('Location: index.php');
    exit;
}

// Récupérer les données du vol
$vol_data = [
    'airline' => $_GET['airline'],
    'flight_number' => $_GET['flight_number'],
    'from' => $_GET['from'],
    'to' => $_GET['to'],
    'departure_date' => $_GET['departure_date'],
    'return_date' => $_GET['return_date'],
    'passengers' => $_GET['passengers'],
    'class' => $_GET['class'],
    'price' => $_GET['price']
];

// Calculer le prix total
$passenger_count = intval($vol_data['passengers']);
$price_per_passenger = floatval($vol_data['price']);
$total_price = $passenger_count * $price_per_passenger;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - JetReserve</title>
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
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none;
            color: #1e293b;
        }

        .logo span {
            color: #2563eb;
        }

        .reservation-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin: 30px auto;
        }

        .flight-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .booking-form {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            position: sticky;
            top: 20px;
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

        .price-breakdown {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
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

        .flight-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .flight-route {
            text-align: center;
            flex: 1;
        }

        .flight-time {
            font-size: 24px;
            font-weight: bold;
            color: #1e293b;
        }

        .flight-city {
            color: #64748b;
            font-size: 14px;
        }

        .flight-duration {
            text-align: center;
            color: #64748b;
            margin: 0 20px;
        }

        .flight-airline {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <a href="vge64/connexion.php" class="btn btn-outline">Connexion</a>
                    <a href="vge64/inscription.php" class="btn btn-primary">Inscription</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="reservation-container">
            <!-- Formulaire de réservation -->
            <div class="booking-section">
                <div class="flight-summary">
                    <h2>Détails du vol</h2>
                    <div class="flight-details">
                        <div class="flight-route">
                            <div class="flight-time"><?php echo date('H:i', strtotime($vol_data['departure_date'])); ?></div>
                            <div class="flight-city"><?php echo htmlspecialchars($vol_data['from']); ?></div>
                        </div>
                        <div class="flight-duration">
                            <div>➝</div>
                            <div style="font-size: 12px;">Vol direct</div>
                        </div>
                        <div class="flight-route">
                            <div class="flight-time"><?php echo date('H:i', strtotime($vol_data['return_date'])); ?></div>
                            <div class="flight-city"><?php echo htmlspecialchars($vol_data['to']); ?></div>
                        </div>
                    </div>
                    <div class="flight-airline">
                        <i class="fas fa-plane"></i>
                        <div>
                            <strong><?php echo htmlspecialchars($vol_data['airline']); ?></strong>
                            <div style="color: #64748b; font-size: 14px;">
                                Vol <?php echo htmlspecialchars($vol_data['flight_number']); ?> • <?php echo htmlspecialchars($vol_data['class']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="process_booking.php" method="POST" class="booking-form">
                    <h3>Informations des passagers</h3>
                    
                    <?php for($i = 1; $i <= $passenger_count; $i++): ?>
                    <div class="passenger-section" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
                        <h4>Passager <?php echo $i; ?></h4>
                        <div class="form-group">
                            <label for="passenger<?php echo $i; ?>_name">Nom complet</label>
                            <input type="text" id="passenger<?php echo $i; ?>_name" name="passengers[<?php echo $i; ?>][name]" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="passenger<?php echo $i; ?>_email">Email</label>
                            <input type="email" id="passenger<?php echo $i; ?>_email" name="passengers[<?php echo $i; ?>][email]" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="passenger<?php echo $i; ?>_phone">Téléphone</label>
                            <input type="tel" id="passenger<?php echo $i; ?>_phone" name="passengers[<?php echo $i; ?>][phone]" class="form-control" required>
                        </div>
                    </div>
                    <?php endfor; ?>

                    <h3>Paiement</h3>
                    <div class="form-group">
                        <label for="card_number">Numéro de carte</label>
                        <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="expiry_date">Date d'expiration</label>
                            <input type="text" id="expiry_date" name="expiry_date" class="form-control" placeholder="MM/AA" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" class="form-control" placeholder="123" required>
                        </div>
                    </div>

                    <!-- Champs cachés avec les données du vol -->
                    <input type="hidden" name="airline" value="<?php echo htmlspecialchars($vol_data['airline']); ?>">
                    <input type="hidden" name="flight_number" value="<?php echo htmlspecialchars($vol_data['flight_number']); ?>">
                    <input type="hidden" name="from" value="<?php echo htmlspecialchars($vol_data['from']); ?>">
                    <input type="hidden" name="to" value="<?php echo htmlspecialchars($vol_data['to']); ?>">
                    <input type="hidden" name="departure_date" value="<?php echo htmlspecialchars($vol_data['departure_date']); ?>">
                    <input type="hidden" name="class" value="<?php echo htmlspecialchars($vol_data['class']); ?>">
                    <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-credit-card"></i> Payer <?php echo $total_price; ?>€
                    </button>
                </form>
            </div>

            <!-- Récapitulatif du prix -->
            <div class="price-summary">
                <div class="booking-form">
                    <h3>Récapitulatif</h3>
                    <div class="price-breakdown">
                        <div class="price-item">
                            <span>Passager x<?php echo $passenger_count; ?> (<?php echo htmlspecialchars($vol_data['class']); ?>)</span>
                            <span><?php echo $price_per_passenger; ?>€</span>
                        </div>
                        <div class="price-item">
                            <span>Frais de service</span>
                            <span>9€</span>
                        </div>
                        <div class="price-item">
                            <span>Taxes aéroport</span>
                            <span>25€</span>
                        </div>
                        <div class="price-total">
                            <span>Total</span>
                            <span><?php echo $total_price + 9 + 25; ?>€</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>