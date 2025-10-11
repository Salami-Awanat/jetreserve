<?php
require_once '../includes/connexion.php';

// Traitement du filtre par statut
$statut_filter = isset($_GET['statut']) ? $_GET['statut'] : '';
$where_clause = '';

if (!empty($statut_filter)) {
    $where_clause = " WHERE p.statut = :statut";
}

// Récupération des paiements
$query = "SELECT
          p.*, r.id_reservation, u.id_user, u.nom, u.prenom, u.email, v.depart, v.arrivee, v.date_depart
          FROM " . TABLE_PAIEMENTS . " p
          JOIN " . TABLE_RESERVATIONS . " r ON p.id_reservation = r.id_reservation
          JOIN " . TABLE_USERS . " u ON r.id_user = u.id_user
          JOIN " . TABLE_VOLS . " v ON r.id_vol = v.id_vol" . $where_clause . "
          ORDER BY p.date_paiement DESC";

$stmt = $bdd->prepare($query);

if (!empty($statut_filter)) {
    $stmt->bindParam(':statut', $statut_filter, PDO::PARAM_STR);
}

$stmt->execute();
$paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul des statistiques
$stmt = $bdd->query("SELECT COUNT(*) as total, SUM(montant) as revenu_total FROM " . TABLE_PAIEMENTS);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $bdd->query("SELECT COUNT(*) as total FROM " . TABLE_PAIEMENTS . " WHERE statut = 'réussi'");
$reussis = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $bdd->query("SELECT COUNT(*) as total FROM " . TABLE_PAIEMENTS . " WHERE statut = 'en attente'");
$en_attente = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $bdd->query("SELECT COUNT(*) as total FROM " . TABLE_PAIEMENTS . " WHERE statut = 'échoué'");
$echoues = $stmt->fetch(PDO::FETCH_ASSOC);

// Mise à jour du statut de paiement
if (isset($_POST['update_status'])) {
    $id_paiement = $_POST['id_paiement'];
    $nouveau_statut = $_POST['nouveau_statut'];
    
    $stmt = $bdd->prepare("UPDATE " . TABLE_PAIEMENTS . " SET statut = :statut WHERE id_paiement = :id");
    $stmt->bindParam(':statut', $nouveau_statut, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id_paiement, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Si le paiement est réussi, mettre à jour le statut de la réservation
        if ($nouveau_statut == 'réussi') {
            $stmt = $bdd->prepare("UPDATE " . TABLE_RESERVATIONS . " SET statut = 'confirmé' 
                                  WHERE id_reservation = (SELECT id_reservation FROM " . TABLE_PAIEMENTS . " WHERE id_paiement = :id)");
            $stmt->bindParam(':id', $id_paiement, PDO::PARAM_INT);
            $stmt->execute();
        }
        
        // Redirection pour éviter la soumission multiple du formulaire
        header("Location: paiements.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des Paiements - Administration</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- CSS OwlCarousel IMPORTANT -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="style2.css">
    <style>
        /* Fix pour les modals */
.modal {
    display: none !important;
}

.modal.show {
    display: block !important;
}

.modal-backdrop {
    display: none !important;
}

.modal.show ~ .modal-backdrop {
    display: block !important;
}

/* Fix pour le carousel */
.main-banner .item {
    display: none;
}

.main-banner .item:first-child {
    display: block;
}
/* FORCER l'affichage correct des modals */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 9999;
    width: 100%;
    height: 100%;
    overflow: hidden;
    outline: 0;
    background-color: rgba(0,0,0,0.5);
}

.modal.show {
    display: block !important;
    opacity: 1;
}

.modal-dialog {
    position: relative;
    width: auto;
    margin: 1.75rem auto;
    pointer-events: none;
    max-width: 500px;
}

.modal.show .modal-dialog {
    transform: none;
    pointer-events: auto;
}

.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.2);
    border-radius: .3rem;
    outline: 0;
}

/* Masquer le carousel pendant le debug */
.main-banner-container {
    height: 300px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.main-banner .header-text h2 {
    color: white;
    text-align: center;
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

    <div class="main-banner-container">
    <div id="mainCarousel" class="carousel slide" data-ride="carousel" data-interval="5000">
        <div class="carousel-inner">
            <div class="carousel-item active banner-1">
                <div class="header-text">
                    <h2>Bienvenue chez JetReserve</h2>
                </div>
            </div>
            <div class="carousel-item banner-2">
                <div class="header-text">
                    <h2>Accédez à vos réservations</h2>
                </div>
            </div>
            <div class="carousel-item banner-3">
                <div class="header-text">
                    <h2>Gérez vos voyages</h2>
                </div>
            </div>
            <div class="carousel-item banner-4">
                <div class="header-text">
                    <h2>Voyagez en toute sérénité</h2>
                </div>
            </div>
            <div class="carousel-item banner-5">
                <div class="header-text">
                    <h2>Retrouvez vos avantages</h2>
                </div>
            </div>
        </div>
    </div>
</div> 

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
                <div class="sidebar-sticky">
                    <div class="text-center my-4">
                        <h4 class="text-white">JetReserve Admin</h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index1.php">
                                <i class="fas fa-tachometer-alt mr-2"></i>
                                Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="vol.php">
                                <i class="fas fa-plane mr-2"></i>
                                Gestion des vols
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="utilisateurs.php">
                                <i class="fas fa-users mr-2"></i>
                                Gestion des utilisateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="paiements.php">
                                <i class="fas fa-money-bill-wave mr-2"></i>
                                Suivi des paiements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="messages.php">
                                <i class="fas fa-envelope mr-2"></i>
                                Support & Messages
                            </a>
                        </li>
                     
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Suivi des Paiements</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-filter mr-1"></i> Filtrer par statut
                            </button>
                            <div class="dropdown-menu" aria-labelledby="filterDropdown">
                                <a class="dropdown-item <?php echo $statut_filter == '' ? 'active' : ''; ?>" href="paiements.php">Tous</a>
                                <a class="dropdown-item <?php echo $statut_filter == 'réussi' ? 'active' : ''; ?>" href="paiements.php?statut=réussi">Réussi</a>
                                <a class="dropdown-item <?php echo $statut_filter == 'en attente' ? 'active' : ''; ?>" href="paiements.php?statut=en attente">En attente</a>
                                <a class="dropdown-item <?php echo $statut_filter == 'échoué' ? 'active' : ''; ?>" href="paiements.php?statut=échoué">Échoué</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques des paiements -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Paiements réussis</h5>
                                <p class="card-text display-4"><?php echo $reussis['total']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">En attente</h5>
                                <p class="card-text display-4"><?php echo $en_attente['total']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">Échoués</h5>
                                <p class="card-text display-4"><?php echo $echoues['total']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Revenus totaux</h5>
                                <p class="card-text display-4"><?php echo number_format($stats['revenu_total'], 2, ',', ' '); ?> €</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des paiements -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Liste des paiements</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                      
                                        <th>Client</th>
                                        <th>Vol</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Mode</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($paiements)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Aucun paiement trouvé</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($paiements as $paiement): ?>
                                            <tr>
                                                
                                                <td>
                                                    <a href="utilisateurs.php?id=<?php echo $paiement['id_user']; ?>" title="Voir le profil">
                                                        <?php echo $paiement['nom'] . ' ' . $paiement['prenom']; ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php echo $paiement['depart'] . ' → ' . $paiement['arrivee']; ?>
                                                    <small class="d-block text-muted">
                                                        <?php echo date('d/m/Y', strtotime($paiement['date_depart'])); ?>
                                                    </small>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($paiement['date_paiement'])); ?></td>
                                                <td><?php echo number_format($paiement['montant'], 2, ',', ' '); ?> €</td>
                                                <td>
                                                    <?php 
                                                        switch($paiement['mode_paiement']) {
                                                            case 'carte':
                                                                echo '<span class="badge badge-primary">Carte bancaire</span>';
                                                                break;
                                                            case 'mobile money':
                                                                echo '<span class="badge badge-info">Mobile Money</span>';
                                                                break;
                                                            case 'paypal':
                                                                echo '<span class="badge badge-secondary">PayPal</span>';
                                                                break;
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        switch($paiement['statut']) {
                                                            case 'réussi':
                                                                echo '<span class="badge badge-success">Réussi</span>';
                                                                break;
                                                            case 'en attente':
                                                                echo '<span class="badge badge-warning">En attente</span>';
                                                                break;
                                                            case 'échoué':
                                                                echo '<span class="badge badge-danger">Échoué</span>';
                                                                break;
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#detailModal<?php echo $paiement['id_paiement']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#statusModal<?php echo $paiement['id_paiement']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal Détails -->
                                            <div class="modal fade" id="detailModal<?php echo $paiement['id_paiement']; ?>" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel<?php echo $paiement['id_paiement']; ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="detailModalLabel<?php echo $paiement['id_paiement']; ?>">Détails du paiement #<?php echo $paiement['id_paiement']; ?></h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p><strong>ID Paiement:</strong> <?php echo $paiement['id_paiement']; ?></p>
                                                                    <p><strong>ID Réservation:</strong> <?php echo $paiement['id_reservation']; ?></p>
                                                                    <p><strong>Client:</strong> <?php echo $paiement['nom'] . ' ' . $paiement['prenom']; ?></p>
                                                                    <p><strong>Email:</strong> <?php echo $paiement['email']; ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p><strong>Montant:</strong> <?php echo number_format($paiement['montant'], 2, ',', ' '); ?> €</p>
                                                                    <p><strong>Mode:</strong> <?php echo $paiement['mode_paiement']; ?></p>
                                                                    <p><strong>Statut:</strong> <?php echo $paiement['statut']; ?></p>
                                                                    <p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($paiement['date_paiement'])); ?></p>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <h6>Détails du vol</h6>
                                                            <p><strong>Trajet:</strong> <?php echo $paiement['depart'] . ' → ' . $paiement['arrivee']; ?></p>
                                                            <p><strong>Date de départ:</strong> <?php echo date('d/m/Y H:i', strtotime($paiement['date_depart'])); ?></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Changement de statut -->
                                            <div class="modal fade" id="statusModal<?php echo $paiement['id_paiement']; ?>" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel<?php echo $paiement['id_paiement']; ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="statusModalLabel<?php echo $paiement['id_paiement']; ?>">Modifier le statut du paiement #<?php echo $paiement['id_paiement']; ?></h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form method="post" action="">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id_paiement" value="<?php echo $paiement['id_paiement']; ?>">
                                                                <div class="form-group">
                                                                    <label for="nouveau_statut">Nouveau statut</label>
                                                                    <select class="form-control" id="nouveau_statut" name="nouveau_statut">
                                                                        <option value="réussi" <?php echo $paiement['statut'] == 'réussi' ? 'selected' : ''; ?>>Réussi</option>
                                                                        <option value="en attente" <?php echo $paiement['statut'] == 'en attente' ? 'selected' : ''; ?>>En attente</option>
                                                                        <option value="échoué" <?php echo $paiement['statut'] == 'échoué' ? 'selected' : ''; ?>>Échoué</option>
                                                                    </select>
                                                                </div>
                                                                <div class="alert alert-info">
                                                                    <small>
                                                                        <i class="fas fa-info-circle mr-1"></i>
                                                                        Si vous changez le statut en "Réussi", la réservation associée sera automatiquement confirmée.
                                                                    </small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                                <button type="submit" name="update_status" class="btn btn-primary">Enregistrer</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function(){
    // Désactiver COMPLÈTEMENT le carousel pour tester
    $('.main-banner .item').first().show();
    
    // Empêcher les événements en double sur les modals
    $('.modal').off('show.bs.modal hidden.bs.modal');
    
    // Empêcher la propagation des clics sur les boutons
    $('button[data-toggle="modal"]').off('click').on('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        var target = $(this).attr('data-target');
        $(target).modal('show');
        return false;
    });
    
    // Fermer proprement les modals
    $('.modal .close, .modal button[data-dismiss="modal"]').off('click').on('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('.modal').modal('hide');
        return false;
    });
});
</script>
</body>
</html>