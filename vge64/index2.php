<?php include('../includes/connexion.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>JetReserve ✈️ | Réservez vos vols au meilleur prix</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>


<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">JetReserve ✈️</a>
    <div>
      <a href="connexion.php" class="btn btn-light btn-sm me-2">Se connecter</a>
      <a href="inscription.php" class="btn btn-outline-light btn-sm">Créer un compte</a>
    </div>
  </div>
</nav>


<section class="bg-light py-5 text-center">
  <div class="container">
    <h2 class="mb-4 fw-bold">Trouvez votre vol en un clic ✈️</h2>

    <form method="GET" action="pages/resultats.php" class="row justify-content-center g-3">
      <div class="col-md-2">
        <input type="text" name="depart" class="form-control" placeholder="Départ" required>
      </div>
      <div class="col-md-2">
        <input type="text" name="arrivee" class="form-control" placeholder="Arrivée" required>
      </div>
      <div class="col-md-2">
        <input type="date" name="date_depart" class="form-control" required>
      </div>
      <div class="col-md-2">
        <select name="classe" class="form-select">
          <option value="économique">Économique</option>
          <option value="affaires">business</option>
          <option value="première">Première</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
      </div>
    </form>
  </div>
</section>


<section class="container text-center my-5">
  <h3 class="fw-bold">Les meilleures offres du moment 💸</h3>
  <p class="text-muted">Réservez maintenant avant la fin des promotions !</p>
  <div class="row mt-4">
    <div class="col-md-4">
      <div class="card shadow-sm">
        <img src="assets/images/paris.jpg" class="card-img-top" alt="Paris">
        <div class="card-body">
          <h5>Paris ✈️ Abidjan</h5>
          <p>À partir de <strong>450 €</strong></p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm">
        <img src="assets/images/dubai.jpg" class="card-img-top" alt="Dubai">
        <div class="card-body">
          <h5>Dubaï ✈️ Abidjan</h5>
          <p>À partir de <strong>700 €</strong></p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm">
        <img src="assets/images/istanbul.jpg" class="card-img-top" alt="Istanbul">
        <div class="card-body">
          <h5>Maroc✈️ Paris</h5>
          <p>À partir de <strong>350 €</strong></p>
        </div>
      </div>
    </div>
  </div>
</section>


<footer class="bg-primary text-light text-center py-3 mt-5">
  <p>© 2025 JetReserve | Tous droits réservés.</p>
</footer>

</body>
</html>
