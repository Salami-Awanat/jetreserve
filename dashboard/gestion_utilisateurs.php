<?php include 'includes/check_admin.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs - Admin JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Mêmes styles que dans index.php */
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
        
        .btn-warning {
            background-color: var(--warning);
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
        
        .d-flex {
            display: flex;
        }
        
        .justify-content-between {
            justify-content: space-between;
        }
        
        .align-items-center {
            align-items: center;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
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
                <li class="menu-item active">
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
                <li class="menu-item">
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
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">Gestion des utilisateurs</h1>
                        <p class="page-subtitle">Gérez les comptes clients et administrateurs</p>
                    </div>
                </div>
            </div>

            <?php
            // Traitement des actions
            if (isset($_POST['changer_statut'])) {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET statut = ? WHERE id_user = ?");
                    $stmt->execute([$_POST['nouveau_statut'], $_POST['id_user']]);
                    
                    $message = "Statut de l'utilisateur changé avec succès!";
                    echo '<div style="background: var(--success); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">' . $message . '</div>';
                } catch (PDOException $e) {
                    echo '<div style="background: var(--danger); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Erreur: ' . $e->getMessage() . '</div>';
                }
            }

            if (isset($_POST['changer_role'])) {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id_user = ?");
                    $stmt->execute([$_POST['nouveau_role'], $_POST['id_user']]);
                    
                    $message = "Rôle de l'utilisateur changé avec succès!";
                    echo '<div style="background: var(--success); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">' . $message . '</div>';
                } catch (PDOException $e) {
                    echo '<div style="background: var(--danger); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Erreur: ' . $e->getMessage() . '</div>';
                }
            }

            // Vue détaillée d'un utilisateur
            if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
                try {
                    $stmt = $pdo->prepare("
                        SELECT u.*, COUNT(r.id_reservation) as total_reservations
                        FROM users u 
                        LEFT JOIN reservations r ON u.id_user = r.id_user 
                        WHERE u.id_user = ?
                        GROUP BY u.id_user
                    ");
                    $stmt->execute([$_GET['id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user) {
                        ?>
                        <div class="data-table">
                            <div class="table-header">
                                <h3 style="margin: 0; color: white;">
                                    <i class="fas fa-eye"></i> Détails de l'utilisateur #<?php echo $user['id_user']; ?>
                                </h3>
                            </div>
                            <div style="padding: 20px;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                    <div>
                                        <h4>Informations personnelles</h4>
                                        <p><strong>Nom complet:</strong> <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                        <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($user['telephone'] ?? 'Non renseigné'); ?></p>
                                        <p><strong>Date d'inscription:</strong> <?php echo date('d/m/Y H:i', strtotime($user['date_creation'])); ?></p>
                                    </div>
                                    <div>
                                        <h4>Statistiques</h4>
                                        <p><strong>Réservations:</strong> <?php echo $user['total_reservations']; ?></p>
                                        <p><strong>Rôle:</strong> 
                                            <span class="badge badge-<?php echo $user['role'] == 'admin' ? 'primary' : 'success'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </p>
                                        <p><strong>Statut:</strong> 
                                            <span class="badge badge-<?php echo $user['statut'] == 'actif' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($user['statut']); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Formulaire changement de statut -->
                                <form method="POST" style="border-top: 1px solid var(--border-color); padding-top: 20px;">
                                    <input type="hidden" name="id_user" value="<?php echo $user['id_user']; ?>">
                                    <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px;">
                                        <label><strong>Changer le statut:</strong></label>
                                        <select name="nouveau_statut" class="form-control" style="width: auto;">
                                            <option value="actif" <?php echo $user['statut'] == 'actif' ? 'selected' : ''; ?>>Actif</option>
                                            <option value="inactif" <?php echo $user['statut'] == 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                                        </select>
                                        <button type="submit" name="changer_statut" class="btn btn-primary">Appliquer</button>
                                    </div>
                                </form>
                                
                                <!-- Formulaire changement de rôle -->
                                <form method="POST">
                                    <input type="hidden" name="id_user" value="<?php echo $user['id_user']; ?>">
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <label><strong>Changer le rôle:</strong></label>
                                        <select name="nouveau_role" class="form-control" style="width: auto;">
                                            <option value="client" <?php echo $user['role'] == 'client' ? 'selected' : ''; ?>>Client</option>
                                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                        </select>
                                        <button type="submit" name="changer_role" class="btn btn-warning">Appliquer</button>
                                    </div>
                                </form>
                                
                                <div class="action-buttons" style="margin-top: 20px;">
                                    <a href="gestion_utilisateurs.php" class="btn btn-outline">
                                        <i class="fas fa-arrow-left"></i> Retour à la liste
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } catch (PDOException $e) {
                    echo '<div style="background: var(--danger); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Erreur: ' . $e->getMessage() . '</div>';
                }
            }
            ?>

            <!-- Liste des utilisateurs -->
            <div class="data-table">
                <div class="table-header">
                    <h3 style="margin: 0; color: white;">
                        <i class="fas fa-list"></i> Tous les utilisateurs
                    </h3>
                </div>
                <?php
                try {
                    $stmt = $pdo->prepare("
                        SELECT u.*, COUNT(r.id_reservation) as total_reservations
                        FROM users u 
                        LEFT JOIN reservations r ON u.id_user = r.id_user 
                        GROUP BY u.id_user 
                        ORDER BY u.date_creation DESC
                    ");
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($users) {
                        echo '<table>';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Utilisateur</th>';
                        echo '<th>Contact</th>';
                        echo '<th>Inscription</th>';
                        echo '<th>Réservations</th>';
                        echo '<th>Rôle</th>';
                        echo '<th>Statut</th>';
                        echo '<th>Actions</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        foreach ($users as $user) {
                            echo '<tr>';
                            echo '<td>';
                            echo '<strong>' . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . '</strong><br>';
                            echo '<small>ID: ' . $user['id_user'] . '</small>';
                            echo '</td>';
                            echo '<td>';
                            echo htmlspecialchars($user['email']) . '<br>';
                            echo '<small>' . ($user['telephone'] ? htmlspecialchars($user['telephone']) : 'Tél: non renseigné') . '</small>';
                            echo '</td>';
                            echo '<td>' . date('d/m/Y', strtotime($user['date_creation'])) . '</td>';
                            echo '<td>' . $user['total_reservations'] . '</td>';
                            echo '<td><span class="badge badge-' . ($user['role'] == 'admin' ? 'primary' : 'success') . '">' . ucfirst($user['role']) . '</span></td>';
                            echo '<td><span class="badge badge-' . ($user['statut'] == 'actif' ? 'success' : 'danger') . '">' . ucfirst($user['statut']) . '</span></td>';
                            echo '<td class="action-buttons">';
                            echo '<a href="gestion_utilisateurs.php?action=view&id=' . $user['id_user'] . '" class="btn btn-primary btn-sm">Détails</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                    } else {
                        echo '<div style="padding: 20px; text-align: center; color: var(--secondary);">Aucun utilisateur trouvé.</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div style="padding: 20px; text-align: center; color: var(--danger);">Erreur: ' . $e->getMessage() . '</div>';
                }
                ?>
            </div>
        </main>
    </div>
</body>
</html>