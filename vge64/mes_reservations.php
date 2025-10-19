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
    
    <!-- Inclure les mêmes styles que index2.php -->
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
                    <h2>Bienvenu sur votre compte</h2>
                </div>
            </div>
            <div class="item banner-2">
                <div class="header-text">
                    <h2>Accédez à vos réservations</h2>
                </div>
            </div>
            <div class="item banner-3">
                <div class="header-text">
                    <h2>Gérez vos voyages</h2>
                </div>
            </div>
            <div class="item banner-4">
                <div class="header-text">
                    <h2>Voyagez en toute sérénité</h2>
                </div>
            </div>
            <div class="item banner-5">
                <div class="header-text">
                    <h2>Retrouvez vos avantages</h2>
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
                            <a class="nav-link" href="mes_reservations.php">
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
                            <a class="nav-link active" href="profile.php">
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
    <!-- Même structure que index2.php -->
    <div class="container">
    <h1>Mes Réservations</h1>
    
    <?php if (empty($reservations)): ?>
        <div class="alert alert-info">
            Vous n'avez aucune réservation.
        </div>
    <?php else: ?>
        <div class="reservations-table">
            <div class="table-header">
                <div>Itinéraire</div>
                <div>Vol</div>
                <div>Date</div>
                <div>Passagers</div>
                <div>Statut</div>
            </div>
            <?php foreach ($reservations as $reservation): ?>
            <div class="table-row">
                <div class="table-cell">
                    <strong><?php echo htmlspecialchars($reservation['depart']); ?> → <?php echo htmlspecialchars($reservation['arrivee']); ?></strong>
                    <br>
                    <small>Sièges: <?php echo $reservation['sieges'] ?? 'Non assignés'; ?></small>
                    <br>
                    <strong><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€</strong>
                </div>
                <div class="table-cell"><?php echo htmlspecialchars($reservation['code_compagnie'] . $reservation['numero_vol']); ?></div>
                <div class="table-cell"><?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?></div>
                <div class="table-cell"><?php echo $reservation['nombre_passagers']; ?></div>
                <div class="table-cell">
                    <span class="status status-<?php echo $reservation['statut']; ?>">
                        <?php echo ucfirst($reservation['statut']); ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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
        });
    </script>
</body>
</html>
