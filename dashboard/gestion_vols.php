<?php include 'includes/header.php'; ?>

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="content-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Gestion des vols</h1>
                    <p class="page-subtitle">Créez et gérez les vols disponibles</p>
                </div>
                <a href="gestion_vols.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouveau vol
                </a>
            </div>
        </div>

        <?php
        // Traitement des actions
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            
            if ($action == 'add' || $action == 'edit') {
                // Formulaire d'ajout/modification
                $vol = null;
                if ($action == 'edit' && isset($_GET['id'])) {
                    $stmt = $pdo->prepare("SELECT * FROM vols WHERE id_vol = ?");
                    $stmt->execute([$_GET['id']]);
                    $vol = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                ?>
                
                <div class="data-table">
                    <div class="table-header">
                        <h3 style="margin: 0; color: white;">
                            <i class="fas fa-<?php echo $action == 'add' ? 'plus' : 'edit'; ?>"></i>
                            <?php echo $action == 'add' ? 'Ajouter un vol' : 'Modifier le vol'; ?>
                        </h3>
                    </div>
                    <div style="padding: 20px;">
                        <form method="POST" action="gestion_vols.php">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <?php if ($vol): ?>
                                <input type="hidden" name="id_vol" value="<?php echo $vol['id_vol']; ?>">
                            <?php endif; ?>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                <div>
                                    <label>Départ *</label>
                                    <input type="text" name="depart" value="<?php echo $vol ? htmlspecialchars($vol['depart']) : ''; ?>" required 
                                           class="form-control" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                                </div>
                                <div>
                                    <label>Arrivée *</label>
                                    <input type="text" name="arrivee" value="<?php echo $vol ? htmlspecialchars($vol['arrivee']) : ''; ?>" required 
                                           class="form-control" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                <div>
                                    <label>Date de départ *</label>
                                    <input type="datetime-local" name="date_depart" value="<?php echo $vol ? str_replace(' ', 'T', $vol['date_depart']) : ''; ?>" required 
                                           class="form-control" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                                </div>
                                <div>
                                    <label>Date d'arrivée *</label>
                                    <input type="datetime-local" name="date_arrivee" value="<?php echo $vol ? str_replace(' ', 'T', $vol['date_arrivee']) : ''; ?>" required 
                                           class="form-control" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                <div>
                                    <label>Prix (€) *</label>
                                    <input type="number" step="0.01" name="prix" value="<?php echo $vol ? $vol['prix'] : ''; ?>" required 
                                           class="form-control" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                                </div>
                                <div>
                                    <label>Compagnie *</label>
                                    <select name="id_compagnie" required class="form-control" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                                        <option value="">Sélectionner...</option>
                                        <?php
                                        $stmt = $pdo->query("SELECT * FROM compagnies");
                                        while ($compagnie = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = $vol && $vol['id_compagnie'] == $compagnie['id_compagnie'] ? 'selected' : '';
                                            echo '<option value="' . $compagnie['id_compagnie'] . '" ' . $selected . '>' . htmlspecialchars($compagnie['nom_compagnie']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div>
                                    <label>Avion *</label>
                                    <select name="id_avion" required class="form-control" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                                        <option value="">Sélectionner...</option>
                                        <?php
                                        $stmt = $pdo->query("SELECT * FROM avions");
                                        while ($avion = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = $vol && $vol['id_avion'] == $avion['id_avion'] ? 'selected' : '';
                                            echo '<option value="' . $avion['id_avion'] . '" ' . $selected . '>' . htmlspecialchars($avion['modele']) . ' (' . $avion['capacite_total'] . ' places)</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                                <div>
                                    <label>Classe *</label>
                                    <select name="classe" required class="form-control" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                                        <option value="économique" <?php echo $vol && $vol['classe'] == 'économique' ? 'selected' : ''; ?>>Économique</option>
                                        <option value="affaires" <?php echo $vol && $vol['classe'] == 'affaires' ? 'selected' : ''; ?>>Affaires</option>
                                        <option value="première" <?php echo $vol && $vol['classe'] == 'première' ? 'selected' : ''; ?>>Première</option>
                                    </select>
                                </div>
                                <div>
                                    <label>Escales</label>
                                    <input type="number" name="escales" value="<?php echo $vol ? $vol['escales'] : '0'; ?>" 
                                           class="form-control" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                                </div>
                                <div>
                                    <label>Places disponibles</label>
                                    <input type="number" name="places_disponibles" value="<?php echo $vol ? $vol['places_disponibles'] : ''; ?>" 
                                           class="form-control" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <button type="submit" name="save_vol" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                                <a href="gestion_vols.php" class="btn btn-outline">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
            }
        }

        // Traitement du formulaire
        if (isset($_POST['save_vol'])) {
            try {
                $data = [
                    'depart' => $_POST['depart'],
                    'arrivee' => $_POST['arrivee'],
                    'date_depart' => $_POST['date_depart'],
                    'date_arrivee' => $_POST['date_arrivee'],
                    'prix' => $_POST['prix'],
                    'id_compagnie' => $_POST['id_compagnie'],
                    'id_avion' => $_POST['id_avion'],
                    'classe' => $_POST['classe'],
                    'escales' => $_POST['escales'],
                    'places_disponibles' => $_POST['places_disponibles']
                ];

                if ($_POST['action'] == 'add') {
                    $stmt = $pdo->prepare("INSERT INTO vols (depart, arrivee, date_depart, date_arrivee, prix, id_compagnie, id_avion, classe, escales, places_disponibles) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute(array_values($data));
                    $message = "Vol ajouté avec succès!";
                } else {
                    $data['id_vol'] = $_POST['id_vol'];
                    $stmt = $pdo->prepare("UPDATE vols SET depart=?, arrivee=?, date_depart=?, date_arrivee=?, prix=?, id_compagnie=?, id_avion=?, classe=?, escales=?, places_disponibles=? WHERE id_vol=?");
                    $stmt->execute(array_values($data));
                    $message = "Vol modifié avec succès!";
                }
                
                echo '<div style="background: var(--success); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">' . $message . '</div>';
            } catch (PDOException $e) {
                echo '<div style="background: var(--danger); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Erreur: ' . $e->getMessage() . '</div>';
            }
        }

        // Suppression
        if (isset($_GET['delete'])) {
            try {
                $stmt = $pdo->prepare("DELETE FROM vols WHERE id_vol = ?");
                $stmt->execute([$_GET['delete']]);
                echo '<div style="background: var(--success); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Vol supprimé avec succès!</div>';
            } catch (PDOException $e) {
                echo '<div style="background: var(--danger); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Erreur: ' . $e->getMessage() . '</div>';
            }
        }
        ?>

        <!-- Liste des vols -->
        <div class="data-table">
            <div class="table-header">
                <h3 style="margin: 0; color: white;">
                    <i class="fas fa-list"></i> Liste des vols
                </h3>
            </div>
            <?php
            try {
                $stmt = $pdo->prepare("
                    SELECT v.*, c.nom_compagnie, a.modele 
                    FROM vols v 
                    JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
                    JOIN avions a ON v.id_avion = a.id_avion 
                    ORDER BY v.date_depart DESC
                ");
                $stmt->execute();
                $vols = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($vols) {
                    echo '<table>';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>Vol</th>';
                    echo '<th>Compagnie</th>';
                    echo '<th>Dates</th>';
                    echo '<th>Prix</th>';
                    echo '<th>Places</th>';
                    echo '<th>Classe</th>';
                    echo '<th>Actions</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($vols as $vol) {
                        echo '<tr>';
                        echo '<td>';
                        echo '<strong>' . htmlspecialchars($vol['depart'] . ' → ' . $vol['arrivee']) . '</strong><br>';
                        echo '<small>Avion: ' . htmlspecialchars($vol['modele']) . '</small>';
                        echo '</td>';
                        echo '<td>' . htmlspecialchars($vol['nom_compagnie']) . '</td>';
                        echo '<td>';
                        echo date('d/m/Y H:i', strtotime($vol['date_depart'])) . '<br>';
                        echo '<small>→ ' . date('d/m/Y H:i', strtotime($vol['date_arrivee'])) . '</small>';
                        echo '</td>';
                        echo '<td>' . number_format($vol['prix'], 2, ',', ' ') . '€</td>';
                        echo '<td>' . $vol['places_disponibles'] . '</td>';
                        echo '<td><span class="badge badge-primary">' . ucfirst($vol['classe']) . '</span></td>';
                        echo '<td class="action-buttons">';
                        echo '<a href="gestion_vols.php?action=edit&id=' . $vol['id_vol'] . '" class="btn btn-primary btn-sm">Modifier</a>';
                        echo '<a href="gestion_vols.php?delete=' . $vol['id_vol'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce vol ?\')">Supprimer</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<div style="padding: 20px; text-align: center; color: var(--secondary);">Aucun vol trouvé.</div>';
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