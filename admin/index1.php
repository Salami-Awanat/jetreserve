<?php
session_start();
require_once '../includes/connexion.php';

// Vérification si l'utilisateur est connecté et est un administrateur
// À implémenter selon votre système d'authentification

// Récupération des statistiques
// Nombre total de vols réservés
$stmt = $bdd->query("SELECT COUNT(*) as total_reservations FROM " . TABLE_RESERVATIONS);
$total_reservations = $stmt->fetch(PDO::FETCH_ASSOC)['total_reservations'] ?? 0;

// Revenus totaux
$stmt = $bdd->query("SELECT SUM(montant) as revenus_totaux FROM " . TABLE_PAIEMENTS . " WHERE statut = 'réussi'");
$revenus_totaux = $stmt->fetch(PDO::FETCH_ASSOC)['revenus_totaux'] ?? 0;

// Clients actifs
$stmt = $bdd->query("SELECT COUNT(DISTINCT id_user) as clients_actifs FROM " . TABLE_RESERVATIONS);
$clients_actifs = $stmt->fetch(PDO::FETCH_ASSOC)['clients_actifs'] ?? 0;

// Nouveaux clients (inscrits dans les 30 derniers jours)
$stmt = $bdd->query("SELECT COUNT(*) as nouveaux_clients FROM " . TABLE_USERS . " WHERE date_creation >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$nouveaux_clients = $stmt->fetch(PDO::FETCH_ASSOC)['nouveaux_clients'] ?? 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord administrateur - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style2.css">
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
                    <?php echo $_SESSION['prenom'] ?? 'Admin'; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="index1.php"><i class="fas fa-home me-2"></i>Accueil admin</a></li>
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                  
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
                    <h2>Connectez-vous à votre compte</h2>
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
                        <h2 class="text-white">JetReserve</h2>
                        <p class="text-white">Administration</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index1.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="utilisateurs.php">
                                <i class="fas fa-users me-2"></i>
                                Gestion des utilisateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="vol.php">
                                <i class="fas fa-plane me-2"></i>
                                Gestion des vols
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="paiements.php">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Suivi des paiements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="messages.php">
                                <i class="fas fa-envelope me-2"></i>
                                Support & messages
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link text-danger" href="../index.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tableau de bord</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Exporter</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Imprimer</button>
                        </div>
                    </div>
                </div>

                <!-- Statistiques générales -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Vols réservés</h6>
                                        <h2 class="card-text"><?php echo $total_reservations; ?></h2>
                                    </div>
                                    <i class="fas fa-plane fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Revenus totaux</h6>
                                        <h2 class="card-text"><?php echo number_format($revenus_totaux, 2, ',', ' '); ?> €</h2>
                                    </div>
                                    <i class="fas fa-euro-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Clients actifs</h6>
                                        <h2 class="card-text"><?php echo $clients_actifs; ?></h2>
                                    </div>
                                    <i class="fas fa-user-check fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Nouveaux clients</h6>
                                        <h2 class="card-text"><?php echo $nouveaux_clients; ?></h2>
                                    </div>
                                    <i class="fas fa-user-plus fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dernières réservations -->
                <h3 class="mb-3">Dernières réservations</h3>
                <div class="table-responsive mb-4">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Vol</th>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Récupération des dernières réservations
                            $stmt = $bdd->query("
                               SELECT r.id_reservation, u.nom, u.prenom, v.depart, v.arrivee,
                                      r.date_reservation, p.montant, p.statut
                                      FROM reservations r
                                      JOIN users u ON r.id_user = u.id_user
                                      JOIN vols v ON r.id_vol = v.id_vol
                                      LEFT JOIN paiements p ON r.id_reservation = p.id_reservation
                                      ORDER BY r.date_reservation DESC
                                      LIMIT 5

                            ");
                            
                            while ($reservation = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $statut_class = match($reservation['statut'] ?? 'en attente') {
                                    'réussi' => 'success',
                                    'en attente' => 'warning',
                                    'échoué' => 'danger',
                                    default => 'secondary'
                                };
                
                                echo '<tr>';
                                echo '<td>' . $reservation['id_reservation'] . '</td>';
                                echo '<td>' . htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']) . '</td>';
                                echo '<td>' . htmlspecialchars($reservation['depart'] . ' → ' . $reservation['arrivee']) . '</td>';
                                echo '<td>' . date('d/m/Y', strtotime($reservation['date_reservation'])) . '</td>';
                                echo '<td>' . number_format($reservation['montant'] ?? 0, 2, ',', ' ') . ' €</td>';
                                echo '<td><span class="badge bg-' . $statut_class . '">' . ($reservation['statut'] ?? 'en attente') . '</span></td>';
                                echo '</tr>';
                            }
                           
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Vols populaires -->
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="mb-3">Vols les plus populaires</h3>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Vol</th>
                                        <th>Trajet</th>
                                        <th>Réservations</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Récupération des vols les plus populaires
                                    $stmt = $bdd->query("
                                        SELECT c.nom_compagnie, v.depart, v.arrivee, COUNT(r.id_reservation) AS nb_reservations
                                        FROM vols v
                                        JOIN compagnies c ON v.id_compagnie = c.id_compagnie
                                        JOIN reservations r ON v.id_vol = r.id_vol
                                        GROUP BY v.id_vol
                                        ORDER BY nb_reservations DESC
                                        LIMIT 5
                                      ");
                                    
                                      while ($vol = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($vol['nom_compagnie']) . '</td>';
                                        echo '<td>' . htmlspecialchars($vol['depart'] . ' → ' . $vol['arrivee']) . '</td>';
                                        echo '<td>' . $vol['nb_reservations'] . '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h3 class="mb-3">Derniers paiements</h3>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Récupération des derniers paiements
                                    $stmt = $bdd->query("
                                      SELECT p.id_paiement, u.nom, u.prenom, p.date_paiement, p.montant, p.statut
                                      FROM paiements p
                                      JOIN reservations r ON p.id_reservation = r.id_reservation
                                      JOIN users u ON r.id_user = u.id_user
                                      ORDER BY p.date_paiement DESC
                                      LIMIT 5
                                    ");
                                    
                                    while ($paiement = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $statut_class = match($paiement['statut']) {
                                            'réussi' => 'success',
                                            'en attente' => 'warning',
                                            'échoué' => 'danger',
                                            default => 'secondary'
                                        };
                                    
                                        echo '<tr>';
                                        echo '<td>' . $paiement['id_paiement'] . '</td>';
                                        echo '<td>' . htmlspecialchars($paiement['prenom'] . ' ' . $paiement['nom']) . '</td>';
                                        echo '<td>' . date('d/m/Y', strtotime($paiement['date_paiement'])) . '</td>';
                                        echo '<td>' . number_format($paiement['montant'], 2, ',', ' ') . ' €</td>';
                                        echo '<td><span class="badge bg-' . $statut_class . '">' . $paiement['statut'] . '</span></td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
   