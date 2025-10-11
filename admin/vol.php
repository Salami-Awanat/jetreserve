<?php
session_start();
require_once '../includes/connexion.php';

// Traitement des actions CRUD
if (isset($_POST['action'])) {
    // Ajout d'un vol
    if ($_POST['action'] == 'ajouter') {
        $id_compagnie = $_POST['id_compagnie'];
        $depart = $_POST['depart'];
        $arrivee = $_POST['arrivee'];
        $date_depart = $_POST['date_depart'];
        $date_arrivee = $_POST['date_arrivee'];
        $prix = $_POST['prix'];
        $escales = $_POST['escales'];
        $classe = $_POST['classe'];
        
        $stmt = $bdd->prepare("INSERT INTO vols (id_compagnie, depart, arrivee, date_depart, date_arrivee, prix, escales, classe)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_compagnie, $depart, $arrivee, $date_depart, $date_arrivee, $prix, $escales, $classe]);
        $message = "Vol ajouté avec succès";
    }

    // Modification d'un vol
    if ($_POST['action'] == 'modifier' && isset($_POST['id_vol'])) {
        $id_vol = $_POST['id_vol'];
        $id_compagnie = $_POST['id_compagnie'];
        $depart = $_POST['depart'];
        $arrivee = $_POST['arrivee'];
        $date_depart = $_POST['date_depart'];
        $date_arrivee = $_POST['date_arrivee'];
        $prix = $_POST['prix'];
        $escales = $_POST['escales'];
        $classe = $_POST['classe'];

        $stmt = $bdd->prepare("UPDATE vols 
                               SET id_compagnie=?, depart=?, arrivee=?, date_depart=?, date_arrivee=?, prix=?, escales=?, classe=?
                               WHERE id_vol=?");
        $stmt->execute([$id_compagnie, $depart, $arrivee, $date_depart, $date_arrivee, $prix, $escales, $classe, $id_vol]);
        $message = "Vol modifié avec succès";
    }

    // Suppression d'un vol
    if ($_POST['action'] == 'supprimer' && isset($_POST['id_vol'])) {
        $id_vol = $_POST['id_vol'];
        
        // Vérifier s’il existe des réservations associées
        $stmt = $bdd->prepare("SELECT COUNT(*) FROM reservations WHERE id_vol = ?");
        $stmt->execute([$id_vol]);
        if ($stmt->fetchColumn() > 0) {
            $message_erreur = "Impossible de supprimer ce vol car il possède des réservations.";
        } else {
            $stmt = $bdd->prepare("DELETE FROM vols WHERE id_vol = ?");
            $stmt->execute([$id_vol]);
            $message = "Vol supprimé avec succès";
        }
    }
}

// Récupération des vols avec jointure compagnies
$stmt = $bdd->query("SELECT v.*, c.nom_compagnie 
                     FROM vols v 
                     INNER JOIN compagnies c ON v.id_compagnie = c.id_compagnie
                     ORDER BY v.date_depart");
$vols = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des compagnies
$stmt = $bdd->query("SELECT * FROM compagnies ORDER BY nom_compagnie");
$compagnies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des vols - JetReserve</title>
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
                    <li><a class="dropdown-item text-danger" href="../deconnexion.php">
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
                            <a class="nav-link" href="utilisateurs.php">
                                <i class="fas fa-users me-2"></i>
                                Gestion des utilisateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="vol.php">
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
                    <h1 class="h2">Gestion des vols</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajouterVolModal">
                            <i class="fas fa-plus me-2"></i>Ajouter un vol
                        </button>
                    </div>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($message_erreur)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $message_erreur; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Filtres -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Filtres</h5>
                                <form class="row g-3">
                                    <div class="col-md-3">
                                    <label for="filtreCompagnie" class="form-label">Compagnie</label>
                                    <select class="form-select" id="filtreCompagnie">
                                        <option value="">Toutes les compagnies</option>
                                        <?php foreach ($compagnies as $compagnie): ?>
                                         <option value="<?php echo $compagnie['id_compagnie']; ?>">
                                        <?php echo htmlspecialchars($compagnie['nom_compagnie']); ?>
                                         </option>
                                        <?php endforeach; ?>
                                    </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filtreDepart" class="form-label">Ville de départ</label>
                                        <input type="text" class="form-control" id="filtreDepart">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filtreArrivee" class="form-label">Ville d'arrivée</label>
                                        <input type="text" class="form-control" id="filtreArrivee">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filtreDate" class="form-label">Date de départ</label>
                                        <input type="date" class="form-control" id="filtreDate">
                                    </div>
                                    <div class="col-12 mt-3">
                                        <button type="button" class="btn btn-primary" id="appliquerFiltres">Appliquer les filtres</button>
                                        <button type="button" class="btn btn-secondary" id="reinitialiserFiltres">Réinitialiser</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des vols -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>N° Vol</th>
                                <th>Compagnie</th>
                                <th>Trajet</th>
                                <th>Départ</th>
                                <th>Arrivée</th>
                                <th>Prix</th>
                                <th>Escales</th>
                                <th>Classe</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                     <tbody>
                      <?php foreach ($vols as $vol): ?>
                        <tr>
                          <td><?= $vol['id_vol']; ?></td>
                          <td><?= htmlspecialchars($vol['nom_compagnie']); ?></td>
                          <td><?= htmlspecialchars($vol['depart'] . ' → ' . $vol['arrivee']); ?></td>
                          <td><?= date('d/m/Y', strtotime($vol['date_depart'])); ?></td>
                         <td><?= date('d/m/Y', strtotime($vol['date_arrivee'])); ?></td>
                         <td><?= number_format($vol['prix'], 2, ',', ' ') ?> €</td>
                         <td><?= $vol['escales']; ?></td>
                         <td><?= ucfirst($vol['classe']); ?></td>
                         <td>
                         <!-- Boutons d’action -->
                         <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                            data-bs-target="#modifierVolModal<?= $vol['id_vol']; ?>">
                           <i class="fas fa-edit"></i>
                          </button>
                          <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                             data-bs-target="#supprimerVolModal<?= $vol['id_vol']; ?>">
                             <i class="fas fa-trash"></i>
                          </button>
                         </td>
                         </td>
                        </tr>
                      <?php endforeach; ?>
                      </tbody>
                    </table>
                </div>

        <!-- Modal MODIFIER -->
        <?php foreach ($vols as $vol): ?>
        <div class="modal fade" id="modifierVolModal<?= $vol['id_vol']; ?>" tabindex="-1"
             aria-labelledby="modifierVolModalLabel<?= $vol['id_vol']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier le vol #<?= $vol['id_vol']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="modifier">
                        <input type="hidden" name="id_vol" value="<?= $vol['id_vol']; ?>">

                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Compagnie</label>
                                    <select name="id_compagnie" class="form-select" required>
                                        <?php foreach ($compagnies as $comp): ?>
                                            <option value="<?= $comp['id_compagnie']; ?>"
                                                <?= $comp['id_compagnie'] == $vol['id_compagnie'] ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($comp['nom_compagnie']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Classe</label>
                                    <select name="classe" class="form-select">
                                        <option value="économique" <?= $vol['classe']=='économique'?'selected':''; ?>>Économique</option>
                                        <option value="affaires" <?= $vol['classe']=='affaires'?'selected':''; ?>>Affaires</option>
                                        <option value="première" <?= $vol['classe']=='première'?'selected':''; ?>>Première</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Départ</label>
                                    <input type="text" name="depart" value="<?= htmlspecialchars($vol['depart']); ?>" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Arrivée</label>
                                    <input type="text" name="arrivee" value="<?= htmlspecialchars($vol['arrivee']); ?>" class="form-control" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Date & heure de départ</label>
                                    <input type="datetime-local" name="date_depart"
                                           value="<?= date('Y-m-d\TH:i', strtotime($vol['date_depart'])); ?>"
                                           class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date & heure d’arrivée</label>
                                    <input type="datetime-local" name="date_arrivee"
                                           value="<?= date('Y-m-d\TH:i', strtotime($vol['date_arrivee'])); ?>"
                                           class="form-control" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Prix (€)</label>
                                    <input type="number" step="0.01" min="0" name="prix" value="<?= $vol['prix']; ?>" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nombre d’escales</label>
                                    <input type="number" min="0" name="escales" value="<?= $vol['escales']; ?>" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

               <!-- Modal SUPPRIMER -->
             <div class="modal fade" id="supprimerVolModal<?= $vol['id_vol']; ?>" tabindex="-1" aria-hidden="true">
                 <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Confirmer la suppression</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                     </div>
                     <div class="modal-body">
                        Voulez-vous vraiment supprimer le vol <strong><?= htmlspecialchars($vol['depart'] . " → " . $vol['arrivee']); ?></strong> ?
                     </div>
                     <div class="modal-footer">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="id_vol" value="<?= $vol['id_vol']; ?>">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                     </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </tbody>

                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Précédent</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Suivant</a>
                        </li>
                    </ul>
                </nav>
            </main>
        </div>
    </div>

    <!-- Modal Ajouter Vol -->
    <div class="modal fade" id="ajouterVolModal" tabindex="-1" aria-labelledby="ajouterVolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ajouterVolModalLabel">Ajouter un nouveau vol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="ajouter">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="numero_vol" class="form-label">Numéro de vol</label>
                                <input type="text" class="form-control" id="numero_vol" name="numero_vol" required>
                            </div>
                            <div class="col-md-6">
                                <label for="compagnie" class="form-label">Compagnie</label>
                                <input type="text" class="form-control" id="compagnie" name="compagnie" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="ville_depart" class="form-label">Ville de départ</label>
                                <input type="text" class="form-control" id="ville_depart" name="ville_depart" required>
                            </div>
                            <div class="col-md-6">
                                <label for="ville_arrivee" class="form-label">Ville d'arrivée</label>
                                <input type="text" class="form-control" id="ville_arrivee" name="ville_arrivee" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_depart" class="form-label">Date de départ</label>
                                <input type="date" class="form-control" id="date_depart" name="date_depart" required>
                            </div>
                            <div class="col-md-6">
                                <label for="heure_depart" class="form-label">Heure de départ</label>
                                <input type="time" class="form-control" id="heure_depart" name="heure_depart" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_arrivee" class="form-label">Date d'arrivée</label>
                                <input type="date" class="form-control" id="date_arrivee" name="date_arrivee" required>
                            </div>
                            <div class="col-md-6">
                                <label for="heure_arrivee" class="form-label">Heure d'arrivée</label>
                                <input type="time" class="form-control" id="heure_arrivee" name="heure_arrivee" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="prix" class="form-label">Prix</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control" id="prix" name="prix" required>
                                    <span class="input-group-text">€</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="places_disponibles" class="form-label">Places disponibles</label>
                                <input type="number" min="0" class="form-control" id="places_disponibles" name="places_disponibles" required>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
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
        // Script pour les filtres
        document.getElementById('appliquerFiltres').addEventListener('click', function() {
            const compagnie = document.getElementById('filtreCompagnie').value.toLowerCase();
            const depart = document.getElementById('filtreDepart').value.toLowerCase();
            const arrivee = document.getElementById('filtreArrivee').value.toLowerCase();
            const date = document.getElementById('filtreDate').value;
            
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const rowCompagnie = row.cells[2].textContent.toLowerCase();
                const rowTrajet = row.cells[3].textContent.toLowerCase();
                const rowDate = row.cells[4].textContent.split(' ')[0]; // Format dd/mm/yyyy
                
                let dateMatch = true;
                if (date) {
                    const dateParts = rowDate.split('/');
                    const rowDateFormatted = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`; // Format yyyy-mm-dd
                    dateMatch = rowDateFormatted === date;
                }
                
                const compagnieMatch = !compagnie || rowCompagnie.includes(compagnie);
                const departMatch = !depart || rowTrajet.split(' - ')[0].toLowerCase().includes(depart);
                const arriveeMatch = !arrivee || rowTrajet.split(' - ')[1].toLowerCase().includes(arrivee);
                
                if (compagnieMatch && departMatch && arriveeMatch && dateMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        document.getElementById('reinitialiserFiltres').addEventListener('click', function() {
            document.getElementById('filtreCompagnie').value = '';
            document.getElementById('filtreDepart').value = '';
            document.getElementById('filtreArrivee').value = '';
            document.getElementById('filtreDate').value = '';
            
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.style.display = '';
            });
        });
    </script>
</body>
</html>