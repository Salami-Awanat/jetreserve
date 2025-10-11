<?php
require_once '../includes/connexion.php';

// Récupération des messages (emails)
$stmt = $bdd->query("SELECT e.*, u.nom, u.prenom, u.email 
                     FROM " . TABLE_EMAILS . " e
                     JOIN " . TABLE_USERS . " u ON e.id_user = u.id_user
                     ORDER BY e.date_envoi DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Envoi d'un nouveau message
if (isset($_POST['send_message'])) {
    $id_user = $_POST['id_user'];
    $sujet = $_POST['sujet'];
    $contenu = $_POST['contenu'];
    $type = $_POST['type'];
    
    $stmt = $bdd->prepare("INSERT INTO " . TABLE_EMAILS . " (id_user, sujet, contenu, type) 
                          VALUES (:id_user, :sujet, :contenu, :type)");
    $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->bindParam(':sujet', $sujet, PDO::PARAM_STR);
    $stmt->bindParam(':contenu', $contenu, PDO::PARAM_STR);
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        // Redirection pour éviter la soumission multiple du formulaire
        header("Location: messages.php");
        exit;
    }
}

// Récupération des utilisateurs pour le formulaire d'envoi
$stmt = $bdd->query("SELECT id_user, nom, prenom, email FROM " . TABLE_USERS . " ORDER BY nom, prenom");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support & Messages - Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
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
                            <a class="nav-link" href="paiements.php">
                                <i class="fas fa-money-bill-wave mr-2"></i>
                                Suivi des paiements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="messages.php">
                                <i class="fas fa-envelope mr-2"></i>
                                Support & Messages
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link text-danger" href="../index.php">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Support & Messages</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newMessageModal">
                            <i class="fas fa-plus mr-1"></i> Nouveau message
                        </button>
                    </div>
                </div>

                <!-- Statistiques des messages -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Total des messages</h5>
                                <p class="card-text display-4"><?php echo count($messages); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Messages envoyés</h5>
                                <p class="card-text display-4">
                                    <?php 
                                    $sent = array_filter($messages, function($m) { return $m['statut'] == 'envoyé'; });
                                    echo count($sent);
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">Messages échoués</h5>
                                <p class="card-text display-4">
                                    <?php 
                                    $failed = array_filter($messages, function($m) { return $m['statut'] == 'échoué'; });
                                    echo count($failed);
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des messages -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Historique des messages</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="messagesTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Sujet</th>
                                        <th>Type</th>
                                        <th>Date d'envoi</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($messages)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Aucun message trouvé</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($messages as $message): ?>
                                            <tr>
                                                <td><?php echo $message['id_email']; ?></td>
                                                <td>
                                                    <a href="utilisateurs.php?id=<?php echo $message['id_user']; ?>" title="Voir le profil">
                                                        <?php echo $message['nom'] . ' ' . $message['prenom']; ?>
                                                    </a>
                                                </td>
                                                <td><?php echo $message['sujet']; ?></td>
                                                <td>
                                                    <?php 
                                                        switch($message['type']) {
                                                            case 'confirmation':
                                                                echo '<span class="badge badge-primary">Confirmation</span>';
                                                                break;
                                                            case 'recu':
                                                                echo '<span class="badge badge-success">Reçu</span>';
                                                                break;
                                                            case 'ticket':
                                                                echo '<span class="badge badge-info">Ticket</span>';
                                                                break;
                                                        }
                                                    ?>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($message['date_envoi'])); ?></td>
                                                <td>
                                                    <?php 
                                                        if ($message['statut'] == 'envoyé') {
                                                            echo '<span class="badge badge-success">Envoyé</span>';
                                                        } else {
                                                            echo '<span class="badge badge-danger">Échoué</span>';
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#viewModal<?php echo $message['id_email']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#resendModal<?php echo $message['id_email']; ?>">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal Voir Message -->
                                            <div class="modal fade" id="viewModal<?php echo $message['id_email']; ?>" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel<?php echo $message['id_email']; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="viewModalLabel<?php echo $message['id_email']; ?>">
                                                                Message #<?php echo $message['id_email']; ?> - <?php echo $message['sujet']; ?>
                                                            </h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <p><strong>Destinataire:</strong> <?php echo $message['nom'] . ' ' . $message['prenom']; ?></p>
                                                                    <p><strong>Email:</strong> <?php echo $message['email']; ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p><strong>Type:</strong> <?php echo $message['type']; ?></p>
                                                                    <p><strong>Date d'envoi:</strong> <?php echo date('d/m/Y H:i', strtotime($message['date_envoi'])); ?></p>
                                                                    <p><strong>Statut:</strong> <?php echo $message['statut']; ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <strong>Contenu du message</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                    <?php echo nl2br(htmlspecialchars($message['contenu'])); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                            <button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#resendModal<?php echo $message['id_email']; ?>">
                                                                <i class="fas fa-paper-plane mr-1"></i> Renvoyer
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Renvoyer Message -->
                                            <div class="modal fade" id="resendModal<?php echo $message['id_email']; ?>" tabindex="-1" role="dialog" aria-labelledby="resendModalLabel<?php echo $message['id_email']; ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="resendModalLabel<?php echo $message['id_email']; ?>">Renvoyer le message</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Voulez-vous renvoyer ce message à <strong><?php echo $message['email']; ?></strong> ?</p>
                                                            <div class="alert alert-info">
                                                                <small>
                                                                    <i class="fas fa-info-circle mr-1"></i>
                                                                    Cette action créera une nouvelle entrée dans l'historique des messages.
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                            <form method="post" action="">
                                                                <input type="hidden" name="id_user" value="<?php echo $message['id_user']; ?>">
                                                                <input type="hidden" name="sujet" value="<?php echo $message['sujet']; ?>">
                                                                <input type="hidden" name="contenu" value="<?php echo $message['contenu']; ?>">
                                                                <input type="hidden" name="type" value="<?php echo $message['type']; ?>">
                                                                <button type="submit" name="send_message" class="btn btn-primary">
                                                                    <i class="fas fa-paper-plane mr-1"></i> Renvoyer
                                                                </button>
                                                            </form>
                                                        </div>
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

    <!-- Modal Nouveau Message -->
    <div class="modal fade" id="newMessageModal" tabindex="-1" role="dialog" aria-labelledby="newMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newMessageModalLabel">Nouveau message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="id_user">Destinataire</label>
                            <select class="form-control" id="id_user" name="id_user" required>
                                <option value="">Sélectionnez un client</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id_user']; ?>">
                                        <?php echo $user['nom'] . ' ' . $user['prenom'] . ' (' . $user['email'] . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="type">Type de message</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="confirmation">Confirmation</option>
                                <option value="recu">Reçu</option>
                                <option value="ticket">Ticket</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sujet">Sujet</label>
                            <input type="text" class="form-control" id="sujet" name="sujet" required>
                        </div>
                        <div class="form-group">
                            <label for="contenu">Contenu</label>
                            <textarea class="form-control" id="contenu" name="contenu" rows="6" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" name="send_message" class="btn btn-primary">
                            <i class="fas fa-paper-plane mr-1"></i> Envoyer
                        </button>
                    </div>
                </form>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function(){
        // Initialisation du carousel Owl
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

        // Recherche dans le tableau des messages
        $("#messageSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#messagesTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Scripts supplémentaires pour l'administration
        // Filtrage des tableaux
        $(".table-filter").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            var target = $(this).data("target");
            $(target + " tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Confirmation pour les actions critiques
        $(".btn-delete").on("click", function() {
            return confirm("Êtes-vous sûr de vouloir effectuer cette action ?");
        });

        // Mise à jour des indicateurs en temps réel
        function updateAdminStats() {
            // Ici vous pouvez ajouter des appels AJAX pour mettre à jour les stats
            console.log("Mise à jour des statistiques...");
        }

        // Mettre à jour toutes les 30 secondes
        setInterval(updateAdminStats, 30000);
    });

    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Initialisation des popovers Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    });
</script>

</body>
</html>
</body>
</html>