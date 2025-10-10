<?php
session_start();
include('../includes/connexion.php');

$message = "";

if (isset($_POST['connecter'])) {
    $email = trim($_POST['email']);
    $mdp = $_POST['motdepasse'];

    $query = $bdd->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    $user = $query->fetch();

    if ($user && password_verify($mdp, $user['motdepasse'])) {
        $_SESSION['id_user'] = $user['id'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['email'] = $user['email'];

        header("Location: index.php");
        exit;
    } else {
        $message = "❌ Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion | JetReserve</title>
    <link rel="stylesheet" href="../assets/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Connexion à <span style="color:#00c3ff;">JetReserve</span></h2>
        <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

        <form method="post">
            <label>Email :</label>
            <input type="email" name="email" placeholder="Votre adresse email" required>

            <label>Mot de passe :</label>
            <input type="password" name="motdepasse" placeholder="Votre mot de passe" required>

            <button type="submit" name="connecter">Se connecter</button>
        </form>

        <p>Pas encore de compte ? <a href="inscription.php">Créer un compte</a></p>
        <p><a href="../index.php">⬅ Retour à l'accueil</a></p>
    </div>
</body>
</html>
