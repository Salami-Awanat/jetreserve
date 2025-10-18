<?php
session_start();
require_once '../includes/db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: connexion.php');
    exit;
}

// Récupérer les réservations de l'utilisateur
try {
    $stmt = $pdo->prepare("
        SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, 
               v.numero_vol, c.nom_compagnie, c.code_compagnie,
               COUNT(rs.id_siege) as nb_sieges
        FROM reservations r
        JOIN vols v ON r.id_vol = v.id_vol
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie
        LEFT JOIN reservation_sieges rs ON r.id_reservation = rs.id_reservation
        WHERE r.id_user = ?
        GROUP BY r.id_reservation
        ORDER BY r.date_reservation DESC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['id_user']]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $reservations = [];
}

// Récupérer les paiements de l'utilisateur
try {
    $stmt = $pdo->prepare("
        SELECT p.*, r.id_reservation, v.depart, v.arrivee
        FROM paiements p
        JOIN reservations r ON p.id_reservation = r.id_reservation
        JOIN vols v ON r.id_vol = v.id_vol
        WHERE r.id_user = ?
        ORDER BY p.date_paiement DESC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['id_user']]);
    $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $paiements = [];
}

// Compter le nombre total de réservations
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservations WHERE id_user = ?");
    $stmt->execute([$_SESSION['id_user']]);
    $total_reservations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $total_reservations = 0;
}

// Compter le nombre total de paiements
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM paiements p 
        JOIN reservations r ON p.id_reservation = r.id_reservation 
        WHERE r.id_user = ?
    ");
    $stmt->execute([$_SESSION['id_user']]);
    $total_paiements = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $total_paiements = 0;
}

// Récupérer les messages (simulation)
$total_messages = 2; // À remplacer par une vraie requête si vous avez une table messages
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style1.css">
    <style>
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border-left: 4px solid #2563eb;
        }

        .stats-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 1.5rem;
        }

        .icon-reservations { background: #2563eb; }
        .icon-paiements { background: #10b981; }
        .icon-messages { background: #f59e0b; }
        .icon-profil { background: #8b5cf6; }

        .stats-card h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #1e293b;
        }

        .stats-card p {
            color: #64748b;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .stats-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #2563eb;
        }

        .trip-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-left: 4px solid #2563eb;
        }

        .trip-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .trip-destination {
            font-weight: 600;
            color: #1e293b;
        }

        .trip-date {
            color: #64748b;
            font-size: 0.9rem;
        }

        .trip-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .trip-info {
            color: #64748b;
            font-size: 0.9rem;
        }

        .trip-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }

        .activity-item {
            display: flex;
            align-items: center;
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
        }

        .activity-details {
            flex: 1;
        }

        .activity-details h4 {
            margin: 0;
            font-size: 1rem;
            color: #1e293b;
        }

        .activity-details p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        .activity-time {
            color: #94a3b8;
            font-size: 0.8rem;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            text-decoration: none;
            color: #334155;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .action-btn:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-2px);
        }

        .action-btn i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .welcome-card {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .welcome-card h2 {
            margin-bottom: 10px;
        }

        .upcoming-trips, .recent-activities, .quick-actions {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .upcoming-trips h3, .recent-activities h3, .quick-actions h3 {
            margin-bottom: 20px;
            color: #1e293b;
            display: flex;
            align-items: center;
        }

        .upcoming-trips h3 i, .recent-activities h3 i, .quick-actions h3 i {
            margin-right: 10px;
            color: #2563eb;
        }
    </style>
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
                            <?php echo htmlspecialchars($_SESSION['prenom'] ?? 'Client'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index2.php"><i class="fas fa-home me-2"></i>Accueil client</a></li>
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
                    <h2>Bienvenue chez JetReserve</h2>
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
                            <a class="nav-link active" href="index2.php">
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
                <!-- Tableau de bord client -->
                <div class="container dashboard-container">
                    <!-- Carte de bienvenue -->
                    <div class="welcome-card">
                        <h2>Bonjour, <?php echo htmlspecialchars($_SESSION['prenom'] ?? 'Client'); ?> !</h2>
                        <p>Bienvenue dans votre espace personnel JetReserve. Gérez vos réservations, vos paiements et votre profil en toute simplicité.</p>
                    </div>

                    <div class="row">
                        <!-- Statistiques principales -->
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="icon icon-reservations">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <h3>Mes réservations</h3>
                                <p>Vos vols à venir et passés</p>
                                <div class="number"><?php echo $total_reservations; ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="icon icon-paiements">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <h3>Mes paiements</h3>
                                <p>Historique des transactions</p>
                                <div class="number"><?php echo $total_paiements; ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="icon icon-messages">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <h3>Messages</h3>
                                <p>Support et réclamations</p>
                                <div class="number"><?php echo $total_messages; ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="icon icon-profil">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h3>Mon profil</h3>
                                <p>Informations personnelles</p>
                                <div class="number">1</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Voyages à venir -->
                        <div class="col-lg-8">
                            <div class="upcoming-trips">
                                <h3><i class="fas fa-calendar-alt"></i> Mes prochains voyages</h3>
                                
                                <?php if (empty($reservations)): ?>
                                    <div class="trip-card">
                                        <div class="trip-header">
                                            <div class="trip-destination">Aucune réservation</div>
                                        </div>
                                        <div class="trip-details">
                                            <div class="trip-info">Vous n'avez pas encore de réservation</div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($reservations as $reservation): ?>
                                        <div class="trip-card">
                                            <div class="trip-header">
                                                <div class="trip-destination"><?php echo htmlspecialchars($reservation['depart']); ?> → <?php echo htmlspecialchars($reservation['arrivee']); ?></div>
                                                <div class="trip-date"><?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?></div>
                                            </div>
                                            <div class="trip-details">
                                                <div class="trip-info">
                                                    Vol <?php echo htmlspecialchars($reservation['code_compagnie'] . $reservation['numero_vol']); ?> • 
                                                    <?php echo $reservation['nb_sieges']; ?> siège(s)
                                                </div>
                                                <span class="trip-status status-<?php echo $reservation['statut'] === 'confirmé' ? 'confirmed' : 'pending'; ?>">
                                                    <?php echo ucfirst($reservation['statut']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Activités récentes -->
                            <div class="recent-activities">
                                <h3><i class="fas fa-history"></i> Activités récentes</h3>
                                
                                <?php if (empty($reservations) && empty($paiements)): ?>
                                    <div class="activity-item">
                                        <div class="activity-details">
                                            <h4>Aucune activité récente</h4>
                                            <p>Vos activités apparaîtront ici</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Afficher les réservations récentes -->
                                    <?php foreach (array_slice($reservations, 0, 3) as $reservation): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon" style="background: linear-gradient(135deg, #2563eb, #1a2530);">
                                                <i class="fas fa-plane"></i>
                                            </div>
                                            <div class="activity-details">
                                                <h4>Réservation <?php echo $reservation['statut']; ?></h4>
                                                <p>Vol <?php echo htmlspecialchars($reservation['depart']); ?> → <?php echo htmlspecialchars($reservation['arrivee']); ?></p>
                                            </div>
                                            <div class="activity-time"><?php echo date('d/m/Y', strtotime($reservation['date_reservation'])); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <!-- Afficher les paiements récents -->
                                    <?php foreach (array_slice($paiements, 0, 2) as $paiement): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon" style="background: linear-gradient(135deg, #10b981, #219653);">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <div class="activity-details">
                                                <h4>Paiement <?php echo $paiement['statut']; ?></h4>
                                                <p>Montant: <?php echo number_format($paiement['montant'], 2, ',', ' '); ?>€ - <?php echo ucfirst($paiement['mode_paiement']); ?></p>
                                            </div>
                                            <div class="activity-time"><?php echo date('d/m/Y', strtotime($paiement['date_paiement'])); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Actions rapides -->
                        <div class="col-lg-4">
                            <div class="quick-actions">
                                <h3><i class="fas fa-bolt"></i> Actions rapides</h3>
                                
                                <div class="action-buttons">
                                    <a href="mes_reservations.php" class="action-btn">
                                        <i class="fas fa-plane"></i>
                                        <span>Mes réservations</span>
                                    </a>
                                    
                                    <a href="mes_paiements.php" class="action-btn">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Mes paiements</span>
                                    </a>
                                    
                                    <a href="profile.php" class="action-btn">
                                        <i class="fas fa-user"></i>
                                        <span>Mon profil</span>
                                    </a>
                                    
                                    <a href="messages.php" class="action-btn">
                                        <i class="fas fa-envelope"></i>
                                        <span>Support client</span>
                                    </a>
                                    
                                    <a href="../index.php" class="action-btn">
                                        <i class="fas fa-search"></i>
                                        <span>Rechercher un vol</span>
                                    </a>
                                    
                                    <a href="mes_reservations.php" class="action-btn">
                                        <i class="fas fa-download"></i>
                                        <span>Télécharger billets</span>
                                    </a>
                                </div>
                            </div>
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