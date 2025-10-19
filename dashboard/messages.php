<?php include 'includes/check_admin.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Mêmes styles que précédemment */
        :root {
            --primary: #2c3e50;
            --secondary: #7f8c8d;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #3498db;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --white: #ffffff;
            --gray: #7f8c8d;
            --light-gray: #f9fafb;
            --border-color: #dee2e6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-gray);
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .admin-header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }
        
        .logo span {
            color: var(--danger);
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-welcome {
            color: var(--secondary);
            font-weight: 500;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
        }
        
        .btn-success {
            background-color: var(--success);
            color: var(--white);
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .admin-container {
            display: flex;
            min-height: calc(100vh - 80px);
        }
        
        .admin-sidebar {
            width: 250px;
            background: var(--white);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            padding: 20px 0;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .menu-item {
            padding: 12px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }
        
        .menu-item.active {
            background: var(--light);
            border-left-color: var(--primary);
        }
        
        .menu-item:hover {
            background: var(--light);
        }
        
        .menu-link {
            color: var(--dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        
        .menu-link i {
            width: 20px;
            text-align: center;
        }
        
        .admin-content {
            flex: 1;
            padding: 20px;
            background: var(--light-gray);
        }
        
        .content-header {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .page-title {
            color: var(--primary);
            margin-bottom: 5px;
            font-size: 1.8rem;
        }
        
        .page-subtitle {
            color: var(--secondary);
            font-size: 1rem;
        }
        
        .data-table {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .table-header {
            background: var(--primary);
            color: var(--white);
            padding: 15px 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background: var(--light);
            font-weight: 600;
            color: var(--dark);
        }
        
        tr:hover {
            background: var(--light);
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-primary { background: #dbeafe; color: #1e40af; }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        
        .message-content {
            max-height: 200px;
            overflow-y: auto;
            background: var(--light);
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">Jet<span>Reserve</span> Admin</a>
                <div class="admin-info">
                    <span class="user-welcome">Bonjour, <?php echo $_SESSION['prenom']; ?></span>
                    <a href="../index.php" class="btn btn-outline" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Voir le site
                    </a>
                    <a href="../vge64/deconnexion.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="admin-container">
        <nav class="admin-sidebar">
            <ul class="sidebar-menu">
                <li class="menu-item">
                    <a href="index.php" class="menu-link">
                        <i class="fas fa-tachometer-alt"></i>
                        Tableau de bord
                    </a>
                </li>
                <li class="menu-item">
                    <a href="gestion_vols.php" class="menu-link">
                        <i class="fas fa-plane"></i>
                        Gestion des vols
                    </a>
                </li>
                <li class="menu-item">
                    <a href="gestion_reservations.php" class="menu-link">
                        <i class="fas fa-ticket-alt"></i>
                        Réservations
                    </a>
                </li>
                <li class="menu-item">
                    <a href="gestion_utilisateurs.php" class="menu-link">
                        <i class="fas fa-users"></i>
                        Utilisateurs
                    </a>
                </li>
                <li class="menu-item">
                    <a href="gestion_compagnies.php" class="menu-link">
                        <i class="fas fa-building"></i>
                        Compagnies
                    </a>
                </li>
                <li class="menu-item active">
                    <a href="messages.php" class="menu-link">
                        <i class="fas fa-envelope"></i>
                        Messages
                    </a>
                </li>
                <li class="menu-item">
                    <a href="statistiques.php" class="menu-link">
                        <i class="fas fa-chart-bar"></i>
                        Statistiques
                    </a>
                </li>
            </ul>
        </nav>
        
        <main class="admin-content">
            <div class="content-header">
                <h1 class="page-title">Messages et notifications</h1>
                <p class="page-subtitle">Gérez les communications avec les clients</p>
            </div>

            <!-- Réservations en attente -->
            <div class="data-table">
                <div class="table-header">
                    <h3 style="margin: 0; color: white;">
                        <i class="fas fa-clock"></i> Réservations en attente de confirmation
                    </h3>
                </div>
                <?php
                try {
                    $stmt = $pdo->prepare("
                        SELECT r.*, v.depart, v.arrivee, v.date_depart, 
                               c.nom_compagnie, u.prenom, u.nom, u.email
                        FROM reservations r 
                        JOIN vols v ON r.id_vol = v.id_vol 
                        JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
                        JOIN users u ON r.id_user = u.id_user 
                        WHERE r.statut = 'en attente'
                        ORDER BY r.date_reservation DESC
                    ");
                    $stmt->execute();
                    $reservations_attente = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($reservations_attente) {
                        echo '<table>';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Réservation</th>';
                        echo '<th>Client</th>';
                        echo '<th>Vol</th>';
                        echo '<th>Date vol</th>';
                        echo '<th>Prix</th>';
                        echo '<th>Actions</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        foreach ($reservations_attente as $reservation) {
                            echo '<tr>';
                            echo '<td>';
                            echo '<strong>#' . $reservation['id_reservation'] . '</strong><br>';
                            echo '<small>' . date('d/m/Y H:i', strtotime($reservation['date_reservation'])) . '</small>';
                            echo '</td>';
                            echo '<td>';
                            echo htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']) . '<br>';
                            echo '<small>' . htmlspecialchars($reservation['email']) . '</small>';
                            echo '</td>';
                            echo '<td>' . htmlspecialchars($reservation['depart'] . ' → ' . $reservation['arrivee']) . '</td>';
                            echo '<td>' . date('d/m/Y H:i', strtotime($reservation['date_depart'])) . '</td>';
                            echo '<td>' . number_format($reservation['prix_total'], 2, ',', ' ') . '€</td>';
                            echo '<td class="action-buttons">';
                            echo '<a href="gestion_reservations.php?action=view&id=' . $reservation['id_reservation'] . '" class="btn btn-primary btn-sm">Traiter</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                    } else {
                        echo '<div style="padding: 20px; text-align: center; color: var(--secondary);">Aucune réservation en attente.</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div style="padding: 20px; text-align: center; color: var(--danger);">Erreur: ' . $e->getMessage() . '</div>';
                }
                ?>
            </div>

            <!-- Historique des emails -->
            <div class="data-table">
                <div class="table-header">
                    <h3 style="margin: 0; color: white;">
                        <i class="fas fa-envelope"></i> Historique des emails envoyés
                    </h3>
                </div>
                <?php
                try {
                    $stmt = $pdo->prepare("
                        SELECT e.*, u.prenom, u.nom, u.email as user_email
                        FROM emails e 
                        JOIN users u ON e.id_user = u.id_user 
                        ORDER BY e.date_envoi DESC
                        LIMIT 20
                    ");
                    $stmt->execute();
                    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($emails) {
                        echo '<table>';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Destinataire</th>';
                        echo '<th>Sujet</th>';
                        echo '<th>Type</th>';
                        echo '<th>Date</th>';
                        echo '<th>Statut</th>';
                        echo '<th>Actions</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        foreach ($emails as $email) {
                            echo '<tr>';
                            echo '<td>';
                            echo htmlspecialchars($email['prenom'] . ' ' . $email['nom']) . '<br>';
                            echo '<small>' . htmlspecialchars($email['user_email']) . '</small>';
                            echo '</td>';
                            echo '<td>' . htmlspecialchars($email['sujet']) . '</td>';
                            echo '<td><span class="badge badge-primary">' . ucfirst($email['type']) . '</span></td>';
                            echo '<td>' . date('d/m/Y H:i', strtotime($email['date_envoi'])) . '</td>';
                            echo '<td><span class="badge badge-' . ($email['statut'] == 'envoyé' ? 'success' : 'danger') . '">' . ucfirst($email['statut']) . '</span></td>';
                            echo '<td class="action-buttons">';
                            echo '<button onclick="showEmailContent(' . $email['id_email'] . ', \'' . htmlspecialchars(addslashes($email['contenu'])) . '\')" class="btn btn-primary btn-sm">Voir</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                    } else {
                        echo '<div style="padding: 20px; text-align: center; color: var(--secondary);">Aucun email envoyé.</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div style="padding: 20px; text-align: center; color: var(--danger);">Erreur: ' . $e->getMessage() . '</div>';
                }
                ?>
            </div>

            <!-- Formulaire d'envoi de message -->
            <div class="data-table">
                <div class="table-header">
                    <h3 style="margin: 0; color: white;">
                        <i class="fas fa-paper-plane"></i> Envoyer un message aux clients
                    </h3>
                </div>
                <div style="padding: 20px;">
                    <form method="POST" action="messages.php">
                        <div style="margin-bottom: 15px;">
                            <label><strong>Destinataires:</strong></label><br>
                            <select name="destinataires[]" multiple class="form-control" style="height: 100px;">
                                <option value="all">Tous les clients</option>
                                <?php
                                $stmt = $pdo->query("SELECT id_user, prenom, nom, email FROM users WHERE role = 'client'");
                                while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . $user['id_user'] . '">' . htmlspecialchars($user['prenom'] . ' ' . $user['nom'] . ' (' . $user['email'] . ')') . '</option>';
                                }
                                ?>
                            </select>
                            <small style="color: var(--secondary);">Maintenez Ctrl pour sélectionner plusieurs destinataires</small>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label><strong>Sujet:</strong></label>
                            <input type="text" name="sujet" class="form-control" required>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label><strong>Message:</strong></label>
                            <textarea name="contenu" rows="6" class="form-control" required placeholder="Tapez votre message ici..."></textarea>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label><strong>Type de message:</strong></label>
                            <select name="type" class="form-control">
                                <option value="confirmation">Confirmation</option>
                                <option value="promotion">Promotion</option>
                                <option value="information">Information</option>
                                <option value="newsletter">Newsletter</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="envoyer_message" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour afficher le contenu de l'email -->
    <div id="emailModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 20px; border-radius: 8px; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
            <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 15px;">
                <h3 id="modalTitle">Contenu de l'email</h3>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div id="modalContent" class="message-content"></div>
            <div style="text-align: right; margin-top: 15px;">
                <button onclick="closeModal()" class="btn btn-outline">Fermer</button>
            </div>
        </div>
    </div>

    <script>
        function showEmailContent(id, content) {
            document.getElementById('modalTitle').textContent = 'Contenu de l\'email #' + id;
            document.getElementById('modalContent').innerHTML = content.replace(/\n/g, '<br>');
            document.getElementById('emailModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('emailModal').style.display = 'none';
        }
        
        // Fermer la modal en cliquant à l'extérieur
        document.getElementById('emailModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

    <?php
    // Traitement de l'envoi de message
    if (isset($_POST['envoyer_message'])) {
        try {
            $destinataires = $_POST['destinataires'];
            $sujet = $_POST['sujet'];
            $contenu = $_POST['contenu'];
            $type = $_POST['type'];
            
            if (in_array('all', $destinataires)) {
                // Envoyer à tous les clients
                $stmt = $pdo->query("SELECT id_user FROM users WHERE role = 'client'");
                $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $users = $destinataires;
            }
            
            $emails_envoyes = 0;
            foreach ($users as $id_user) {
                $stmt = $pdo->prepare("INSERT INTO emails (id_user, sujet, contenu, type, statut) VALUES (?, ?, ?, ?, 'envoyé')");
                $stmt->execute([$id_user, $sujet, $contenu, $type]);
                $emails_envoyes++;
            }
            
            echo '<div style="background: var(--success); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Message envoyé à ' . $emails_envoyes . ' client(s) avec succès!</div>';
        } catch (PDOException $e) {
            echo '<div style="background: var(--danger); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Erreur: ' . $e->getMessage() . '</div>';
        }
    }
    ?>
</body>
</html>