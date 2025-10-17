<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0078d4;
            --secondary: #005fa3;
            --light: #f5f8ff;
            --text: #333;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e6f0ff, #ffffff);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 60px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 30px 40px;
        }

        h1 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 25px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        label {
            font-weight: 600;
            color: var(--text);
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
        }

        button {
            background: var(--primary);
            color: white;
            font-size: 16px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: var(--secondary);
        }

        .retour {
            display: inline-block;
            margin-top: 15px;
            text-align: center;
            text-decoration: none;
            color: var(--primary);
            font-weight: 600;
        }

        .retour:hover {
            text-decoration: underline;
        }

        .logo {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo img {
            width: 80px;
        }

        .moyens {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .moyen {
            background: var(--light);
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .moyen:hover {
            border-color: var(--primary);
            transform: scale(1.03);
        }

        .moyen input {
            display: none;
        }

        .moyen label {
            display: block;
            cursor: pointer;
            font-weight: 500;
        }

        .moyen i {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo">
            <img src="images/logo.png" alt="JetReserve">
        </div>
        <h1>💳 Paiement sécurisé</h1>

        <form action="traitement_paiement.php" method="POST">
            <div>
                <label for="nom">Nom complet</label>
                <input type="text" id="nom" name="nom" placeholder="Entrez votre nom complet" required>
            </div>

            <div>
                <label for="telephone">Numéro de téléphone</label>
                <input type="text" id="telephone" name="telephone" placeholder="Ex : 0700000000" required>
            </div>

            <div>
                <label>Moyen de paiement</label>
                <div class="moyens">
                    <div class="moyen">
                        <input type="radio" id="orange" name="paiement" value="Orange Money" required>
                        <label for="orange"><i class="fas fa-mobile-alt"></i> Orange Money</label>
                    </div>
                    <div class="moyen">
                        <input type="radio" id="moov" name="paiement" value="Moov Money">
                        <label for="moov"><i class="fas fa-sim-card"></i> Moov Money</label>
                    </div>
                    <div class="moyen">
                        <input type="radio" id="mtn" name="paiement" value="MTN Money">
                        <label for="mtn"><i class="fas fa-wallet"></i> MTN Money</label>
                    </div>
                    <div class="moyen">
                        <input type="radio" id="wave" name="paiement" value="Wave">
                        <label for="wave"><i class="fas fa-wave-square"></i> Wave</label>
                    </div>
                </div>
            </div>

            <div>
                <label for="montant">Montant à payer (€)</label>
                <input type="number" id="montant" name="montant" placeholder="Ex : 120" required>
            </div>

            <button type="submit">Procéder au paiement</button>
        </form>

        <a href="index.php" class="retour">⬅ Retour à l'accueil</a>
    </div>

</body>
</html>
