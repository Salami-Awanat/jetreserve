<?php include 'includes/header.php'; ?>

<?php
function envoyerFactureParEmail($pdo, $reservation_id) {
    try {
        // Récupérer les détails de la réservation
        $stmt = $pdo->prepare("
            SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, v.numero_vol, 
                   c.nom_compagnie, c.code_compagnie, 
                   u.prenom, u.nom, u.email
            FROM reservations r
            JOIN vols v ON r.id_vol = v.id_vol
            JOIN compagnies c ON v.id_compagnie = c.id_compagnie
            JOIN users u ON r.id_user = u.id_user
            WHERE r.id_reservation = ?
        ");
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            return ['success' => false, 'message' => 'Réservation introuvable'];
        }

        // Pas de génération PDF: envoi du reçu par email uniquement

        // Configuration PHPMailer
        require_once __DIR__ . '/../phpmailer/phpmailer/src/Exception.php';
        require_once __DIR__ . '/../phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../phpmailer/phpmailer/src/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Charger la configuration SMTP
            $smtpConfigPath = __DIR__ . '/smtp_config.php';
            $smtp = [];
            if (file_exists($smtpConfigPath)) {
                $smtp = include $smtpConfigPath;
            }

            // Configuration de base
            $mail->CharSet = 'UTF-8';
            
            if (!empty($smtp) && !empty($smtp['use_smtp'])) {
                // Configuration SMTP
                $mail->isSMTP();
                $mail->Host = $smtp['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $smtp['username'];
                $mail->Password = $smtp['password'];
                $mail->SMTPSecure = $smtp['encryption'];
                $mail->Port = $smtp['port'];
                
                // Debug (à désactiver en production)
                // $mail->SMTPDebug = 2;
                // $mail->Debugoutput = function($str, $level) { error_log("SMTP: $str"); };
            } else {
                // Utiliser la fonction mail() native
                $mail->isMail();
            }

            $fromEmail = $smtp['from_email'] ?? 'contact@jetreserve.com';
            $fromName = $smtp['from_name'] ?? 'JetReserve';
            $adminBcc = $smtp['admin_bcc'] ?? 'contact@jetreserve.com';

            // Expéditeur et destinataire
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($reservation['email'], $reservation['prenom'] . ' ' . $reservation['nom']);
            
            // Copie cachée admin
            if (!empty($adminBcc)) {
                $mail->addBCC($adminBcc);
            }

            // Contenu de l'email (reçu HTML sans pièce jointe)
            $mail->Subject = 'Reçu de paiement - Réservation N°' . $reservation['id_reservation'];
            $mail->isHTML(true);
            $emailTotal = number_format($reservation['prix_total'], 2, ',', ' ') . ' €';
            $emailDepart = date('d/m/Y à H:i', strtotime($reservation['date_depart']));
            $itineraire = htmlspecialchars($reservation['depart'] . ' → ' . $reservation['arrivee']);
            $compagnie = htmlspecialchars($reservation['nom_compagnie']);
            $clientNom = htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']);
            $mail->Body = '
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reçu JetReserve</title>
  <style>
    body { margin:0; padding:0; background:#f6f7fb; font-family:Segoe UI, Roboto, Helvetica, Arial, sans-serif; color:#0f172a; }
    .wrapper { width:100%; padding:24px 0; }
    .container { max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden; }
    .brand { background:#0ea5e9; color:#fff; padding:18px 24px; font-weight:700; font-size:18px; letter-spacing:0.3px; }
    .content { padding:24px; }
    .title { margin:0 0 8px 0; font-size:20px; }
    .muted { color:#475569; margin:0 0 16px 0; }
    .card { border:1px solid #e2e8f0; border-radius:10px; padding:16px; margin-top:12px; }
    .row { display:flex; flex-wrap:wrap; gap:12px; }
    .col { flex:1; min-width:220px; }
    .label { font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; }
    .value { font-size:14px; color:#0f172a; margin-top:4px; font-weight:600; }
    .total { display:flex; justify-content:space-between; margin-top:12px; padding-top:12px; border-top:1px dashed #e2e8f0; font-weight:700; }
    .footer { padding:16px 24px 24px 24px; color:#64748b; font-size:12px; }
    .cta { display:inline-block; background:#0ea5e9; color:#ffffff !important; text-decoration:none; padding:10px 14px; border-radius:8px; margin-top:16px; font-weight:600; }
  </style>
  <!--[if mso]>
    <style>
      .row { display:block !important; }
      .col { width:100% !important; }
    </style>
  <![endif]-->
  </head>
  <body>
    <div class="wrapper">
      <div class="container">
        <div class="brand">JetReserve · Reçu de paiement</div>
        <div class="content">
          <h1 class="title">Bonjour ' . $clientNom . ',</h1>
          <p class="muted">Merci pour votre confiance. Voici le reçu de votre réservation <strong>N°' . (int)$reservation['id_reservation'] . '</strong>.</p>

          <div class="card">
            <div class="row">
              <div class="col">
                <div class="label">Itinéraire</div>
                <div class="value">' . $itineraire . '</div>
              </div>
              <div class="col">
                <div class="label">Compagnie</div>
                <div class="value">' . $compagnie . '</div>
              </div>
            </div>
            <div class="row" style="margin-top:8px;">
              <div class="col">
                <div class="label">Date de départ</div>
                <div class="value">' . $emailDepart . '</div>
              </div>
              <div class="col">
                <div class="label">Montant payé</div>
                <div class="value">' . $emailTotal . '</div>
              </div>
            </div>
            <div class="total"><span>Total</span><span>' . $emailTotal . '</span></div>
          </div>

          <p class="muted">Nous vous souhaitons un excellent voyage. Conservez cet email comme preuve de paiement.</p>
          <a class="cta" href="#" target="_blank" rel="noopener">Accéder à mon espace</a>
        </div>
        <div class="footer">
          JetReserve · Service client: <a href="mailto:contact@jetreserve.com">contact@jetreserve.com</a>
        </div>
      </div>
    </div>
  </body>
</html>';
            $mail->AltBody = 'Bonjour ' . $reservation['prenom'] . ',\n\nReçu de paiement pour la réservation N°' . (int)$reservation['id_reservation'] . '.\n\nItinéraire: ' . ($reservation['depart'] . ' -> ' . $reservation['arrivee']) . '\nDate de départ: ' . $emailDepart . '\nCompagnie: ' . $reservation['nom_compagnie'] . '\nMontant payé: ' . $emailTotal . '\n';

            // Envoyer l'email
            $mail->send();

            // Enregistrer l'email (type reçu)
            $stmt = $pdo->prepare("INSERT INTO emails (id_user, sujet, contenu, type, statut) VALUES (?, ?, ?, 'recu', 'envoyé')");
            $contenu_enregistre = 'Reçu envoyé par email (sans pièce jointe).';
            $stmt->execute([
                $reservation['id_user'], 
                'Reçu réservation #' . $reservation['id_reservation'],
                $contenu_enregistre
            ]);

            return ['success' => true, 'message' => 'Reçu envoyé avec succès à ' . $reservation['email']];

        } catch (Exception $e) {
            // Enregistrer l'erreur (type reçu)
            $stmt = $pdo->prepare("INSERT INTO emails (id_user, sujet, contenu, type, statut) VALUES (?, ?, ?, 'recu', 'échoué')");
            $stmt->execute([
                $reservation['id_user'],
                'Reçu réservation #' . $reservation['id_reservation'],
                'Erreur envoi: ' . $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()];
        }

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
    }
}
?>



<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="content-header">
            <h1 class="page-title">Gestion des réservations</h1>
            <p class="page-subtitle">Visualisez et gérez toutes les réservations</p>
        </div>

        <?php
        // Traitement de l'envoi de facture (action admin)
        if (isset($_POST['envoyer_facture'])) {
            try {
                $id_reservation = $_POST['id_reservation'];
                $res = envoyerFactureParEmail($pdo, $id_reservation);
                if ($res['success']) {
                    echo '<div style="background: var(--success); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">' . htmlspecialchars($res['message']) . '</div>';
                } else {
                    echo '<div style="background: var(--danger); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">' . htmlspecialchars($res['message']) . '</div>';
                }
            } catch (Exception $e) {
                echo '<div style="background: var(--danger); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }

        // Traitement du changement de statut
       // Traitement du changement de statut
if (isset($_POST['changer_statut'])) {
    try {
        $nouveau_statut = $_POST['nouveau_statut'];
        $id_reservation = $_POST['id_reservation'];
        
        $stmt = $pdo->prepare("UPDATE reservations SET statut = ? WHERE id_reservation = ?");
        $stmt->execute([$nouveau_statut, $id_reservation]);
        
        // Envoyer un email de confirmation si le statut passe à "confirmé"
      
       // Envoyer le reçu si le statut passe à "confirmé"
if ($nouveau_statut === 'confirmé') {
    $res = envoyerFactureParEmail($pdo, $id_reservation);
    if ($res['success']) {
        $message = "Statut de la réservation #" . $id_reservation . " changé en: " . $nouveau_statut . " - Reçu envoyé au client.";
    } else {
        $message = "Statut de la réservation #" . $id_reservation . " changé en: " . $nouveau_statut . " - Erreur lors de l'envoi du reçu.";
    }
} else {
    $message = "Statut de la réservation #" . $id_reservation . " changé en: " . $nouveau_statut;
}

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
                                        <span class="badge badge-<?php echo $reservation['statut'] == 'confirmé' ? 'success' : ($reservation['statut'] == 'en attente' ? 'warning' : 'danger'); ?>">
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
                            
                                    <div style="display:flex; gap:10px; margin-top:20px;">
                                        <form id="invoiceForm" method="POST" action="gestion_reservations.php?action=view&id=<?php echo $reservation['id_reservation']; ?>">
                                            <input type="hidden" name="id_reservation" value="<?php echo $reservation['id_reservation']; ?>">
                                            <div class="action-buttons" style="display:flex; gap:10px; margin-top:12px;">
                                                <button type="button" class="btn btn-secondary" id="previewBtn">Aperçu</button>
                                                <button type="submit" name="envoyer_facture" class="btn btn-primary" id="sendBtn">Envoyer le reçu</button>
                                            </div>
                                        </form>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('invoiceForm');
    if (!form) return;

    const successAlert = document.getElementById('successAlert');
    const successEmailTarget = document.getElementById('successEmailTarget');

    const clientNameInput = document.getElementById('clientName');
    const clientEmailInput = document.getElementById('clientEmail');
    const ticketNumberInput = document.getElementById('ticketNumber');
    const bookingRefInput = document.getElementById('bookingRef');

    const previewName = document.getElementById('previewName');
    const previewTicket = document.getElementById('previewTicket');
    const previewRef = document.getElementById('previewRef');

    function updatePreview() {
        if (clientNameInput) previewName.textContent = clientNameInput.value || '';
        if (ticketNumberInput) previewTicket.textContent = ticketNumberInput.value || '';
        if (bookingRefInput) previewRef.textContent = bookingRefInput.value || '';
    }

    [clientNameInput, ticketNumberInput, bookingRefInput].forEach(function(el){
        if (el) el.addEventListener('input', updatePreview);
    });
    updatePreview();

    // La soumission est maintenant gérée côté serveur (POST), pas d'interception JS ici.

    const previewBtn = document.getElementById('previewBtn');
    if (previewBtn) {
        previewBtn.addEventListener('click', function(){
            alert("Aperçu généré. Vous pouvez maintenant envoyer la facture.");
        });
    }
});
</script>

</body>
</html>