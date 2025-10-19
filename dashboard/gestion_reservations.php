<?php include 'includes/header.php'; ?>

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="content-header">
            <h1 class="page-title">Gestion des réservations</h1>
            <p class="page-subtitle">Visualisez et gérez toutes les réservations</p>
        </div>

        <?php
        // Traitement du changement de statut
        if (isset($_POST['changer_statut'])) {
            try {
                $stmt = $pdo->prepare("UPDATE reservations SET statut = ? WHERE id_reservation = ?");
                $stmt->execute([$_POST['nouveau_statut'], $_POST['id_reservation']]);
                
                // Envoyer un email de notification (simulé)
                $message = "Statut de la réservation #" . $_POST['id_reservation'] . " changé en: " . $_POST['nouveau_statut'];
                echo '<div style="background: var(--success); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">' . $message . '</div>';
            } catch (PDOException $e) {
                echo '<div style="background: var(--danger); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Erreur: ' . $e->getMessage() . '</div>';
            }
        }

        // Vue détaillée d'une réservation
        if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
            try {
                $stmt = $pdo->prepare("
                    SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, v.numero_vol,
                           c.nom_compagnie, c.code_compagnie,
                           u.prenom, u.nom, u.email, u.telephone,
                           GROUP_CONCAT(CONCAT(s.position, s.rang) SEPARATOR ', ') as sieges
                    FROM reservations r
                    JOIN vols v ON r.id_vol = v.id_vol
                    JOIN compagnies c ON v.id_compagnie = c.id_compagnie
                    JOIN users u ON r.id_user = u.id_user
                    LEFT JOIN reservation_sieges rs ON r.id_reservation = rs.id_reservation
                    LEFT JOIN sieges_avion s ON rs.id_siege = s.id_siege
                    WHERE r.id_reservation = ?
                    GROUP BY r.id_reservation
                ");
                $stmt->execute([$_GET['id']]);
                $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($reservation) {
                    ?>
                    <div class="data-table">
                        <div class="table-header">
                            <h3 style="margin: 0; color: white;">
                                <i class="fas fa-eye"></i> Détails de la réservation #<?php echo $reservation['id_reservation']; ?>
                            </h3>
                        </div>
                        <div style="padding: 20px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                <div>
                                    <h4>Informations client</h4>
                                    <p><strong>Nom:</strong> <?php echo htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($reservation['email']); ?></p>
                                    <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($reservation['telephone'] ?? 'Non renseigné'); ?></p>
                                </div>
                                <div>
                                    <h4>Informations vol</h4>
                                    <p><strong>Vol:</strong> <?php echo htmlspecialchars($reservation['depart'] . ' → ' . $reservation['arrivee']); ?></p>
                                    <p><strong>Compagnie:</strong> <?php echo htmlspecialchars($reservation['nom_compagnie']); ?></p>
                                    <p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?></p>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                <div>
                                    <h4>Détails réservation</h4>
                                    <p><strong>Passagers:</strong> <?php echo $reservation['nombre_passagers']; ?></p>
                                    <p><strong>Sièges:</strong> <?php echo $reservation['sieges'] ? htmlspecialchars($reservation['sieges']) : 'Non assignés'; ?></p>
                                    <p><strong>Date réservation:</strong> <?php echo date('d/m/Y H:i', strtotime($reservation['date_reservation'])); ?></p>
                                </div>
                                <div>
                                    <h4>Statut et paiement</h4>
                                    <p><strong>Statut:</strong> 
                                        <span class="badge badge-<?php echo $reservation['statut'] == 'confirmé' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($reservation['statut']); ?>
                                        </span>
                                    </p>
                                    <p><strong>Prix total:</strong> <?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€</p>
                                </div>
                            </div>
                            
                            <!-- Formulaire changement de statut -->
                            <form method="POST" style="border-top: 1px solid var(--border-color); padding-top: 20px;">
                                <input type="hidden" name="id_reservation" value="<?php echo $reservation['id_reservation']; ?>">
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <label><strong>Changer le statut:</strong></label>
                                    <select name="nouveau_statut" class="form-control" style="width: auto;">
                                        <option value="en attente" <?php echo $reservation['statut'] == 'en attente' ? 'selected' : ''; ?>>En attente</option>
                                        <option value="confirmé" <?php echo $reservation['statut'] == 'confirmé' ? 'selected' : ''; ?>>Confirmé</option>
                                        <option value="annulé" <?php echo $reservation['statut'] == 'annulé' ? 'selected' : ''; ?>>Annulé</option>
                                    </select>
                                    <button type="submit" name="changer_statut" class="btn btn-primary">Appliquer</button>
                                </div>
                            </form>
                            
                            <div class="action-buttons" style="margin-top: 20px;">
                                <a href="gestion_reservations.php" class="btn btn-outline">
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

        <!-- Liste des réservations -->
        <div class="data-table">
            <div class="table-header">
                <h3 style="margin: 0; color: white;">
                    <i class="fas fa-list"></i> Toutes les réservations
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
                    ORDER BY r.date_reservation DESC
                ");
                $stmt->execute();
                $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($reservations) {
                    echo '<table>';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>Réservation</th>';
                    echo '<th>Client</th>';
                    echo '<th>Vol</th>';
                    echo '<th>Date vol</th>';
                    echo '<th>Passagers</th>';
                    echo '<th>Prix</th>';
                    echo '<th>Statut</th>';
                    echo '<th>Actions</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($reservations as $reservation) {
                        echo '<tr>';
                        echo '<td>';
                        echo '<strong>#' . $reservation['id_reservation'] . '</strong><br>';
                        echo '<small>' . date('d/m/Y H:i', strtotime($reservation['date_reservation'])) . '</small>';
                        echo '</td>';
                        echo '<td>';
                        echo htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']) . '<br>';
                        echo '<small>' . htmlspecialchars($reservation['email']) . '</small>';
                        echo '</td>';
                        echo '<td>' . htmlspecialchars($reservation['depart'] . ' → ' . $reservation['arrivee']) . '<br>';
                        echo '<small>' . htmlspecialchars($reservation['nom_compagnie']) . '</small></td>';
                        echo '<td>' . date('d/m/Y H:i', strtotime($reservation['date_depart'])) . '</td>';
                        echo '<td>' . $reservation['nombre_passagers'] . '</td>';
                        echo '<td>' . number_format($reservation['prix_total'], 2, ',', ' ') . '€</td>';
                        echo '<td><span class="badge badge-' . ($reservation['statut'] == 'confirmé' ? 'success' : ($reservation['statut'] == 'en attente' ? 'warning' : 'danger')) . '">' . ucfirst($reservation['statut']) . '</span></td>';
                        echo '<td class="action-buttons">';
                        echo '<a href="gestion_reservations.php?action=view&id=' . $reservation['id_reservation'] . '" class="btn btn-primary btn-sm">Détails</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<div style="padding: 20px; text-align: center; color: var(--secondary);">Aucune réservation trouvée.</div>';
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