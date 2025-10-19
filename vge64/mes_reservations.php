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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <!-- Header -->
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
            <nav class="nav-menu">
                <ul class="nav-links">
                    <li><a href="../index.php"><i class="fas fa-home"></i> Accueil</a></li>
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
    
    <!-- Bannière avec carousel -->
    <div class="main-banner-container">
        <div class="main-banner owl-carousel owl-theme">
            <div class="item banner-1">
                <div class="header-text">
                    <h2>Mes Réservations</h2>
                </div>
            </div>
            <div class="item banner-2">
                <div class="header-text">
                    <h2>Gérez vos voyages</h2>
                </div>
            </div>
            <div class="item banner-3">
                <div class="header-text">
                    <h2>Vos billets en ligne</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h2>JetReserve</h2>
                        <p>Tableau de bord client</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index2.php">
                                <i class="fas fa-home me-2"></i>
                                Accueil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="mes_reservations.php">
                                <i class="fas fa-plane-departure me-2"></i>
                                Mes réservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mes_paiements.php">
                                <i class="fas fa-credit-card me-2"></i>
                                Mes paiements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="messages.php">
                                <i class="fas fa-envelope me-2"></i>
                                Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user me-2"></i>
                                Mon profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-title">
                        <i class="fas fa-plane me-2"></i>
                        Mes Réservations
                    </h1>
                    <div class="filter-options">
                        <select class="form-select" id="statusFilter">
                            <option value="all">Tous les statuts</option>
                            <option value="confirmé">Confirmées</option>
                            <option value="en attente">En attente</option>
                            <option value="annulé">Annulées</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($reservations)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        Vous n'avez aucune réservation pour le moment.
                        <a href="../index.php" class="alert-link">Réserver un vol</a>
                    </div>
                <?php else: ?>
                    <div class="reservations-container">
                        <div class="reservations-header">
                            <h2>Historique des réservations</h2>
                            <span class="badge bg-primary"><?php echo count($reservations); ?> réservation(s)</span>
                        </div>

                        <div class="reservations-list">
                            <?php foreach ($reservations as $reservation): ?>
                                <div class="reservation-card" data-status="<?php echo $reservation['statut']; ?>">
                                    <div class="reservation-header">
                                        <div class="flight-info">
                                            <div class="flight-icon">
                                                <i class="fas fa-plane"></i>
                                            </div>
                                            <div>
                                                <div class="route">
                                                    <?php echo htmlspecialchars($reservation['depart']); ?> → 
                                                    <?php echo htmlspecialchars($reservation['arrivee']); ?>
                                                </div>
                                                <div class="flight-number">
                                                    <?php echo htmlspecialchars($reservation['code_compagnie'] . $reservation['numero_vol']); ?> • 
                                                    <?php echo htmlspecialchars($reservation['nom_compagnie']); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="reservation-status status-<?php echo $reservation['statut']; ?>">
                                            <?php echo ucfirst($reservation['statut']); ?>
                                        </div>
                                    </div>

                                    <div class="reservation-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Date de départ</span>
                                            <span class="detail-value">
                                                <?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?>
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Date d'arrivée</span>
                                            <span class="detail-value">
                                                <?php echo date('d/m/Y H:i', strtotime($reservation['date_arrivee'])); ?>
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Passagers</span>
                                            <span class="detail-value">
                                                <?php echo $reservation['nombre_passagers']; ?> personne(s)
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Sièges</span>
                                            <span class="detail-value">
                                                <?php echo $reservation['sieges'] ?: 'Non assignés'; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="reservation-footer">
                                        <div class="price">
                                            <strong><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?> €</strong>
                                        </div>
                                        <div class="reservation-actions">
                                            <button class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>Détails
                                            </button>
                                            <?php if ($reservation['statut'] === 'confirmé'): ?>
                                                <button class="btn btn-success btn-sm">
                                                    <i class="fas fa-download me-1"></i>Télécharger
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($reservation['statut'] === 'en attente'): ?>
                                                <button class="btn btn-warning btn-sm">
                                                    <i class="fas fa-credit-card me-1"></i>Payer
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h4>JetReserve</h4>
                    <ul class="footer-links">
                        <li><a href="#">À propos de nous</a></li>
                        <li><a href="#">Carrières</a></li>
                        <li><a href="#">Presse</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Informations</h4>
                    <ul class="footer-links">
                        <li><a href="#">Aide/FAQ</a></li>
                        <li><a href="#">Conditions générales</a></li>
                        <li><a href="#">Politique de confidentialité</a></li>
                        <li><a href="#">Cookies</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Services</h4>
                    <ul class="footer-links">
                        <li><a href="#">Vols</a></li>
                        <li><a href="#">Hôtels</a></li>
                        <li><a href="#">Voitures</a></li>
                        <li><a href="#">Activités</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Contact</h4>
                    <ul class="footer-links">
                        <li><a href="#">Service client</a></li>
                        <li><a href="#">Nous contacter</a></li>
                        <li><a href="#">Centres d'aide</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 JetReserve. Tous droits réservés.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function(){
            $(".owl-carousel").owlCarousel({
                items: 1,
                loop: true,
                autoplay: true,
                autoplayTimeout: 5000,
                autoplayHoverPause: true,
                nav: true,
                dots: true,
                animateOut: 'fadeOut',
                animateIn: 'fadeIn'
            });

            // Filtre par statut
            $('#statusFilter').on('change', function() {
                var status = $(this).val();
                $('.reservation-card').show();
                
                if (status !== 'all') {
                    $('.reservation-card').not('[data-status="' + status + '"]').hide();
                }
            });
        });
    </script>
</body>
</html>