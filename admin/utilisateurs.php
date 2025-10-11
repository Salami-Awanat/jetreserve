<?php
session_start();
require_once '../includes/connexion.php';

// Récupération de tous les utilisateurs
$stmt = $bdd->query("SELECT * FROM users ORDER BY date_creation DESC");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des détails d'un utilisateur spécifique si demandé
$utilisateur_details = null;
$reservations_utilisateur = [];

if (isset($_GET['id'])) {
    $id_utilisateur = $_GET['id'];
    
    // Récupération des détails de l'utilisateur
    $stmt = $bdd->prepare("SELECT * FROM users WHERE id_user = ?");
    $stmt->execute([$id_user]);
    $utilisateur_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupération des réservations de l'utilisateur
    $stmt = $bdd->prepare("
        SELECT r.*, v.numero_vol, v.ville_depart, v.ville_arrivee, v.date_depart, v.heure_depart, v.compagnie
        FROM reservations r
        JOIN vols v ON r.id_vol = v.id
        WHERE r.id_users = ?
        ORDER BY r.date_reservation DESC
    ");
    $stmt->execute([$id_user]);
    $reservations_utilisateur = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Traitement de la désactivation/activation d'un compte
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'toggle_status' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $currentStatus = $_POST['status'];

        // Bascule entre 'actif' et 'inactif'
        $newStatus = ($currentStatus === 'actif') ? 'inactif' : 'actif';

        // ✅ Correction du nom de table et des colonnes
        $stmt = $bdd->prepare("UPDATE users SET statut = ? WHERE id_user = ?");
        $stmt->execute([$newStatus, $id]);

        // Redirection pour éviter la soumission multiple du formulaire
        header("Location: utilisateurs.php" . (isset($_GET['id']) ? "?id=" . $_GET['id'] : ""));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs - JetReserve</title>
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
                        <h2 class="text-white">JetReserve</h2>
                        <p class="text-white">Administration</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index1.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="utilisateurs.php">
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
                      
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion des utilisateurs</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchUser" placeholder="Rechercher un utilisateur...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Liste des utilisateurs -->
                    <div class="<?php echo isset($_GET['id']) ? 'col-md-6' : 'col-md-12'; ?>">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Liste des utilisateurs</h5>
                                <span class="badge bg-primary"><?php echo count($utilisateurs); ?> utilisateurs</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                               
                                                <th>Nom</th>
                                                <th>Email</th>
                                                <th>Date d'inscription</th>
                                                <th>Téléphone</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($utilisateurs as $utilisateur): ?>
                                                <tr class="<?php echo isset($_GET['id']) && $_GET['id'] == $utilisateur['id_user'] ? 'table-primary' : ''; ?>">
                                                   
                                                    <td><?php echo $utilisateur['nom'] . ' ' . $utilisateur['prenom']; ?></td>
                                                    <td><?php echo $utilisateur['email']; ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($utilisateur['date_creation'])); ?></td>
                                                    <td><?php echo $utilisateur['telephone']; ?></td>
                                                    <td>
                                                    <?php if ($utilisateur['statut'] === 'actif'): ?>
                                                        <span class="badge bg-success">Actif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactif</span>
                                                    <?php endif; ?>

                                                    </td>

                                                    <td>
                                                      <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="id" value="<?php echo $utilisateur['id_user']; ?>">
                                                        <input type="hidden" name="status" value="<?php echo $utilisateur['statut']; ?>">
                                                        <button type="submit" class="btn btn-sm <?php echo ($utilisateur['statut'] === 'actif') ? 'btn-warning' : 'btn-success'; ?>">
                                                        <?php echo ($utilisateur['statut'] === 'actif') ? 'Désactiver' : 'Activer'; ?>
                                                        </button>
                                                      </form>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails de l'utilisateur et historique des réservations -->
                    <?php if (isset($_GET['id']) && $utilisateur_details): ?>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Détails de l'utilisateur</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>ID:</strong> <?php echo $utilisateur_details['id']; ?></p>
                                            <p><strong>Nom:</strong> <?php echo $utilisateur_details['nom'] . ' ' . $utilisateur_details['prenom']; ?></p>
                                            <p><strong>Email:</strong> <?php echo $utilisateur_details['email']; ?></p>
                                            <p><strong>Téléphone:</strong> <?php echo $utilisateur_details['telephone'] ?? 'Non renseigné'; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Date d'inscription:</strong> <?php echo date('d/m/Y', strtotime($utilisateur_details['date_inscription'])); ?></p>
                                            <p><strong>Statut:</strong> 
                                                <?php if ($utilisateur_details['actif'] == 1): ?>
                                                    <span class="badge bg-success">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactif</span>
                                                <?php endif; ?>
                                            </p>
                                            <p><strong>Dernière connexion:</strong> <?php echo isset($utilisateur_details['derniere_connexion']) ? date('d/m/Y H:i', strtotime($utilisateur_details['derniere_connexion'])) : 'Jamais'; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Historique des réservations</h5>
                                    <span class="badge bg-primary"><?php echo count($reservations_utilisateur); ?> réservations</span>
                                </div>
                                <div class="card-body">
                                    <?php if (count($reservations_utilisateur) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Réf.</th>
                                                        <th>Vol</th>
                                                        <th>Date</th>
                                                        <th>Statut</th>
                                                        <th>Prix</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($reservations_utilisateur as $reservation): ?>
                                                        <tr>
                                                            <td><?php echo $reservation['reference']; ?></td>
                                                            <td>
                                                                <strong><?php echo $reservation['numero_vol']; ?></strong><br>
                                                                <small><?php echo $reservation['ville_depart'] . ' - ' . $reservation['ville_arrivee']; ?></small><br>
                                                                <small class="text-muted"><?php echo $reservation['compagnie']; ?></small>
                                                            </td>
                                                            <td>
                                                                <?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?><br>
                                                                <small><?php echo $reservation['heure_depart']; ?></small>
                                                            </td>
                                                            <td>
                                                                <?php 
                                                                switch ($reservation['statut']) {
                                                                    case 'confirmé':
                                                                        echo '<span class="badge bg-success">Confirmé</span>';
                                                                        break;
                                                                    case 'en attente':
                                                                        echo '<span class="badge bg-warning">En attente</span>';
                                                                        break;
                                                                    case 'annulé':
                                                                        echo '<span class="badge bg-danger">Annulé</span>';
                                                                        break;
                                                                    default:
                                                                        echo '<span class="badge bg-secondary">' . $reservation['statut'] . '</span>';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?> €</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Cet utilisateur n'a pas encore effectué de réservation.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
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



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
    <script>
        // Script pour la recherche d'utilisateurs
        document.getElementById('searchUser').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const name = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                
                if (name.includes(searchValue) || email.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>