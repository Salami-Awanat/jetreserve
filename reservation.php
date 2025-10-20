<?php
// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DÉBUT DU BUFFER - TRÈS IMPORTANT
ob_start();

require_once 'includes/db.php';
session_start();

// Vérifier si l'utilisateur est connecté - CORRECTION DES NOMS DE SESSION
if (!isset($_SESSION['id_user'])) {
    header('Location: vge64/connexion.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Récupérer l'ID du vol depuis l'URL
$id_vol = isset($_GET['id_vol']) ? intval($_GET['id_vol']) : 0;

if ($id_vol <= 0) {
    header('Location: index.php');
    exit;
}

// Récupérer les détails du vol
try {
    $stmt = $pdo->prepare("
        SELECT v.*, c.nom_compagnie, c.code_compagnie, a.modele as avion_modele,
               a.capacite_total, a.configuration_sieges
        FROM vols v 
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
        JOIN avions a ON v.id_avion = a.id_avion
        WHERE v.id_vol = ?
    ");
    $stmt->execute([$id_vol]);
    $vol = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vol) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération du vol: " . $e->getMessage());
}

// Récupérer les sièges disponibles ET occupés pour ce vol
try {
    // Sièges disponibles
    $stmt = $pdo->prepare("
        SELECT sa.* 
        FROM sieges_avion sa
        WHERE sa.id_avion = ? 
        AND sa.statut = 'actif'
        AND sa.id_siege NOT IN (
            SELECT rs.id_siege 
            FROM reservation_sieges rs
            JOIN reservations r ON rs.id_reservation = r.id_reservation
            WHERE r.id_vol = ? AND r.statut IN ('confirmé', 'en attente')
        )
        ORDER BY sa.rang, sa.position
    ");
    $stmt->execute([$vol['id_avion'], $id_vol]);
    $sieges_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sièges occupés
    $stmt_occupes = $pdo->prepare("
        SELECT sa.id_siege
        FROM sieges_avion sa
        JOIN reservation_sieges rs ON sa.id_siege = rs.id_siege
        JOIN reservations r ON rs.id_reservation = r.id_reservation
        WHERE r.id_vol = ? AND r.statut IN ('confirmé', 'en attente')
    ");
    $stmt_occupes->execute([$id_vol]);
    $sieges_occupes = $stmt_occupes->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $sieges_disponibles = [];
    $sieges_occupes = [];
}

// Récupérer les options de bagage
try {
    $stmt_bagages = $pdo->prepare("SELECT * FROM options_bagage WHERE statut = 'actif' ORDER BY prix_supplement");
    $stmt_bagages->execute();
    $options_bagage = $stmt_bagages->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $options_bagage = [
        ['id_option' => 1, 'nom_option' => 'Bagage à main', 'description' => 'Bagage cabine standard (8kg)', 'poids_max' => 8, 'prix_supplement' => 0],
        ['id_option' => 2, 'nom_option' => 'Bagage en soute 20kg', 'description' => 'Bagage en soute jusqu\'à 20kg', 'poids_max' => 20, 'prix_supplement' => 25],
        ['id_option' => 3, 'nom_option' => 'Bagage en soute 30kg', 'description' => 'Bagage en soute jusqu\'à 30kg', 'poids_max' => 30, 'prix_supplement' => 45],
    ];
}

// Traitement du formulaire de réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserver'])) {
    $nombre_passagers = intval($_POST['nombre_passagers']);
    $sieges_selectionnes = isset($_POST['sieges']) ? $_POST['sieges'] : [];
    $bagages_selectionnes = isset($_POST['bagages']) ? $_POST['bagages'] : [];
    
    // Validation
    $erreurs = [];
    
    if ($nombre_passagers <= 0 || $nombre_passagers > 10) {
        $erreurs[] = "Nombre de passagers invalide";
    }
    
    if (count($sieges_selectionnes) !== $nombre_passagers) {
        $erreurs[] = "Vous devez sélectionner exactement " . $nombre_passagers . " siège(s)";
    }
    
    // Vérifier que les sièges sélectionnés sont toujours disponibles
    foreach ($sieges_selectionnes as $siege_id) {
        if (in_array($siege_id, $sieges_occupes)) {
            $erreurs[] = "Un des sièges sélectionnés n'est plus disponible";
            break;
        }
    }
    
    if (empty($erreurs)) {
        try {
            // Commencer une transaction
            $pdo->beginTransaction();
            
            error_log("=== DÉBUT RÉSERVATION ===");
            error_log("Passagers: $nombre_passagers, Sièges: " . count($sieges_selectionnes));
            
            // Calculer le prix total
            $prix_total = $vol['prix'] * $nombre_passagers;
            $supplements_total = 0;
            $supplement_bagages = 0;
            
            // Ajouter les suppléments des sièges
            foreach ($sieges_selectionnes as $id_siege) {
                $stmt = $pdo->prepare("SELECT supplement_prix FROM sieges_avion WHERE id_siege = ?");
                $stmt->execute([$id_siege]);
                $siege = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($siege) {
                    $supplement = $siege['supplement_prix'];
                    $prix_total += $supplement;
                    $supplements_total += $supplement;
                }
            }
            
            // Ajouter les suppléments des bagages
            foreach ($bagages_selectionnes as $id_option => $quantite) {
                $quantite = intval($quantite);
                if ($quantite > 0) {
                    $prix_option = 0;
                    foreach ($options_bagage as $option) {
                        if ($option['id_option'] == $id_option) {
                            $prix_option = $option['prix_supplement'];
                            break;
                        }
                    }
                    $supplement = $prix_option * $quantite;
                    $prix_total += $supplement;
                    $supplement_bagages += $supplement;
                }
            }
            
            // Ajouter les frais fixes
            $frais_service = 9.00;
            $taxes_aeroport = 25.00;
            $prix_total += $frais_service + $taxes_aeroport;
            
            // CORRECTION : Utiliser 'confirmé' au lieu de 'confirmée'
            $statut_reservation = 'confirmé';
            
            error_log("Prix total calculé: $prix_total");
            
            // Créer la réservation
            $stmt = $pdo->prepare("
                INSERT INTO reservations (id_user, id_vol, statut, nombre_passagers, prix_total, date_reservation)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$_SESSION['id_user'], $id_vol, $statut_reservation, $nombre_passagers, $prix_total]);
            $id_reservation = $pdo->lastInsertId();
            
            if (!$id_reservation) {
                throw new Exception("Échec de la création de la réservation");
            }
            
            error_log("Réservation créée avec ID: $id_reservation");
            
            // Associer les sièges à la réservation
            foreach ($sieges_selectionnes as $id_siege) {
                $stmt_siege = $pdo->prepare("SELECT supplement_prix FROM sieges_avion WHERE id_siege = ?");
                $stmt_siege->execute([$id_siege]);
                $siege = $stmt_siege->fetch(PDO::FETCH_ASSOC);
                
                $prix_siege = $vol['prix'] + ($siege ? $siege['supplement_prix'] : 0);
                
                $stmt = $pdo->prepare("
                    INSERT INTO reservation_sieges (id_reservation, id_siege, prix_paye)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$id_reservation, $id_siege, $prix_siege]);
            }
            
            error_log("Sièges associés: " . count($sieges_selectionnes));
            
            // Associer les bagages à la réservation
            foreach ($bagages_selectionnes as $id_option => $quantite) {
                $quantite = intval($quantite);
                if ($quantite > 0) {
                    $prix_option = 0;
                    foreach ($options_bagage as $option) {
                        if ($option['id_option'] == $id_option) {
                            $prix_option = $option['prix_supplement'];
                            break;
                        }
                    }
                    
                    $stmt_bagage = $pdo->prepare("
                        INSERT INTO reservation_bagages (id_reservation, id_option, quantite, prix_applique)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt_bagage->execute([$id_reservation, $id_option, $quantite, $prix_option]);
                }
            }
            
            error_log("Bagages associés: " . count($bagages_selectionnes));
            
            // Enregistrer le paiement
            $stmt_paiement = $pdo->prepare("
                INSERT INTO paiements (id_reservation, montant, mode_paiement, statut, date_paiement)
                VALUES (?, ?, 'carte', 'réussi', NOW())
            ");
            $stmt_paiement->execute([$id_reservation, $prix_total]);
            
            error_log("Paiement enregistré");
            
            // Mettre à jour les places disponibles
            $stmt = $pdo->prepare("
                UPDATE vols 
                SET places_disponibles = places_disponibles - ? 
                WHERE id_vol = ?
            ");
            $stmt->execute([$nombre_passagers, $id_vol]);
            
            error_log("Places mises à jour");
            
            // Envoyer un email de confirmation
            try {
                $stmt_email = $pdo->prepare("
                    INSERT INTO emails (id_user, sujet, contenu, type, statut, date_envoi)
                    VALUES (?, ?, ?, 'confirmation', 'envoyé', NOW())
                ");
                $sujet_email = "Confirmation de votre réservation JetReserve";
                $contenu_email = "Votre réservation n°" . $id_reservation . " pour le vol " . $vol['depart'] . " - " . $vol['arrivee'] . " a été confirmée. Montant total : " . number_format($prix_total, 2, ',', ' ') . "€";
                $stmt_email->execute([$_SESSION['id_user'], $sujet_email, $contenu_email]);
                error_log("Email programmé");
            } catch (Exception $e) {
                error_log("Erreur email non critique: " . $e->getMessage());
                // Ne pas bloquer la réservation pour une erreur d'email
            }
            
            // Confirmer la transaction
            $pdo->commit();
            
            error_log("=== RÉSERVATION RÉUSSIE ===");
            
            // Préparer et effectuer une redirection robuste
            $redirectUrl = 'confirmation_reservation.php?id_reservation=' . $id_reservation;
            
            // Libérer le verrou de session pour éviter tout blocage côté confirmation
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            
            // Nettoyer le buffer en cours (empêche "headers already sent")
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Tenter l'en-tête HTTP, sinon fallback JS/meta
            if (!headers_sent()) {
                // Utiliser 303 See Other pour rediriger après POST
                http_response_code(303);
                header('Location: ' . $redirectUrl);
                header('Connection: close');
                // Si possible, annoncer une longueur nulle pour libérer le client
                if (!headers_sent()) {
                    header('Content-Length: 0');
                }
                flush();
                exit;
            } else {
                error_log('Redirection fallback JS utilisée vers: ' . $redirectUrl);
                echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') . '"><script>window.location.replace(' . json_encode($redirectUrl) . ');</script></head><body>Redirection...</body></html>';
                exit;
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $erreur_message = "Erreur lors de la réservation: " . $e->getMessage();
            $erreurs[] = $erreur_message;
            error_log("=== ERREUR RÉSERVATION: " . $e->getMessage() . " ===");
            error_log("Stack trace: " . $e->getTraceAsString());
        }
    }
    
    if (!empty($erreurs)) {
        $erreur = implode("<br>", $erreurs);
    }
}

// Si on arrive ici, c'est qu'on affiche le formulaire ou qu'il y a une erreur
// On nettoie le buffer et on affiche la page normalement
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* VOTRE CSS EXISTANT - JE GARDE TOUT */
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
            --premium: #f39c12;
            --business: #9b59b6;
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        header {
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
        
        .auth-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
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
        
        .btn-success {
            background-color: var(--success);
            color: var(--white);
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .btn-lg {
            padding: 12px 24px;
            font-size: 1.1rem;
        }
        
        .btn-full {
            width: 100%;
        }
        
        .reservation-container {
            max-width: 1000px;
            margin: 2rem auto;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .reservation-header {
            background: linear-gradient(135deg, var(--primary), #34495e);
            color: var(--white);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .reservation-header::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
        }
        
        .reservation-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
            position: relative;
        }
        
        .progress-steps::before {
            content: "";
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--border-color);
            z-index: 1;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--white);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .step.active .step-number {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }
        
        .step.completed .step-number {
            background: var(--success);
            color: var(--white);
            border-color: var(--success);
        }
        
        .step-label {
            font-size: 0.8rem;
            color: var(--secondary);
        }
        
        .step.active .step-label {
            color: var(--primary);
            font-weight: 500;
        }
        
        .vol-resume {
            padding: 2rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .vol-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-card {
            background: var(--light);
            padding: 1rem;
            border-radius: 6px;
            border-left: 4px solid var(--primary);
            transition: transform 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-2px);
        }
        
        .info-label {
            font-weight: 600;
            color: var(--secondary);
            font-size: 0.9rem;
        }
        
        .info-value {
            font-size: 1.1rem;
            color: var(--dark);
            font-weight: 500;
        }
        
        .reservation-form {
            padding: 2rem;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--white);
        }
        
        .form-section:hover {
            border-color: var(--primary);
        }
        
        .section-title {
            color: var(--primary);
            margin-bottom: 1rem;
            font-weight: 600;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            color: var(--info);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
        }
        
        .sieges-container {
            margin-top: 1rem;
        }
        
        .avion-schema {
            background: var(--light);
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            position: relative;
        }
        
        .cockpit {
            background: var(--primary);
            color: white;
            padding: 1rem;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin-bottom: 2rem;
            font-weight: bold;
        }
        
        .rangee-sieges {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1rem;
            gap: 10px;
        }
        
        .numero-rangee {
            width: 30px;
            text-align: center;
            font-weight: 600;
            color: var(--secondary);
        }
        
        .siege {
            width: 40px;
            height: 40px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
        }
        
        .siege.disponible {
            background: var(--white);
            border-color: var(--success);
        }
        
        .siege.disponible:hover {
            background: var(--success);
            color: var(--white);
            transform: scale(1.1);
        }
        
        .siege.selectionne {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
            transform: scale(1.1);
        }
        
        .siege.occupe {
            background: var(--danger);
            color: var(--white);
            border-color: var(--danger);
            cursor: not-allowed;
        }
        
        .siege.premium {
            border-color: var(--premium);
        }
        
        .siege.business {
            border-color: var(--business);
        }
        
        .siege::after {
            content: attr(data-supplement);
            position: absolute;
            top: -20px;
            font-size: 0.7rem;
            color: var(--secondary);
        }
        
        .couloir {
            width: 40px;
            text-align: center;
            color: var(--secondary);
            font-size: 0.8rem;
        }
        
        .legende-sieges {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .legende-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .legende-couleur {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        /* Styles pour les options de bagage */
        .bagages-container {
            display: grid;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .bagage-option {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            background: var(--white);
            transition: all 0.3s ease;
        }
        
        .bagage-option:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .bagage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .bagage-header h4 {
            color: var(--primary);
            margin: 0;
            font-size: 1.1rem;
        }
        
        .bagage-price {
            background: var(--success);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .bagage-description {
            color: var(--secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .bagage-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-qty {
            width: 35px;
            height: 35px;
            border: 1px solid var(--border-color);
            background: var(--light);
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-qty:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .bagage-qty {
            width: 60px;
            text-align: center;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 0.5rem;
        }
        
        .bagage-total {
            margin-left: auto;
            font-weight: bold;
            color: var(--primary);
            min-width: 60px;
            text-align: right;
        }
        
        .selected-bagages {
            background: var(--light);
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
            display: none;
        }
        
        .bagage-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--info);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin: 0.25rem;
            font-size: 0.9rem;
        }
        
        .resume-prix {
            background: var(--light);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .ligne-prix {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .ligne-prix:last-child {
            border-bottom: none;
        }
        
        .prix-total {
            border-top: 2px solid var(--border-color);
            padding-top: 0.5rem;
            margin-top: 0.5rem;
            font-weight: 600;
            font-size: 1.2rem;
            color: var(--primary);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        
        .selected-seats-display {
            background: var(--light);
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .seat-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin: 0.25rem;
            font-size: 0.9rem;
        }
        
        .timer {
            background: var(--warning);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .vol-info-grid {
                grid-template-columns: 1fr;
            }
            
            .rangee-sieges {
                flex-wrap: wrap;
            }
            
            .legende-sieges {
                flex-direction: column;
                gap: 1rem;
            }
            
            .progress-steps {
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .auth-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .bagage-controls {
                flex-wrap: wrap;
            }
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 1rem;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <span class="user-welcome">
                        <i class="fas fa-user"></i> 
                        Bonjour, <?php echo htmlspecialchars($_SESSION['prenom'] ?? $_SESSION['nom'] ?? 'Utilisateur'); ?>
                    </span>
                    <a href="vge64/index2.php" class="btn btn-outline">Mon compte</a>
                    <a href="vge64/deconnexion.php" class="btn btn-primary">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Barre de progression -->
        <div class="progress-steps">
            <div class="step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Recherche</div>
            </div>
            <div class="step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Sélection</div>
            </div>
            <div class="step active">
                <div class="step-number">3</div>
                <div class="step-label">Réservation</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>

        <div class="reservation-container">
            <!-- En-tête -->
            <div class="reservation-header">
                <h1 class="reservation-title"><i class="fas fa-plane"></i> Réservation de vol</h1>
                <p>Finalisez votre réservation en choisissant vos sièges et options</p>
                
                <!-- Timer de réservation -->
                <div class="timer">
                    <i class="fas fa-clock"></i>
                    <span id="reservation-timer">15:00</span>
                </div>
            </div>
            
            <!-- Résumé du vol -->
            <div class="vol-resume">
                <h2 class="section-title"><i class="fas fa-info-circle"></i> Résumé du vol</h2>
                <div class="vol-info-grid">
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-route"></i> Itinéraire</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['depart']); ?> → <?php echo htmlspecialchars($vol['arrivee']); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-calendar-alt"></i> Date et heure</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($vol['date_depart'])); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-building"></i> Compagnie</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['nom_compagnie']); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-star"></i> Classe</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['classe']); ?></div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Important :</strong> Vous avez 15 minutes pour compléter votre réservation. Passé ce délai, les sièges sélectionnés seront libérés.
                </div>
            </div>
            
            <!-- Formulaire de réservation -->
            <form method="POST" class="reservation-form" id="reservationForm">
                <?php if (isset($erreur)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <div><?php echo $erreur; ?></div>
                    </div>
                <?php endif; ?>
                
                <!-- Sélection du nombre de passagers -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-users"></i> Nombre de passagers</h3>
                    <div class="form-group">
                        <label for="nombre_passagers">Nombre de passagers *</label>
                        <select name="nombre_passagers" id="nombre_passagers" class="form-control" required>
                            <?php for ($i = 1; $i <= min(10, $vol['places_disponibles']); $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo isset($_POST['nombre_passagers']) && $_POST['nombre_passagers'] == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> passager(s) - <?php echo number_format($vol['prix'] * $i, 2, ',', ' '); ?>€
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Sélection des sièges -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-chair"></i> Choix des sièges</h3>
                    
                    <!-- Affichage des sièges sélectionnés -->
                    <div class="selected-seats-display" id="selected-seats-display" style="display: none;">
                        <h4>Sièges sélectionnés :</h4>
                        <div id="selected-seats-list"></div>
                    </div>
                    
                    <div class="sieges-container">
                        <div class="avion-schema">
                            <div class="cockpit">
                                <i class="fas fa-plane"></i> COCKPIT
                            </div>
                            
                            <?php
                            // Organiser les sièges par rangée
                            $sieges_par_rang = [];
                            foreach ($sieges_disponibles as $siege) {
                                $sieges_par_rang[$siege['rang']][] = $siege;
                            }
                            
                            // Trier par rangée
                            ksort($sieges_par_rang);
                            
                            foreach ($sieges_par_rang as $rang => $sieges_rang):
                                // Déterminer la configuration des sièges
                                $positions = array_column($sieges_rang, 'position');
                                $has_couloir = in_array('C', $positions);
                            ?>
                                <div class="rangee-sieges">
                                    <div class="numero-rangee"><?php echo $rang; ?></div>
                                    
                                    <?php 
                                    $last_position = '';
                                    foreach ($sieges_rang as $siege): 
                                        // Ajouter un espace pour le couloir si nécessaire
                                        if ($has_couloir && $last_position === 'A' && $siege['position'] === 'C') {
                                            echo '<div class="couloir">Couloir</div>';
                                        }
                                        
                                        $classe_siege = 'disponible';
                                        if (in_array($siege['id_siege'], $sieges_occupes)) {
                                            $classe_siege = 'occupe';
                                        }
                                        
                                        // Ajouter classe pour siège premium/business
                                        if ($siege['supplement_prix'] > 50) {
                                            $classe_siege .= ' premium';
                                        } elseif ($siege['supplement_prix'] > 20) {
                                            $classe_siege .= ' business';
                                        }
                                    ?>
                                        <label class="siege <?php echo $classe_siege; ?>" 
                                               data-supplement="<?php echo $siege['supplement_prix'] > 0 ? '+' . $siege['supplement_prix'] . '€' : ''; ?>"
                                               title="Siège <?php echo $siege['position'].$rang; ?><?php echo $siege['supplement_prix'] > 0 ? ' - Supplément: ' . $siege['supplement_prix'] . '€' : ''; ?>">
                                            <input type="checkbox" name="sieges[]" value="<?php echo $siege['id_siege']; ?>" 
                                                   style="display: none;" 
                                                   <?php echo in_array($siege['id_siege'], $sieges_occupes) ? 'disabled' : ''; ?>
                                                   onchange="toggleSiege(this)">
                                            <?php echo $siege['position']; ?>
                                        </label>
                                    <?php 
                                        $last_position = $siege['position'];
                                    endforeach; 
                                    ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Légende améliorée -->
                            <div class="legende-sieges">
                                <div class="legende-item">
                                    <div class="legende-couleur" style="background: var(--success);"></div>
                                    <span>Disponible</span>
                                </div>
                                <div class="legende-item">
                                    <div class="legende-couleur" style="background: var(--primary);"></div>
                                    <span>Sélectionné</span>
                                </div>
                                <div class="legende-item">
                                    <div class="legende-couleur" style="background: var(--danger);"></div>
                                    <span>Occupé</span>
                                </div>
                                <div class="legende-item">
                                    <div class="legende-couleur" style="background: var(--premium);"></div>
                                    <span>Premium (+50€)</span>
                                </div>
                                <div class="legende-item">
                                    <div class="legende-couleur" style="background: var(--business);"></div>
                                    <span>Business (+20€)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Options de bagage -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-suitcase"></i> Options de bagage</h3>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Inclus avec votre billet :</strong> 1 bagage à main (8kg) per passager
                    </div>
                    
                    <!-- Affichage des bagages sélectionnés -->
                    <div class="selected-bagages" id="selected-bagages-display" style="display: none;">
                        <h4>Bagages sélectionnés :</h4>
                        <div id="selected-bagages-list"></div>
                    </div>
                    
                    <div class="bagages-container">
                        <?php foreach ($options_bagage as $option): ?>
                            <?php if ($option['nom_option'] !== 'Bagage à main'): ?>
                                <div class="bagage-option">
                                    <div class="bagage-header">
                                        <h4><?php echo htmlspecialchars($option['nom_option']); ?></h4>
                                        <span class="bagage-price">+<?php echo number_format($option['prix_supplement'], 2, ',', ' '); ?>€</span>
                                    </div>
                                    
                                    <?php if (!empty($option['description'])): ?>
                                        <p class="bagage-description"><?php echo htmlspecialchars($option['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="bagage-controls">
                                        <button type="button" class="btn-qty minus" data-target="bagage_<?php echo $option['id_option']; ?>">-</button>
                                        <input type="number" 
                                               name="bagages[<?php echo $option['id_option']; ?>]" 
                                               id="bagage_<?php echo $option['id_option']; ?>"
                                               class="bagage-qty" 
                                               value="0" 
                                               min="0" 
                                               max="10"
                                               onchange="calculerPrix()">
                                        <button type="button" class="btn-qty plus" data-target="bagage_<?php echo $option['id_option']; ?>">+</button>
                                        
                                        <span class="bagage-total" id="bagage_total_<?php echo $option['id_option']; ?>">
                                            0,00€
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Résumé du prix -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-receipt"></i> Résumé du prix</h3>
                    <div class="resume-prix">
                        <div class="ligne-prix">
                            <span>Prix de base (<span id="passagers-count">1</span> passager(s)):</span>
                            <span id="prix-base"><?php echo number_format($vol['prix'], 2, ',', ' '); ?>€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Suppléments sièges:</span>
                            <span id="supplements-total">0,00€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Suppléments bagages:</span>
                            <span id="supplements-bagage">0,00€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Frais de service:</span>
                            <span>9,00€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Taxes aéroport:</span>
                            <span>25,00€</span>
                        </div>
                        <div class="ligne-prix prix-total">
                            <span>Total:</span>
                            <span id="prix-total"><?php echo number_format($vol['prix'] + 9 + 25, 2, ',', ' '); ?>€</span>
                        </div>
                    </div>
                </div>
                
                <!-- Loading indicator -->
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Traitement de votre réservation...</p>
                </div>
                
                <!-- Bouton de réservation -->
                <div class="form-section">
                    <button type="submit" name="reserver" class="btn btn-success btn-lg btn-full" id="submit-btn">
                        <i class="fas fa-credit-card"></i> Confirmer et payer la réservation
                    </button>
                    <p style="text-align: center; margin-top: 1rem; color: var(--secondary); font-size: 0.9rem;">
                        <i class="fas fa-lock"></i> Paiement sécurisé SSL
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // VOTRE JAVASCRIPT EXISTANT - JE GARDE TOUT
        // Stocker les suppléments des sièges
        const supplementsSieges = {};
        <?php foreach ($sieges_disponibles as $siege): ?>
        supplementsSieges[<?php echo $siege['id_siege']; ?>] = <?php echo $siege['supplement_prix']; ?>;
        <?php endforeach; ?>

        // Stocker les noms des sièges
        const nomsSieges = {};
        <?php foreach ($sieges_disponibles as $siege): ?>
        nomsSieges[<?php echo $siege['id_siege']; ?>] = '<?php echo $siege['position'] . $siege['rang']; ?>';
        <?php endforeach; ?>

        // Stocker les options de bagage
        const optionsBagage = {};
        <?php foreach ($options_bagage as $option): ?>
        optionsBagage[<?php echo $option['id_option']; ?>] = {
            nom: '<?php echo addslashes($option['nom_option']); ?>',
            prix: <?php echo $option['prix_supplement']; ?>,
            poids: <?php echo $option['poids_max'] ?? 0; ?>
        };
        <?php endforeach; ?>

        const prixBase = <?php echo $vol['prix']; ?>;
        const fraisService = 9.00;
        const taxesAeroport = 25.00;

        function calculerPrix() {
            const nbPassagers = parseInt(document.getElementById('nombre_passagers').value);
            let supplements = 0;
            const siegesSelectionnes = [];
            let supplementBagages = 0;
            const bagagesSelectionnes = [];
            
            // Calculer les suppléments des sièges sélectionnés
            document.querySelectorAll('input[name="sieges[]"]:checked').forEach(checkbox => {
                const siegeId = parseInt(checkbox.value);
                supplements += supplementsSieges[siegeId] || 0;
                siegesSelectionnes.push(siegeId);
            });
            
            // Calculer les suppléments des bagages
            document.querySelectorAll('input.bagage-qty').forEach(input => {
                const optionId = parseInt(input.name.match(/\[(\d+)\]/)[1]);
                const quantite = parseInt(input.value) || 0;
                
                if (quantite > 0 && optionsBagage[optionId]) {
                    const supplement = optionsBagage[optionId].prix * quantite;
                    supplementBagages += supplement;
                    bagagesSelectionnes.push({
                        id: optionId,
                        nom: optionsBagage[optionId].nom,
                        quantite: quantite,
                        total: supplement
                    });
                    
                    // Mettre à jour le total par option
                    document.getElementById(`bagage_total_${optionId}`).textContent = 
                        supplement.toFixed(2).replace('.', ',') + '€';
                } else {
                    document.getElementById(`bagage_total_${optionId}`).textContent = '0,00€';
                }
            });
            
            const total = (prixBase * nbPassagers) + supplements + supplementBagages + fraisService + taxesAeroport;
            
            document.getElementById('passagers-count').textContent = nbPassagers;
            document.getElementById('prix-base').textContent = (prixBase * nbPassagers).toFixed(2).replace('.', ',') + '€';
            document.getElementById('supplements-total').textContent = supplements.toFixed(2).replace('.', ',') + '€';
            document.getElementById('supplements-bagage').textContent = supplementBagages.toFixed(2).replace('.', ',') + '€';
            document.getElementById('prix-total').textContent = total.toFixed(2).replace('.', ',') + '€';
            
            // Mettre à jour l'affichage des sièges et bagages sélectionnés
            afficherSiegesSelectionnes(siegesSelectionnes);
            afficherBagagesSelectionnes(bagagesSelectionnes);
        }

        function afficherSiegesSelectionnes(siegesIds) {
            const container = document.getElementById('selected-seats-display');
            const list = document.getElementById('selected-seats-list');
            
            if (siegesIds.length === 0) {
                container.style.display = 'none';
                return;
            }
            
            list.innerHTML = '';
            siegesIds.forEach(siegeId => {
                const nomSiege = nomsSieges[siegeId];
                const supplement = supplementsSieges[siegeId] || 0;
                const badge = document.createElement('div');
                badge.className = 'seat-badge';
                badge.innerHTML = `<i class="fas fa-chair"></i> ${nomSiege} ${supplement > 0 ? '(+' + supplement + '€)' : ''}`;
                list.appendChild(badge);
            });
            
            container.style.display = 'block';
        }

        function afficherBagagesSelectionnes(bagages) {
            const container = document.getElementById('selected-bagages-display');
            const list = document.getElementById('selected-bagages-list');
            
            if (bagages.length === 0) {
                container.style.display = 'none';
                return;
            }
            
            list.innerHTML = '';
            bagages.forEach(bagage => {
                const badge = document.createElement('div');
                badge.className = 'bagage-badge';
                badge.innerHTML = `
                    <i class="fas fa-suitcase"></i> 
                    ${bagage.quantite}x ${bagage.nom} 
                    (${bagage.total.toFixed(2).replace('.', ',')}€)
                `;
                list.appendChild(badge);
            });
            
            container.style.display = 'block';
        }

        function toggleSiege(checkbox) {
            const siege = checkbox.parentElement;
            const maxSieges = parseInt(document.getElementById('nombre_passagers').value);
            const siegesSelectionnes = document.querySelectorAll('input[name="sieges[]"]:checked');
            
            if (checkbox.checked && siegesSelectionnes.length > maxSieges) {
                checkbox.checked = false;
                siege.classList.remove('selectionne');
                alert(`Vous ne pouvez sélectionner que ${maxSieges} siège(s) pour ${maxSieges} passager(s)`);
                return;
            }
            
            siege.classList.toggle('selectionne', checkbox.checked);
            calculerPrix();
            
            // Mettre à jour la disponibilité des autres sièges
            document.querySelectorAll('input[name="sieges[]"]').forEach(cb => {
                if (!cb.checked && cb.disabled === false) {
                    cb.disabled = (siegesSelectionnes.length >= maxSieges);
                }
            });
        }

        // Gestion des boutons quantité pour les bagages
        document.querySelectorAll('.btn-qty').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = this.getAttribute('data-target');
                const input = document.getElementById(target);
                let value = parseInt(input.value) || 0;
                
                if (this.classList.contains('plus')) {
                    value++;
                } else if (this.classList.contains('minus') && value > 0) {
                    value--;
                }
                
                input.value = value;
                calculerPrix();
            });
        });

        // Timer de réservation
        let timer = 15 * 60; // 15 minutes en secondes
        const timerElement = document.getElementById('reservation-timer');

        function updateTimer() {
            const minutes = Math.floor(timer / 60);
            const seconds = timer % 60;
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timer <= 0) {
                clearInterval(timerInterval);
                alert('Temps écoulé ! Votre réservation a expiré.');
                window.location.href = 'index.php';
            }
            
            // Changement de couleur pour les 5 dernières minutes
            if (timer <= 300) {
                timerElement.parentElement.style.background = 'var(--danger)';
            } else if (timer <= 600) {
                timerElement.parentElement.style.background = 'var(--warning)';
            }
            
            timer--;
        }

        const timerInterval = setInterval(updateTimer, 1000);

        // Gestion de la soumission du formulaire
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            const nbPassagers = parseInt(document.getElementById('nombre_passagers').value);
            const siegesSelectionnes = document.querySelectorAll('input[name="sieges[]"]:checked');
            
            if (siegesSelectionnes.length !== nbPassagers) {
                e.preventDefault();
                alert(`Vous devez sélectionner exactement ${nbPassagers} siège(s) pour ${nbPassagers} passager(s)`);
                return;
            }
            
            // Afficher le loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submit-btn').disabled = true;
            
            // Arrêter le timer
            clearInterval(timerInterval);
        });

        // Initialisation
        document.getElementById('nombre_passagers').addEventListener('change', function() {
            const maxSieges = parseInt(this.value);
            const siegesSelectionnes = document.querySelectorAll('input[name="sieges[]"]:checked');
            
            // Désélectionner les sièges en trop
            if (siegesSelectionnes.length > maxSieges) {
                for (let i = maxSieges; i < siegesSelectionnes.length; i++) {
                    siegesSelectionnes[i].checked = false;
                    siegesSelectionnes[i].parentElement.classList.remove('selectionne');
                }
            }
            
            // Mettre à jour la disponibilité
            document.querySelectorAll('input[name="sieges[]"]').forEach(checkbox => {
                if (!checkbox.checked && checkbox.disabled === false) {
                    checkbox.disabled = (document.querySelectorAll('input[name="sieges[]"]:checked').length >= maxSieges);
                }
            });
            
            calculerPrix();
        });

        // Initialiser le calcul du prix
        calculerPrix();

        // Empêcher la fermeture de la page pendant la réservation
        window.addEventListener('beforeunload', function(e) {
            if (document.querySelectorAll('input[name="sieges[]"]:checked').length > 0) {
                e.preventDefault();
                e.returnValue = 'Votre réservation en cours sera perdue. Êtes-vous sûr de vouloir quitter ?';
            }
        });

        // Raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                document.getElementById('submit-btn').click();
            }
        });
    </script>
</body>
</html>