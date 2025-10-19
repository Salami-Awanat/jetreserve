<?php include 'includes/check_admin.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Admin JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .chart-container {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .chart-title {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
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
            
            .charts-grid {
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
                <li class="menu-item">
                    <a href="messages.php" class="menu-link">
                        <i class="fas fa-envelope"></i>
                        Messages
                    </a>
                </li>
                <li class="menu-item active">
                    <a href="statistiques.php" class="menu-link">
                        <i class="fas fa-chart-bar"></i>
                        Statistiques
                    </a>
                </li>
            </ul>
        </nav>
        
        <main class="admin-content">
            <div class="content-header">
                <h1 class="page-title">Statistiques détaillées</h1>
                <p class="page-subtitle">Analyse complète de votre activité</p>
            </div>

            <?php
            // Récupérer les statistiques avancées
            try {
                // Statistiques des réservations par mois
                $stmt = $pdo->prepare("
                    SELECT 
                        DATE_FORMAT(date_reservation, '%Y-%m') as mois,
                        COUNT(*) as nb_reservations,
                        SUM(prix_total) as chiffre_affaires
                    FROM reservations 
                    WHERE date_reservation >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                    GROUP BY DATE_FORMAT(date_reservation, '%Y-%m')
                    ORDER BY mois
                ");
                $stmt->execute();
                $stats_mois = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Réservations par statut
                $stmt = $pdo->prepare("SELECT statut, COUNT(*) as count FROM reservations GROUP BY statut");
                $stmt->execute();
                $stats_statut = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Top destinations
                $stmt = $pdo->prepare("
                    SELECT arrivee, COUNT(*) as count 
                    FROM vols v 
                    JOIN reservations r ON v.id_vol = r.id_vol 
                    GROUP BY arrivee 
                    ORDER BY count DESC 
                    LIMIT 5
                ");
                $stmt->execute();
                $top_destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Compagnies les plus populaires
                $stmt = $pdo->prepare("
                    SELECT c.nom_compagnie, COUNT(r.id_reservation) as count
                    FROM compagnies c 
                    JOIN vols v ON c.id_compagnie = v.id_compagnie 
                    JOIN reservations r ON v.id_vol = r.id_vol 
                    GROUP BY c.id_compagnie 
                    ORDER BY count DESC 
                    LIMIT 5
                ");
                $stmt->execute();
                $top_compagnies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                echo '<div style="background: var(--danger); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Erreur: ' . $e->getMessage() . '</div>';
            }
            ?>

            <!-- Statistiques principales -->
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

            <!-- Graphiques -->
            <div class="charts-grid">
                <!-- Graphique réservations par mois -->
                <div class="chart-container">
                    <h3 class="chart-title">Réservations par mois</h3>
                    <canvas id="reservationsChart" width="400" height="200"></canvas>
                </div>

                <!-- Graphique statut des réservations -->
                <div class="chart-container">
                    <h3 class="chart-title">Statut des réservations</h3>
                    <canvas id="statutChart" width="400" height="200"></canvas>
                </div>

                <!-- Top destinations -->
                <div class="chart-container">
                    <h3 class="chart-title">Top 5 destinations</h3>
                    <canvas id="destinationsChart" width="400" height="200"></canvas>
                </div>

                <!-- Compagnies populaires -->
                <div class="chart-container">
                    <h3 class="chart-title">Compagnies les plus populaires</h3>
                    <canvas id="compagniesChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Tableau des statistiques détaillées -->
            <div class="chart-container">
                <h3 class="chart-title">Détails des 10 dernières réservations</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--light);">
                            <th style="padding: 10px; border: 1px solid var(--border-color);">Client</th>
                            <th style="padding: 10px; border: 1px solid var(--border-color);">Vol</th>
                            <th style="padding: 10px; border: 1px solid var(--border-color);">Date</th>
                            <th style="padding: 10px; border: 1px solid var(--border-color);">Prix</th>
                            <th style="padding: 10px; border: 1px solid var(--border-color);">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT r.*, v.depart, v.arrivee, u.prenom, u.nom
                                FROM reservations r 
                                JOIN vols v ON r.id_vol = v.id_vol 
                                JOIN users u ON r.id_user = u.id_user 
                                ORDER BY r.date_reservation DESC 
                                LIMIT 10
                            ");
                            $stmt->execute();
                            $dernieres_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($dernieres_reservations as $reservation) {
                                echo '<tr>';
                                echo '<td style="padding: 10px; border: 1px solid var(--border-color);">' . htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']) . '</td>';
                                echo '<td style="padding: 10px; border: 1px solid var(--border-color);">' . htmlspecialchars($reservation['depart'] . ' → ' . $reservation['arrivee']) . '</td>';
                                echo '<td style="padding: 10px; border: 1px solid var(--border-color);">' . date('d/m/Y H:i', strtotime($reservation['date_reservation'])) . '</td>';
                                echo '<td style="padding: 10px; border: 1px solid var(--border-color);">' . number_format($reservation['prix_total'], 2, ',', ' ') . '€</td>';
                                echo '<td style="padding: 10px; border: 1px solid var(--border-color);">' . ucfirst($reservation['statut']) . '</td>';
                                echo '</tr>';
                            }
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="5" style="padding: 10px; border: 1px solid var(--border-color); text-align: center; color: var(--danger);">Erreur: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Graphique réservations par mois
        const reservationsCtx = document.getElementById('reservationsChart').getContext('2d');
        new Chart(reservationsCtx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return "'" . date('M Y', strtotime($item['mois'] . '-01')) . "'"; }, $stats_mois)); ?>],
                datasets: [{
                    label: 'Réservations',
                    data: [<?php echo implode(',', array_column($stats_mois, 'nb_reservations')); ?>],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });

        // Graphique statut des réservations
        const statutCtx = document.getElementById('statutChart').getContext('2d');
        new Chart(statutCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return "'" . ucfirst($item['statut']) . "'"; }, $stats_statut)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($stats_statut, 'count')); ?>],
                    backgroundColor: [
                        '#27ae60',
                        '#f39c12',
                        '#e74c3c'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Graphique top destinations
        const destinationsCtx = document.getElementById('destinationsChart').getContext('2d');
        new Chart(destinationsCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return "'" . $item['arrivee'] . "'"; }, $top_destinations)); ?>],
                datasets: [{
                    label: 'Réservations',
                    data: [<?php echo implode(',', array_column($top_destinations, 'count')); ?>],
                    backgroundColor: '#2c3e50'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Graphique compagnies populaires
        const compagniesCtx = document.getElementById('compagniesChart').getContext('2d');
        new Chart(compagniesCtx, {
            type: 'polarArea',
            data: {
                labels: [<?php echo implode(',', array_map(function($item) { return "'" . $item['nom_compagnie'] . "'"; }, $top_compagnies)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($top_compagnies, 'count')); ?>],
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#e74c3c',
                        '#f39c12',
                        '#9b59b6'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>