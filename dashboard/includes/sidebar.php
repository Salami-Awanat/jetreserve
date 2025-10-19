<nav class="admin-sidebar">
    <ul class="sidebar-menu">
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <a href="index.php" class="menu-link">
                <i class="fas fa-tachometer-alt"></i>
                Tableau de bord
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'gestion_vols.php' ? 'active' : ''; ?>">
            <a href="gestion_vols.php" class="menu-link">
                <i class="fas fa-plane"></i>
                Gestion des vols
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'gestion_reservations.php' ? 'active' : ''; ?>">
            <a href="gestion_reservations.php" class="menu-link">
                <i class="fas fa-ticket-alt"></i>
                Réservations
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'gestion_utilisateurs.php' ? 'active' : ''; ?>">
            <a href="gestion_utilisateurs.php" class="menu-link">
                <i class="fas fa-users"></i>
                Utilisateurs
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'gestion_compagnies.php' ? 'active' : ''; ?>">
            <a href="gestion_compagnies.php" class="menu-link">
                <i class="fas fa-building"></i>
                Compagnies
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
            <a href="messages.php" class="menu-link">
                <i class="fas fa-envelope"></i>
                Messages
                <span class="badge badge-danger"><?php echo $reservations_attente; ?></span>
            </a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'statistiques.php' ? 'active' : ''; ?>">
            <a href="statistiques.php" class="menu-link">
                <i class="fas fa-chart-bar"></i>
                Statistiques
            </a>
        </li>
    </ul>
</nav>