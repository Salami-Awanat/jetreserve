<?php
// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../vge64/connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 id="page-title">Tableau de Bord</h1>
                </div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php 
                        // Afficher la première lettre du prénom OU du nom
                        $first_letter = strtoupper(substr($_SESSION['prenom'] ?? $_SESSION['nom'] ?? 'A', 0, 1));
                        echo $first_letter;
                        ?>
                    </div>
                    <div class="user-details">
                        <strong>
                            <?php 
                            // Afficher le nom complet (prénom + nom)
                            $full_name = ($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? 'Admin');
                            echo htmlspecialchars(trim($full_name));
                            ?>
                        </strong>
                        <span>Administrateur</span>
                    </div>
                    <div class="dropdown">
                        <button class="dropdown-btn">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="../index.php"><i class="fas fa-home"></i> Site Public</a>
                            <a href="../index.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                        </div>
                    </div>
                </div>
            </div>