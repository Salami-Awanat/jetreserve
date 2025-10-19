<?php include 'includes/check_admin.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des compagnies - Admin JetReserve</title>
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
                <li class="menu-item">
                    <a href="gestion_utilisateurs.php" class="menu-link">
                        <i class="fas fa-users"></i>
                        Utilisateurs
                    </a>
                </li>
                <li class="menu-item active">
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
                        <h1 class="page-title">Gestion des compagnies</h1>
                        <p class="page-subtitle">Gérez les compagnies aériennes partenaires</p>
                    </div>
                    <a href="gestion_compagnies.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvelle compagnie
                    </a>
                </div>
            </div>

            <?php
            // Traitement des actions
            if (isset($_GET['action'])) {
                $action = $_GET['action'];
                
                if ($action == 'add' || $action == 'edit') {
                    $compagnie = null;
                    if ($action == 'edit' && isset($_GET['id'])) {
                        $stmt = $pdo->prepare("SELECT * FROM compagnies WHERE id_compagnie = ?");
                        $stmt->execute([$_GET['id']]);
                        $compagnie = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                    ?>
                    
                    <div class="data-table">
                        <div class="table-header">
                            <h3 style="margin: 0; color: white;">
                                <i class="fas fa-<?php echo $action == 'add' ? 'plus' : 'edit'; ?>"></i>
                                <?php echo $action == 'add' ? 'Ajouter une compagnie' : 'Modifier la compagnie'; ?>
                            </h3>
                        </div>
                        <div style="padding: 20px;">
                            <form method="POST" action="gestion_compagnies.php">
                                <input type="hidden" name="action" value="<?php echo $action; ?>">
                                <?php if ($compagnie): ?>
                                    <input type="hidden" name="id_compagnie" value="<?php echo $compagnie['id_compagnie']; ?>">
                                <?php endif; ?>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                    <div>
                                        <label>Nom de la compagnie *</label>
                                        <input type="text" name="nom_compagnie" value="<?php echo $compagnie ? htmlspecialchars($compagnie['nom_compagnie']) : ''; ?>" required 
                                               class="form-control">
                                    </div>
                                    <div>
                                        <label>Code compagnie *</label>
                                        <input type="text" name="code_compagnie" value="<?php echo $compagnie ? htmlspecialchars($compagnie['code_compagnie']) : ''; ?>" required 
                                               class="form-control" maxlength="10" style="text-transform: uppercase;">
                                    </div>
                                </div>
                                
                                <div class="action-buttons">
                                    <button type="submit" name="save_compagnie" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Enregistrer
                                    </button>
                                    <a href="gestion_compagnies.php" class="btn btn-outline">Annuler</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                }
            }

            // Traitement du formulaire
            if (isset($_POST['save_compagnie'])) {
                try {
                    $data = [
                        'nom_compagnie' => $_POST['nom_compagnie'],
                        'code_compagnie' => strtoupper($_POST['code_compagnie'])
                    ];

                    if ($_POST['action'] == 'add') {
                        $stmt = $pdo->prepare("INSERT INTO compagnies (nom_compagnie, code_compagnie) VALUES (?, ?)");
                        $stmt->execute(array_values($data));
                        $message = "Compagnie ajoutée avec succès!";
                    } else {
                        $data['id_compagnie'] = $_POST['id_compagnie'];
                        $stmt = $pdo->prepare("UPDATE compagnies SET nom_compagnie=?, code_compagnie=? WHERE id_compagnie=?");
                        $stmt->execute(array_values($data));
                        $message = "Compagnie modifiée avec succès!";
                    }
                    
                    echo '<div style="background: var(--success); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">' . $message . '</div>';
                } catch (PDOException $e) {
                    echo '<div style="background: var(--danger); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Erreur: ' . $e->getMessage() . '</div>';
                }
            }

            // Suppression
            if (isset($_GET['delete'])) {
                try {
                    // Vérifier s'il y a des vols associés
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vols WHERE id_compagnie = ?");
                    $stmt->execute([$_GET['delete']]);
                    $vols_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    
                    if ($vols_count > 0) {
                        echo '<div style="background: var(--warning); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Impossible de supprimer: ' . $vols_count . ' vol(s) associé(s) à cette compagnie.</div>';
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM compagnies WHERE id_compagnie = ?");
                        $stmt->execute([$_GET['delete']]);
                        echo '<div style="background: var(--success); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Compagnie supprimée avec succès!</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div style="background: var(--danger); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Erreur: ' . $e->getMessage() . '</div>';
                }
            }
            ?>

            <!-- Liste des compagnies -->
            <div class="data-table">
                <div class="table-header">
                    <h3 style="margin: 0; color: white;">
                        <i class="fas fa-list"></i> Liste des compagnies
                    </h3>
                </div>
                <?php
                try {
                    $stmt = $pdo->prepare("
                        SELECT c.*, COUNT(v.id_vol) as total_vols
                        FROM compagnies c 
                        LEFT JOIN vols v ON c.id_compagnie = v.id_compagnie 
                        GROUP BY c.id_compagnie 
                        ORDER BY c.nom_compagnie
                    ");
                    $stmt->execute();
                    $compagnies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($compagnies) {
                        echo '<table>';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Compagnie</th>';
                        echo '<th>Code</th>';
                        echo '<th>Vols</th>';
                        echo '<th>Actions</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        foreach ($compagnies as $compagnie) {
                            echo '<tr>';
                            echo '<td>';
                            echo '<strong>' . htmlspecialchars($compagnie['nom_compagnie']) . '</strong><br>';
                            echo '<small>ID: ' . $compagnie['id_compagnie'] . '</small>';
                            echo '</td>';
                            echo '<td><span class="badge badge-primary">' . htmlspecialchars($compagnie['code_compagnie']) . '</span></td>';
                            echo '<td>' . $compagnie['total_vols'] . ' vol(s)</td>';
                            echo '<td class="action-buttons">';
                            echo '<a href="gestion_compagnies.php?action=edit&id=' . $compagnie['id_compagnie'] . '" class="btn btn-primary btn-sm">Modifier</a>';
                            echo '<a href="gestion_compagnies.php?delete=' . $compagnie['id_compagnie'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette compagnie ?\')">Supprimer</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                    } else {
                        echo '<div style="padding: 20px; text-align: center; color: var(--secondary);">Aucune compagnie trouvée.</div>';
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