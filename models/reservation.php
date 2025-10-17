<?php
session_start();

// Inclure la configuration et les modèles
include_once 'config/database.php';
include_once 'models/Vol.php';
include_once 'models/Reservation.php';
include_once 'models/Paiement.php';
include_once 'models/User.php';

// Initialiser la base de données
$database = new Database();
$db = $database->getConnection();

// Vérifier si l'utilisateur est connecté
$user_connected = isset($_SESSION['user_id']);
$userData = [];

if ($user_connected) {
    $user = new User($db);
    $userData = $user->getById($_SESSION['user_id']);
}

// Récupération des données du vol depuis l'URL
$id_vol = $_GET['id_vol'] ?? null;
$volData = [];

if ($id_vol) {
    $vol = new Vol($db);
    $volData = $vol->getById($id_vol);
    
    if (!$volData) {
        die("Vol non trouvé");
    }
} else {
    // Données par défaut si pas d'ID (pour compatibilité)
    $volData = [
        'id_vol' => 0,
        'nom_compagnie' => $_GET['airline'] ?? 'Air France',
        'code_compagnie' => $_GET['flight_number'] ?? 'AF',
        'depart' => $_GET['from'] ?? 'Paris (CDG)',
        'arrivee' => $_GET['to'] ?? 'New York (JFK)',
        'date_depart' => $_GET['departure_date'] ?? date('Y-m-d H:i:s', strtotime('+7 days')),
        'date_arrivee' => $_GET['return_date'] ?? date('Y-m-d H:i:s', strtotime('+7 days 8 hours')),
        'prix' => $_GET['price'] ?? '450',
        'classe' => $_GET['class'] ?? 'Économique',
        'escales' => 0
    ];
}

// Traitement du formulaire de réservation
if ($_POST && $user_connected) {
    try {
        // Démarrer une transaction
        $db->beginTransaction();
        
        // 1. Créer la réservation
        $reservation = new Reservation($db);
        $reservation->id_user = $_SESSION['user_id'];
        $reservation->id_vol = $id_vol;
        
        $id_reservation = $reservation->create();
        
        if (!$id_reservation) {
            throw new Exception("Erreur lors de la création de la réservation");
        }
        
        // 2. Créer le paiement
        $paiement = new Paiement($db);
        $paiement->id_reservation = $id_reservation;
        $paiement->montant = $volData['prix'];
        $paiement->mode_paiement = 'carte'; // Par défaut
        
        $id_paiement = $paiement->create();
        
        if (!$id_paiement) {
            throw new Exception("Erreur lors de la création du paiement");
        }
        
        // Valider la transaction
        $db->commit();
        
        // Rediriger vers la page de paiement
        header("Location: paiement.php?id_reservation=" . $id_reservation);
        exit;
        
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $db->rollBack();
        $error_message = $e->getMessage();
    }
} elseif ($_POST && !$user_connected) {
    $error_message = "Vous devez être connecté pour effectuer une réservation";
}

// Calcul du nombre de voyageurs
$passengerCount = 1;
if (preg_match('/(\d+)/', $_GET['passengers'] ?? '1 voyageur', $matches)) {
    $passengerCount = intval($matches[1]);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="reservation.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <?php if ($user_connected): ?>
                        <span>Bonjour, <?php echo htmlspecialchars($userData['prenom']); ?></span>
                        <a href="deconnexion.php" class="btn btn-outline">Déconnexion</a>
                    <?php else: ?>
                        <a href="vge64/connexion.php" class="btn btn-outline">Connexion</a>
                        <a href="vge64/inscription.php" class="btn btn-primary">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
            <nav class="nav-menu">
                <ul class="nav-links">
                    <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="#"><i class="fas fa-plane-departure"></i> Vols</a></li>
                    <li><a href="#"><i class="fas fa-suitcase-rolling"></i> Forfaits</a></li>
                    <li><a href="#"><i class="fas fa-ticket-alt"></i> Billetterie</a></li>
                    <li><a href="#"><i class="fas fa-map-marked-alt"></i> Destinations</a></li>
                    <li><a href="#"><i class="fas fa-tags"></i> Offres spéciales</a></li>
                </ul>
                <div class="contact-info">
                    <a href="#"><i class="fas fa-phone-alt"></i> Service client</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Processus de réservation -->
    <main class="container">
        <div class="reservation-header">
            <h1>Finaliser votre réservation</h1>
            <div class="reservation-steps">
                <div class="step active">
                    <span class="step-number">1</span>
                    <span class="step-text">Détails du vol</span>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span class="step-text">Informations voyageurs</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-text">Options supplémentaires</span>
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    <span class="step-text">Paiement</span>
                </div>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <?php if (!$user_connected): ?>
        <div class="warning-message">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Attention :</strong> Vous devez être connecté pour effectuer une réservation. 
            <a href="vge64/connexion.php" style="color: #2563eb; text-decoration: underline; margin-left: 10px;">Se connecter</a>
            ou 
            <a href="vge64/inscription.php" style="color: #2563eb; text-decoration: underline;">créer un compte</a>
        </div>
        <?php endif; ?>

        <div class="reservation-content">
            <!-- Section Détails du vol -->
            <section class="reservation-section">
                <h2><i class="fas fa-plane"></i> Détails de votre vol</h2>
                
                <div class="flight-details-card">
                    <div class="flight-header">
                        <div class="airline-info">
                            <i class="fas fa-plane fa-2x"></i>
                            <div>
                                <h3><?php echo htmlspecialchars($volData['nom_compagnie']); ?></h3>
                                <p>Vol <?php echo htmlspecialchars($volData['code_compagnie'] . $volData['id_vol']); ?></p>
                            </div>
                        </div>
                        <div class="flight-price">
                            <span class="price"><?php echo htmlspecialchars($volData['prix']); ?>€</span>
                            <span class="price-detail">par personne</span>
                        </div>
                    </div>

                    <div class="flight-route">
                        <div class="route-segment">
                            <div class="route-time">
                                <strong><?php echo date('H:i', strtotime($volData['date_depart'])); ?></strong>
                                <span><?php echo htmlspecialchars($volData['depart']); ?></span>
                            </div>
                            <div class="route-duration">
                                <?php
                                $depart = new DateTime($volData['date_depart']);
                                $arrivee = new DateTime($volData['date_arrivee']);
                                $duree = $depart->diff($arrivee);
                                ?>
                                <div class="duration-line">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <span><?php echo $duree->h . 'h ' . $duree->i . 'min'; ?></span>
                            </div>
                            <div class="route-time">
                                <strong><?php echo date('H:i', strtotime($volData['date_arrivee'])); ?></strong>
                                <span><?php echo htmlspecialchars($volData['arrivee']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="flight-info-grid">
                        <div class="info-item">
                            <label>Date de départ</label>
                            <strong><?php echo date('d/m/Y', strtotime($volData['date_depart'])); ?></strong>
                        </div>
                        <div class="info-item">
                            <label>Date d'arrivée</label>
                            <strong><?php echo date('d/m/Y', strtotime($volData['date_arrivee'])); ?></strong>
                        </div>
                        <div class="info-item">
                            <label>Classe</label>
                            <strong><?php echo htmlspecialchars(ucfirst($volData['classe'])); ?></strong>
                        </div>
                        <div class="info-item">
                            <label>Passagers</label>
                            <strong><?php echo $passengerCount; ?> voyageur(s)</strong>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section Informations voyageurs -->
            <section class="reservation-section">
                <h2><i class="fas fa-users"></i> Informations des voyageurs</h2>
                
                <form id="passengerForm" class="passenger-form">
                    <?php for($i = 1; $i <= $passengerCount; $i++): ?>
                    <div class="passenger-card">
                        <h3>Voyageur <?php echo $i; ?></h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="passenger_civility_<?php echo $i; ?>">Civilité *</label>
                                <select id="passenger_civility_<?php echo $i; ?>" name="passenger[<?php echo $i; ?>][civility]" required <?php echo !$user_connected ? 'disabled' : ''; ?>>
                                    <option value="">Sélectionner</option>
                                    <option value="M">Monsieur</option>
                                    <option value="Mme">Madame</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="passenger_lastname_<?php echo $i; ?>">Nom *</label>
                                <input type="text" id="passenger_lastname_<?php echo $i; ?>" name="passenger[<?php echo $i; ?>][lastname]" required <?php echo !$user_connected ? 'disabled' : ''; ?>>
                            </div>
                            <div class="form-group">
                                <label for="passenger_firstname_<?php echo $i; ?>">Prénom *</label>
                                <input type="text" id="passenger_firstname_<?php echo $i; ?>" name="passenger[<?php echo $i; ?>][firstname]" required <?php echo !$user_connected ? 'disabled' : ''; ?>>
                            </div>
                            <div class="form-group">
                                <label for="passenger_birthdate_<?php echo $i; ?>">Date de naissance *</label>
                                <input type="date" id="passenger_birthdate_<?php echo $i; ?>" name="passenger[<?php echo $i; ?>][birthdate]" required <?php echo !$user_connected ? 'disabled' : ''; ?>>
                            </div>
                            <div class="form-group">
                                <label for="passenger_email_<?php echo $i; ?>">Email *</label>
                                <input type="email" id="passenger_email_<?php echo $i; ?>" name="passenger[<?php echo $i; ?>][email]" required <?php echo !$user_connected ? 'disabled' : ''; ?> 
                                    value="<?php echo ($i === 1 && $user_connected) ? htmlspecialchars($userData['email']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="passenger_phone_<?php echo $i; ?>">Téléphone *</label>
                                <input type="tel" id="passenger_phone_<?php echo $i; ?>" name="passenger[<?php echo $i; ?>][phone]" required <?php echo !$user_connected ? 'disabled' : ''; ?>
                                    value="<?php echo ($i === 1 && $user_connected && !empty($userData['telephone'])) ? htmlspecialchars($userData['telephone']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </form>
            </section>

            <!-- Section Options supplémentaires -->
            <section class="reservation-section">
                <h2><i class="fas fa-plus-circle"></i> Options supplémentaires</h2>
                
                <div class="options-grid">
                    <div class="option-card">
                        <div class="option-header">
                            <h3>Bagages supplémentaires</h3>
                            <span class="option-price">+ 35€</span>
                        </div>
                        <p>Ajoutez 23kg de bagage en soute supplémentaire</p>
                        <div class="option-select">
                            <input type="checkbox" id="extra_baggage" name="extra_baggage" <?php echo !$user_connected ? 'disabled' : ''; ?>>
                            <label for="extra_baggage">Sélectionner</label>
                        </div>
                    </div>

                    <div class="option-card">
                        <div class="option-header">
                            <h3>Assurance voyage</h3>
                            <span class="option-price">+ 29€</span>
                        </div>
                        <p>Protection annulation, bagages et assistance médicale</p>
                        <div class="option-select">
                            <input type="checkbox" id="travel_insurance" name="travel_insurance" <?php echo !$user_connected ? 'disabled' : ''; ?>>
                            <label for="travel_insurance">Sélectionner</label>
                        </div>
                    </div>

                    <div class="option-card">
                        <div class="option-header">
                            <h3>Siège premium</h3>
                            <span class="option-price">+ 25€</span>
                        </div>
                        <p>Siège avec plus d'espace pour les jambes</p>
                        <div class="option-select">
                            <input type="checkbox" id="premium_seat" name="premium_seat" <?php echo !$user_connected ? 'disabled' : ''; ?>>
                            <label for="premium_seat">Sélectionner</label>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Récapitulatif et paiement -->
            <section class="reservation-section">
                <h2><i class="fas fa-credit-card"></i> Récapitulatif et validation</h2>
                
                <div class="summary-payment">
                    <div class="summary-card">
                        <h3>Récapitulatif de commande</h3>
                        <div class="summary-items">
                            <div class="summary-item">
                                <span>Vol <?php echo htmlspecialchars($volData['nom_compagnie']); ?></span>
                                <span><?php echo htmlspecialchars($volData['prix']); ?>€ × <?php echo $passengerCount; ?></span>
                            </div>
                            <div class="summary-item optional" id="baggage-item" style="display: none;">
                                <span>Bagage supplémentaire</span>
                                <span>35€</span>
                            </div>
                            <div class="summary-item optional" id="insurance-item" style="display: none;">
                                <span>Assurance voyage</span>
                                <span>29€</span>
                            </div>
                            <div class="summary-item optional" id="seat-item" style="display: none;">
                                <span>Siège premium</span>
                                <span>25€</span>
                            </div>
                            <div class="summary-total">
                                <strong>Total</strong>
                                <strong id="total-price"><?php echo htmlspecialchars($volData['prix'] * $passengerCount); ?>€</strong>
                            </div>
                        </div>
                    </div>

                    <div class="payment-card">
                        <h3>Finaliser la réservation</h3>
                        
                        <!-- Formulaire pour passer au paiement -->
                        <form id="reservationForm" method="POST" action="">
                            <!-- Champs cachés avec les données du vol -->
                            <input type="hidden" name="id_vol" value="<?php echo htmlspecialchars($volData['id_vol']); ?>">
                            
                            <div class="terms-section">
                                <div class="checkbox-group">
                                    <input type="checkbox" id="terms" name="terms" required <?php echo !$user_connected ? 'disabled' : ''; ?>>
                                    <label for="terms">
                                        J'accepte les <a href="#" target="_blank">conditions générales de vente</a> 
                                        et la <a href="#" target="_blank">politique de confidentialité</a> *
                                    </label>
                                </div>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" id="newsletter" name="newsletter" <?php echo !$user_connected ? 'disabled' : ''; ?>>
                                    <label for="newsletter">
                                        Je souhaite recevoir les offres promotionnelles de JetReserve
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-large payment-btn" <?php echo !$user_connected ? 'disabled' : ''; ?>>
                                <i class="fas fa-arrow-right"></i>
                                <span>Procéder au paiement</span>
                                <small>Total: <span id="final-total"><?php echo htmlspecialchars($volData['prix'] * $passengerCount); ?>€</span></small>
                            </button>
                        </form>
                        
                        <div class="security-guarantee">
                            <i class="fas fa-shield-check"></i>
                            <span>Paiement 100% sécurisé - Données cryptées</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h4>JetReserve</h4>
                    <p style="color: #cbd5e1; margin-bottom: 20px; font-size: 14px;">
                        Votre partenaire de confiance pour des voyages inoubliables depuis 2023.
                    </p>
                </div>
                <div class="footer-column">
                    <h4>Assistance réservation</h4>
                    <div class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Conditions générales</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Politique de remboursement</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> FAQ</a></li>
                    </div>
                </div>
                <div class="footer-column">
                    <h4>Contact urgent</h4>
                    <div class="contact-info-footer">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+33 1 23 45 67 89</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>reservation@jetreserve.com</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 JetReserve. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Script pour la page de réservation
        $(document).ready(function() {
            // Gestion des options supplémentaires
            $('input[type="checkbox"]').on('change', function() {
                updateSummary();
            });

            // Validation du formulaire de réservation
            $('#reservationForm').on('submit', function(e) {
                if (!validateReservationForm()) {
                    e.preventDefault();
                }
            });

            // Mise à jour du récapitulatif
            function updateSummary() {
                const basePrice = parseFloat('<?php echo $volData["prix"]; ?>');
                const passengerCount = parseInt('<?php echo $passengerCount; ?>');
                let total = basePrice * passengerCount;

                // Bagage supplémentaire
                if ($('#extra_baggage').is(':checked')) {
                    $('#baggage-item').show();
                    total += 35;
                } else {
                    $('#baggage-item').hide();
                }

                // Assurance voyage
                if ($('#travel_insurance').is(':checked')) {
                    $('#insurance-item').show();
                    total += 29;
                } else {
                    $('#insurance-item').hide();
                }

                // Siège premium
                if ($('#premium_seat').is(':checked')) {
                    $('#seat-item').show();
                    total += 25;
                } else {
                    $('#seat-item').hide();
                }

                $('#total-price').text(total.toFixed(2) + '€');
                $('#final-total').text(total.toFixed(2) + '€');
            }

            // Validation du formulaire de réservation
            function validateReservationForm() {
                let isValid = true;

                // Validation des informations voyageurs
                const passengerCount = parseInt('<?php echo $passengerCount; ?>');
                
                for (let i = 1; i <= passengerCount; i++) {
                    const civility = $(`#passenger_civility_${i}`).val();
                    const lastname = $(`#passenger_lastname_${i}`).val();
                    const firstname = $(`#passenger_firstname_${i}`).val();
                    const birthdate = $(`#passenger_birthdate_${i}`).val();
                    const email = $(`#passenger_email_${i}`).val();
                    const phone = $(`#passenger_phone_${i}`).val();

                    if (!civility || !lastname || !firstname || !birthdate || !email || !phone) {
                        isValid = false;
                        showNotification('Veuillez remplir tous les champs obligatoires pour chaque voyageur', 'error');
                        break;
                    }

                    // Validation email
                    if (email && !isValidEmail(email)) {
                        isValid = false;
                        showNotification(`Format d'email invalide pour le voyageur ${i}`, 'error');
                        break;
                    }
                }

                // Validation conditions générales
                if (!$('#terms').is(':checked')) {
                    isValid = false;
                    showNotification('Vous devez accepter les conditions générales', 'error');
                }

                return isValid;
            }

            // Validation email
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Affichage des notifications
            function showNotification(message, type) {
                const notification = $('<div class="notification"></div>');
                const bgColor = type === 'success' ? '#10b981' : '#ef4444';
                
                notification.css({
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    background: bgColor,
                    color: 'white',
                    padding: '15px 20px',
                    borderRadius: '8px',
                    zIndex: '10000',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                    animation: 'slideInRight 0.3s ease-out',
                    maxWidth: '400px',
                    fontWeight: '500'
                });
                
                notification.text(message);
                $('body').append(notification);
                
                setTimeout(() => {
                    notification.animate({
                        right: '-500px'
                    }, 300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }

            // Initialisation
            updateSummary();
        });
    </script>
</body>
</html>