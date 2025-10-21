<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: connexion.php');
    exit;
}

// Récupérer tous les paiements de l'utilisateur
try {
    $stmt = $pdo->prepare("
        SELECT p.*, r.id_reservation, v.depart, v.arrivee, v.date_depart,
               v.numero_vol, c.nom_compagnie, c.code_compagnie
        FROM paiements p
        JOIN reservations r ON p.id_reservation = r.id_reservation
        JOIN vols v ON r.id_vol = v.id_vol
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie
        WHERE r.id_user = ?
        ORDER BY p.date_paiement DESC
    ");
    $stmt->execute([$_SESSION['id_user']]);
    $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $paiements = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Paiements - JetReserve</title>
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
                    <h2>Mes Paiements</h2>
                </div>
            </div>
            <div class="item banner-2">
                <div class="header-text">
                    <h2>Historique des transactions</h2>
                </div>
            </div>
            <div class="item banner-3">
                <div class="header-text">
                    <h2>Gérez vos factures</h2>
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
                            <a class="nav-link active" href="mes_paiements.php">
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
                        <i class="fas fa-credit-card me-2"></i>
                        Mes Paiements
                    </h1>
                    <div class="filter-options">
                        <select class="form-select" id="statusFilter">
                            <option value="all">Tous les statuts</option>
                            <option value="réussi">Réussis</option>
                            <option value="en attente">En attente</option>
                            <option value="échoué">Échoués</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($paiements)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        Vous n'avez effectué aucun paiement pour le moment.
                        <a href="../index.php" class="alert-link">Réserver un vol</a>
                    </div>
                <?php else: ?>
                    <div class="paiements-container">
                        <div class="paiements-header">
                            <h2>Historique des paiements</h2>
                            <span class="badge bg-primary"><?php echo count($paiements); ?> paiement(s)</span>
                        </div>

                        <div class="paiements-list">
                            <?php foreach ($paiements as $paiement): ?>
                                <div class="paiement-card" data-status="<?php echo $paiement['statut']; ?>">
                                    <div class="paiement-header">
                                        <div class="paiement-info">
                                            <div class="paiement-icon">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <div>
                                                <div class="paiement-reference">
                                                    Référence: #PAY-<?php echo str_pad($paiement['id_paiement'], 6, '0', STR_PAD_LEFT); ?>
                                                </div>
                                                <div class="vol-info">
                                                    <?php echo htmlspecialchars($paiement['depart']); ?> → 
                                                    <?php echo htmlspecialchars($paiement['arrivee']); ?> • 
                                                    <?php echo htmlspecialchars($paiement['code_compagnie'] . $paiement['numero_vol']); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="paiement-status status-<?php echo $paiement['statut']; ?>">
                                            <?php echo ucfirst($paiement['statut']); ?>
                                        </div>
                                    </div>

                                    <div class="paiement-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Date du paiement</span>
                                            <span class="detail-value">
                                                <?php echo date('d/m/Y H:i', strtotime($paiement['date_paiement'])); ?>
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Date du vol</span>
                                            <span class="detail-value">
                                                <?php echo date('d/m/Y', strtotime($paiement['date_depart'])); ?>
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Méthode de paiement</span>
                                            <span class="detail-value">
                                                <?php echo ucfirst($paiement['mode_paiement']); ?>
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Réservation</span>
                                            <span class="detail-value">
                                                #RES-<?php echo str_pad($paiement['id_reservation'], 6, '0', STR_PAD_LEFT); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="paiement-footer">
                                        <div class="montant">
                                            <strong><?php echo number_format($paiement['montant'], 2, ',', ' '); ?> €</strong>
                                        </div>
                                        <!-- SUPPRESSION DES BOUTONS FACTURE ET TÉLÉCHARGER -->
                                        <?php if ($paiement['statut'] === 'en attente'): ?>
                                            <div class="paiement-actions">
                                                <button class="btn btn-warning btn-sm">
                                                    <i class="fas fa-redo me-1"></i>Réessayer
                                                </button>
                                            </div>
                                        <?php endif; ?>
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
                $('.paiement-card').show();
                
                if (status !== 'all') {
                    $('.paiement-card').not('[data-status="' + status + '"]').hide();
                }
            });
        });
    </script>
</body>
</html>