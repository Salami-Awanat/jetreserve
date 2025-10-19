<?php include 'includes/check_admin.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Admin JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            text-align: center;
            border-left: 4px solid var(--primary);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--secondary);
            font-size: 0.9rem;
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
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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
                <li class="menu-item active">
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
                <li class="menu-item">
                    <a href="messages.php" class="menu-link">
                        <i class="fas fa-envelope"></i>
                        Messages
                        <span class="badge badge-danger"><?php echo $reservations_attente; ?></span>
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
                <h1 class="page-title">Tableau de bord</h1>
                <p class="page-subtitle">Vue d'ensemble de votre activité</p>
            </div>

            <!-- Cartes de statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_clients; ?></div>
                    <div class="stat-label">Clients inscrits</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_reservations; ?></div>
                    <div class="stat-label">Réservations totales</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-plane"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_vols; ?></div>
                    <div class="stat-label">Vols disponibles</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($chiffre_affaires, 0, ',', ' '); ?>€</div>
                    <div class="stat-label">Chiffre d'affaires</div>
                </div>
            </div>

            <!-- Dernières réservations -->
            <div class="data-table">
                <div class="table-header">
                    <h3 style="margin: 0; color: white;">
                        <i class="fas fa-clock"></i> Dernières réservations
                    </h3>
                </div>
                <?php
                try {
                    $stmt = $pdo->prepare("
                        SELECT r.*, v.depart, v.arrivee, v.date_depart, 
                               c.nom_compagnie, u.prenom, u.nom
                        FROM reservations r 
                        JOIN vols v ON r.id_vol = v.id_vol 
                        JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
                        JOIN users u ON r.id_user = u.id_user 
                        ORDER BY r.date_reservation DESC 
                        LIMIT 5
                    ");
                    $stmt->execute();
                    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($reservations) {
                        echo '<table>';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Client</th>';
                        echo '<th>Vol</th>';
                        echo '<th>Date</th>';
                        echo '<th>Passagers</th>';
                        echo '<th>Prix</th>';
                        echo '<th>Statut</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        foreach ($reservations as $reservation) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']) . '</td>';
                            echo '<td>' . htmlspecialchars($reservation['depart'] . ' → ' . $reservation['arrivee']) . '</td>';
                            echo '<td>' . date('d/m/Y H:i', strtotime($reservation['date_depart'])) . '</td>';
                            echo '<td>' . $reservation['nombre_passagers'] . '</td>';
                            echo '<td>' . number_format($reservation['prix_total'], 2, ',', ' ') . '€</td>';
                            echo '<td><span class="badge badge-' . ($reservation['statut'] == 'confirmé' ? 'success' : ($reservation['statut'] == 'en attente' ? 'warning' : 'danger')) . '">' . ucfirst($reservation['statut']) . '</span></td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                    } else {
                        echo '<div style="padding: 20px; text-align: center; color: var(--secondary);">Aucune réservation récente.</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div style="padding: 20px; text-align: center; color: var(--danger);">Erreur: ' . $e->getMessage() . '</div>';
                }
                ?>
            </div>

            <!-- Prochains vols -->
            <div class="data-table">
                <div class="table-header">
                    <h3 style="margin: 0; color: white;">
                        <i class="fas fa-plane-departure"></i> Prochains départs
                    </h3>
                </div>
                <?php
                try {
                    $stmt = $pdo->prepare("
                        SELECT v.*, c.nom_compagnie, a.modele 
                        FROM vols v 
                        JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
                        JOIN avions a ON v.id_avion = a.id_avion 
                        WHERE v.date_depart > NOW() 
                        ORDER BY v.date_depart ASC 
                        LIMIT 5
                    ");
                    $stmt->execute();
                    $vols = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($vols) {
                        echo '<table>';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Vol</th>';
                        echo '<th>Compagnie</th>';
                        echo '<th>Date départ</th>';
                        echo '<th>Places</th>';
                        echo '<th>Prix</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        foreach ($vols as $vol) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($vol['depart'] . ' → ' . $vol['arrivee']) . '</td>';
                            echo '<td>' . htmlspecialchars($vol['nom_compagnie']) . '</td>';
                            echo '<td>' . date('d/m/Y H:i', strtotime($vol['date_depart'])) . '</td>';
                            echo '<td>' . $vol['places_disponibles'] . '</td>';
                            echo '<td>' . number_format($vol['prix'], 2, ',', ' ') . '€</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                    } else {
                        echo '<div style="padding: 20px; text-align: center; color: var(--secondary);">Aucun vol à venir.</div>';
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