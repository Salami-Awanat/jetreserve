<?php
include('../includes/connexion.php');
$message = "";

if (isset($_POST['inscrire'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mdp = password_hash($_POST['motdepasse'], PASSWORD_DEFAULT);

    $verif = $bdd->prepare("SELECT * FROM users WHERE email = ?");
    $verif->execute([$email]);

    if ($verif->rowCount() > 0) {
        $message = "⚠️ Cet email est déjà utilisé.";
    } else {
        $insert = $bdd->prepare("INSERT INTO users (nom, prenom, email, motdepasse) VALUES (?, ?, ?, ?)");
        $insert->execute([$nom, $prenom, $email, $mdp]);
        $message = "✅ Inscription réussie ! Vous pouvez maintenant vous connecter.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription | JetReserve</title>
    <link rel="stylesheet" href="../assets/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Créer un compte <span style="color:#00c3ff;">JetReserve</span></h2>
        <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

        <form method="post">
            <label>Nom :</label>
            <input type="text" name="nom" placeholder="Votre nom" required>

            <label>Prénom :</label>
            <input type="text" name="prenom" placeholder="Votre prénom" required>

            <label>Email :</label>
            <input type="email" name="email" placeholder="Votre adresse email" required>

            <label>Mot de passe :</label>
            <input type="password" name="motdepasse" placeholder="Choisissez un mot de passe" required>

            <button type="submit" name="inscrire">Créer un compte</button>
        </form>

        <p>Déjà un compte ? <a href="connexion.php">Connectez-vous</a></p>
        <p><a href="../index.php">⬅ Retour à l'accueil</a></p>
    </div>
</body>
</html>
