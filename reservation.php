<?php
// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Augmenter le temps d'exécution maximum
set_time_limit(60);
ini_set('max_execution_time', 60);

// Ajouter des logs pour déboguer
error_log("=== DÉBUT DE LA PAGE RESERVATION ===" . date('Y-m-d H:i:s') . " ====");
error_log("URL complète: " . $_SERVER['REQUEST_URI']);
error_log("Méthode de requête: " . $_SERVER['REQUEST_METHOD']);
error_log("Données POST: " . (empty($_POST) ? 'vide' : 'présentes'));

// DÉBUT DU BUFFER AMÉLIORÉ
if (ob_get_level() == 0) {
    ob_start();
}

// Démarrer la session avec des paramètres optimisés
session_start();

// Désactiver la mise en cache pour éviter les problèmes de redirection
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    // Nettoyer proprement le buffer avant redirection
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    $redirect = isset($_SERVER['REQUEST_URI']) ? urlencode($_SERVER['REQUEST_URI']) : '';
    header('Location: vge64/connexion.php?redirect=' . $redirect);
    exit;
}

// Récupérer l'ID du vol depuis l'URL
$id_vol = isset($_GET['id_vol']) ? intval($_GET['id_vol']) : 0;

if ($id_vol <= 0) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
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
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération du vol: " . $e->getMessage());
}

// Récupérer les sièges disponibles ET occupés pour ce vol
try {
    // Récupérer tous les sièges de l'avion
    $stmt = $pdo->prepare("
        SELECT sa.* 
        FROM sieges_avion sa
        WHERE sa.id_avion = ? 
        AND sa.statut = 'actif'
        ORDER BY sa.rang, sa.position
    ");
    $stmt->execute([$vol['id_avion']]);
    $tous_sieges = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les sièges occupés
    $stmt_occupes = $pdo->prepare("
        SELECT rs.id_siege
        FROM reservation_sieges rs
        JOIN reservations r ON rs.id_reservation = r.id_reservation
        WHERE r.id_vol = ? AND r.statut IN ('confirmé', 'en attente')
    ");
    $stmt_occupes->execute([$id_vol]);
    $sieges_occupes = $stmt_occupes->fetchAll(PDO::FETCH_COLUMN);

    // Filtrer les sièges disponibles
    $sieges_disponibles = array_filter($tous_sieges, function($siege) use ($sieges_occupes) {
        return !in_array($siege['id_siege'], $sieges_occupes);
    });

} catch (PDOException $e) {
    error_log("Erreur sièges: " . $e->getMessage());
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

// Traitement du formulaire de réservation - VERSION CORRIGÉE
$erreur = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserver'])) {
    
    echo "<div style='background: purple; color: white; padding: 20px; margin: 20px;'>FORMULAIRE REÇU PAR LE SERVEUR !</div>";
    
    $nombre_passagers = intval($_POST['nombre_passagers']);
    $sieges_selectionnes = isset($_POST['sieges']) ? array_map('intval', $_POST['sieges']) : [];
    $bagages_selectionnes = isset($_POST['bagages']) ? $_POST['bagages'] : [];
    
    echo "<div style='background: cyan; color: black; padding: 20px; margin: 20px;'>Données: Passagers=$nombre_passagers, Sièges=" . count($sieges_selectionnes) . "</div>";
    
    
    // Validation
    $erreurs = [];
    
    if ($nombre_passagers <= 0 || $nombre_passagers > 10) {
        $erreurs[] = "Nombre de passagers invalide";
    }
    
    if (count($sieges_selectionnes) === 0) {
        $erreurs[] = "Vous devez sélectionner au moins un siège";
    }
    
    // Vérification rapide de la disponibilité des sièges
    foreach ($sieges_selectionnes as $siege_id) {
        if (in_array($siege_id, $sieges_occupes)) {
            $erreurs[] = "Un des sièges sélectionnés n'est plus disponible";
            break;
        }
    }
    
    if (empty($erreurs)) {
        try {
            // DÉBUT DE LA TRANSACTION
            $pdo->beginTransaction();
            
            error_log("=== DÉBUT RÉSERVATION ===");
            echo "<div style='background: green; color: white; padding: 20px; margin: 20px;'>DÉBUT RÉSERVATION - Pas d'erreurs de validation</div>";
            
            // CALCUL RAPIDE DU PRIX
            $prix_total = $vol['prix'] * $nombre_passagers;
            
            // Suppléments sièges
            foreach ($sieges_selectionnes as $id_siege) {
                foreach ($tous_sieges as $siege) {
                    if ($siege['id_siege'] == $id_siege) {
                        $prix_total += $siege['supplement_prix'];
                        break;
                    }
                }
            }
            
            // Suppléments bagages
            foreach ($bagages_selectionnes as $id_option => $quantite) {
                $quantite = intval($quantite);
                if ($quantite > 0) {
                    foreach ($options_bagage as $option) {
                        if ($option['id_option'] == $id_option) {
                            $prix_total += $option['prix_supplement'] * $quantite;
                            break;
                        }
                    }
                }
            }
            
            // Frais fixes
            $prix_total += 9.00 + 25.00;
            
            error_log("Prix total calculé: $prix_total");
            
            // CRÉATION DE LA RÉSERVATION
            $stmt = $pdo->prepare("
                INSERT INTO reservations (id_user, id_vol, statut, nombre_passagers, prix_total, date_reservation)
                VALUES (?, ?, 'confirmé', ?, ?, NOW())
            ");
            $stmt->execute([$_SESSION['id_user'], $id_vol, $nombre_passagers, $prix_total]);
            $id_reservation = $pdo->lastInsertId();
            
            if (!$id_reservation) {
                throw new Exception("Échec de la création de la réservation");
            }
            
            error_log("Réservation créée avec ID: $id_reservation");
            
            // ASSOCIATION DES SIÈGES
            $stmt_siege = $pdo->prepare("
                INSERT INTO reservation_sieges (id_reservation, id_siege, prix_paye)
                VALUES (?, ?, ?)
            ");
            
            foreach ($sieges_selectionnes as $id_siege) {
                $prix_siege = $vol['prix'];
                foreach ($tous_sieges as $siege) {
                    if ($siege['id_siege'] == $id_siege) {
                        $prix_siege += $siege['supplement_prix'];
                        break;
                    }
                }
                $stmt_siege->execute([$id_reservation, $id_siege, $prix_siege]);
            }
            
            error_log("Sièges associés: " . count($sieges_selectionnes));
            
            // ASSOCIATION DES BAGAGES
            if (!empty($bagages_selectionnes)) {
                $stmt_bagage = $pdo->prepare("
                    INSERT INTO reservation_bagages (id_reservation, id_option, quantite, prix_applique)
                    VALUES (?, ?, ?, ?)
                ");
                
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
                        $stmt_bagage->execute([$id_reservation, $id_option, $quantite, $prix_option]);
                    }
                }
                error_log("Bagages associés: " . count(array_filter($bagages_selectionnes)));
            }
            
            // ENREGISTREMENT DU PAIEMENT
            $stmt_paiement = $pdo->prepare("
                INSERT INTO paiements (id_reservation, montant, mode_paiement, statut, date_paiement)
                VALUES (?, ?, 'carte', 'réussi', NOW())
            ");
            $stmt_paiement->execute([$id_reservation, $prix_total]);
            error_log("Paiement enregistré");
            
            // MISE À JOUR DES PLACES DISPONIBLES
            $stmt = $pdo->prepare("
                UPDATE vols 
                SET places_disponibles = GREATEST(0, places_disponibles - ?)
                WHERE id_vol = ?
            ");
            $stmt->execute([$nombre_passagers, $id_vol]);
            error_log("Places mises à jour");
            
            // EMAIL (optionnel - ne pas bloquer en cas d'erreur)
            try {
                $stmt_email = $pdo->prepare("
                    INSERT INTO emails (id_user, sujet, contenu, type, statut, date_envoi)
                    VALUES (?, ?, ?, 'confirmation', 'en attente', NOW())
                ");
                $sujet_email = "Confirmation de votre réservation JetReserve";
                $contenu_email = "Votre réservation n°" . $id_reservation . " pour le vol " . $vol['depart'] . " - " . $vol['arrivee'] . " a été confirmée. Montant total : " . number_format($prix_total, 2, ',', ' ') . "€";
                $stmt_email->execute([$_SESSION['id_user'], $sujet_email, $contenu_email]);
                error_log("Email programmé");
            } catch (Exception $e) {
                error_log("Erreur email non critique: " . $e->getMessage());
            }
            
            // VALIDATION DE LA TRANSACTION
            $pdo->commit();
            
            error_log("=== RÉSERVATION RÉUSSIE ===");
            echo "<div style='background: blue; color: white; padding: 20px; margin: 20px;'>RÉSERVATION RÉUSSIE - ID: $id_reservation</div>";
            
            // VIDER LE BUFFER ET REDIRIGER PROPREMENT
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Rediriger vers la page de confirmation
<<<<<<< HEAD
            header('Location: confirmation_reservation.php?id_reservation=' . $id_reservation);
=======
            error_log("Redirection vers paiement.php avec reservation_id=$id_reservation");
            header('Location: paiement.php?reservation_id=' . $id_reservation);
>>>>>>> 8161cc1ad516c2df9fe8064c8f9bfff11ee16d63
            exit;
            
        } catch (Exception $e) {
            // ANNULATION DE LA TRANSACTION EN CAS D'ERREUR
            $pdo->rollBack();
            $erreur_message = "Erreur lors de la réservation: " . $e->getMessage();
            $erreurs[] = $erreur_message;
            error_log("=== ERREUR RÉSERVATION: " . $e->getMessage() . " ===");
            echo "<div style='background: red; color: white; padding: 20px; margin: 20px;'>ERREUR: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
    if (!empty($erreurs)) {
        $erreur = implode("<br>", $erreurs);
        echo "<div style='background: orange; color: white; padding: 20px; margin: 20px;'>ERREURS DE VALIDATION:<br>" . htmlspecialchars($erreur) . "</div>";
    }
}

// Si on arrive ici, c'est qu'on affiche le formulaire ou qu'il y a une erreur
// On flush le buffer normalement
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
        /* STYLE INSPIRÉ DE CORSAIR */
        :root {
            --primary: #0054A4;
            --secondary: #7f8c8d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --white: #ffffff;
            --gray: #6c757d;
            --light-gray: #f9fafb;
            --border-color: #dee2e6;
            --premium: #FFD700;
            --business: #6f42c1;
            --corsair-blue: #0054A4;
            --corsair-dark-blue: #003366;
            --corsair-light-blue: #0077CC;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        header {
            background-color: var(--white);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--corsair-blue);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo span {
            color: var(--danger);
        }
        
        .logo i {
            font-size: 32px;
        }
        
        .auth-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .user-welcome {
            color: var(--corsair-blue);
            font-weight: 500;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-align: center;
            font-size: 14px;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--corsair-blue);
            color: var(--corsair-blue);
        }
        
        .btn-primary {
            background-color: var(--corsair-blue);
            color: var(--white);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #1e7e34);
            color: var(--white);
            font-weight: 600;
            font-size: 16px;
            padding: 15px 30px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn-lg {
            padding: 12px 24px;
            font-size: 1.1rem;
        }
        
        .btn-full {
            width: 100%;
            justify-content: center;
        }
        
        .reservation-container {
            max-width: 1000px;
            margin: 2rem auto;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .reservation-header {
            background: linear-gradient(135deg, var(--corsair-blue), var(--corsair-dark-blue));
            color: var(--white);
            padding: 2.5rem;
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
            font-size: 2rem;
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
            height: 3px;
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
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--white);
            border: 3px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .step.active .step-number {
            background: var(--corsair-blue);
            color: var(--white);
            border-color: var(--corsair-blue);
        }
        
        .step.completed .step-number {
            background: var(--success);
            color: var(--white);
            border-color: var(--success);
        }
        
        .step-label {
            font-size: 0.9rem;
            color: var(--secondary);
            font-weight: 500;
        }
        
        .step.active .step-label {
            color: var(--corsair-blue);
            font-weight: 600;
        }
        
        .vol-resume {
            padding: 2rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--white);
        }
        
        .vol-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 5px solid var(--corsair-blue);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .info-label {
            font-weight: 600;
            color: var(--corsair-blue);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            font-size: 1.2rem;
            color: var(--dark);
            font-weight: 600;
        }
        
        .reservation-form {
            padding: 2rem;
        }
        
        .form-section {
            margin-bottom: 2.5rem;
            padding: 2rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: var(--white);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .form-section:hover {
            border-color: var(--corsair-blue);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        
        .section-title {
            color: var(--corsair-blue);
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .section-title i {
            color: var(--corsair-blue);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--corsair-blue);
            box-shadow: 0 0 0 3px rgba(0, 84, 164, 0.1);
        }
        
        .sieges-container {
            margin-top: 1.5rem;
        }
        
        .avion-schema {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            position: relative;
            border: 1px solid var(--border-color);
        }
        
        .cockpit {
            background: var(--corsair-blue);
            color: white;
            padding: 1.5rem;
            text-align: center;
            border-radius: 12px 12px 0 0;
            margin-bottom: 2rem;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .rangee-sieges {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 15px;
        }
        
        .numero-rangee {
            width: 35px;
            text-align: center;
            font-weight: 700;
            color: var(--corsair-blue);
            font-size: 1.1rem;
        }
        
        .siege {
            width: 45px;
            height: 45px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            position: relative;
        }
        
        .siege.disponible {
            background: var(--white);
            border-color: var(--success);
        }
        
        .siege.disponible:hover {
            background: var(--success);
            color: var(--white);
            transform: scale(1.15);
        }
        
        .siege.selectionne {
            background: var(--corsair-blue);
            color: var(--white);
            border-color: var(--corsair-blue);
            transform: scale(1.15);
        }
        
        .siege.occupe {
            background: var(--danger);
            color: var(--white);
            border-color: var(--danger);
            cursor: not-allowed;
        }
        
        .siege.premium {
            border-color: var(--premium);
            background: linear-gradient(135deg, #fff9e6 0%, #ffefb3 100%);
        }
        
        .siege.business {
            border-color: var(--business);
            background: linear-gradient(135deg, #f0e6ff 0%, #d9c3ff 100%);
        }
        
        .siege::after {
            content: attr(data-supplement);
            position: absolute;
            top: -25px;
            font-size: 0.75rem;
            color: var(--corsair-blue);
            font-weight: 600;
        }
        
        .couloir {
            width: 50px;
            text-align: center;
            color: var(--secondary);
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .legende-sieges {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
            padding: 1rem;
            background: var(--white);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .legende-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .legende-couleur {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        /* Styles pour les options de bagage */
        .bagages-container {
            display: grid;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .bagage-option {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            background: var(--white);
            transition: all 0.3s ease;
        }
        
        .bagage-option:hover {
            border-color: var(--corsair-blue);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        
        .bagage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        
        .bagage-header h4 {
            color: var(--corsair-blue);
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .bagage-price {
            background: var(--success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1rem;
        }
        
        .bagage-description {
            color: var(--secondary);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }
        
        .bagage-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .btn-qty {
            width: 40px;
            height: 40px;
            border: 2px solid var(--border-color);
            background: var(--light);
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        
        .btn-qty:hover {
            background: var(--corsair-blue);
            color: white;
            border-color: var(--corsair-blue);
        }
        
        .bagage-qty {
            width: 70px;
            text-align: center;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .bagage-total {
            margin-left: auto;
            font-weight: bold;
            color: var(--corsair-blue);
            min-width: 80px;
            text-align: right;
            font-size: 1.1rem;
        }
        
        .selected-bagages {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1.5rem;
            display: none;
            border: 1px solid var(--corsair-light-blue);
        }
        
        .bagage-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--corsair-blue);
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: 25px;
            margin: 0.5rem;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .resume-prix {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }
        
        .ligne-prix {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 1.05rem;
        }
        
        .ligne-prix:last-child {
            border-bottom: none;
        }
        
        .prix-total {
            border-top: 3px solid var(--border-color);
            padding-top: 1rem;
            margin-top: 1rem;
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--corsair-blue);
        }
        
        .alert {
            padding: 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1rem;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 2px solid #a7f3d0;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 2px solid #93c5fd;
        }
        
        .selected-seats-display {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: none;
            border: 1px solid var(--corsair-light-blue);
        }
        
        .seat-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--corsair-blue);
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: 25px;
            margin: 0.5rem;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .timer {
            background: var(--warning);
            color: var(--dark);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
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
            
            .reservation-header {
                padding: 1.5rem;
            }
            
            .form-section {
                padding: 1.5rem;
            }
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--corsair-blue);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .flight-path {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1rem 0;
            gap: 1rem;
        }
        
        .flight-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--corsair-blue);
        }
        
        .flight-line {
            flex-grow: 1;
            height: 3px;
            background: linear-gradient(90deg, var(--corsair-blue), var(--corsair-light-blue));
            position: relative;
        }
        
        .flight-line::after {
            content: "✈";
            position: absolute;
            right: -10px;
            top: -12px;
            font-size: 1.5rem;
            color: var(--corsair-blue);
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">
                    <i class="fas fa-plane"></i>
                    Jet<span>Reserve</span>
                </a>
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
                
                <!-- Représentation du vol -->
                <div class="flight-path">
                    <div class="flight-dot"></div>
                    <div class="flight-line"></div>
                    <div class="flight-dot"></div>
                </div>
                
                <div class="vol-info-grid">
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-plane-departure"></i> Départ</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['depart']); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-plane-arrival"></i> Arrivée</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['arrivee']); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-calendar-alt"></i> Date et heure</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($vol['date_depart'])); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-building"></i> Compagnie</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['nom_compagnie']); ?></div>
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
                                    <div class="legende-couleur" style="background: var(--corsair-blue);"></div>
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
                        <strong>Inclus avec votre billet :</strong> 1 bagage à main (8kg) par passager
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
                timerElement.parentElement.style.color = 'white';
            } else if (timer <= 600) {
                timerElement.parentElement.style.background = 'var(--warning)';
                timerElement.parentElement.style.color = 'var(--dark)';
            }
            
            timer--;
        }

        const timerInterval = setInterval(updateTimer, 1000);

<<<<<<< HEAD
        // JavaScript temporairement désactivé pour debug
        console.log('JavaScript chargé mais gestionnaire de soumission désactivé');
=======
        // Gestion de la soumission du formulaire
        let formSubmitted = false;
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            // Empêcher les soumissions multiples
            if (formSubmitted) {
                console.log("Tentative de soumission multiple bloquée");
                e.preventDefault();
                return false;
            }
            
            const nbPassagers = parseInt(document.getElementById('nombre_passagers').value);
            const siegesSelectionnes = document.querySelectorAll('input[name="sieges[]"]:checked');
            
            if (siegesSelectionnes.length !== nbPassagers) {
                e.preventDefault();
                alert(`Vous devez sélectionner exactement ${nbPassagers} siège(s) pour ${nbPassagers} passager(s)`);
                return;
            }
            
            // Marquer le formulaire comme soumis
            formSubmitted = true;
            
            // Afficher le loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submit-btn').disabled = true;
            
            // Arrêter le timer
            clearInterval(timerInterval);
            
            // Désactiver tous les événements qui pourraient interférer
            window.onbeforeunload = null;
            
            // Désactiver tous les champs du formulaire pour éviter les modifications pendant la soumission
            Array.from(this.elements).forEach(element => {
                if (element.id !== 'submit-btn') {
                    element.disabled = true;
                }
            });
            
            // Soumission directe sans setTimeout pour éviter les problèmes
            try {
                console.log('Soumission du formulaire de réservation');
                
                // Soumission immédiate sans overlay ni délai
                this.submit();
            } catch (error) {
                console.error('Erreur lors de la soumission:', error);
                // En cas d'erreur, réactiver le formulaire
                formSubmitted = false;
                Array.from(this.elements).forEach(element => {
                    element.disabled = false;
                });
                document.getElementById('submit-btn').disabled = false;
                document.getElementById('loading').style.display = 'none';
            }
        });
>>>>>>> 8161cc1ad516c2df9fe8064c8f9bfff11ee16d63

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

        // beforeunload temporairement désactivé pour debug
        console.log('beforeunload désactivé pour debug');

        // Raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                document.getElementById('submit-btn').click();
            }
        });
    </script>
</body>
</html>