<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>e-Voting Admin</title>
  <link rel="stylesheet" href="style/style.css">
</head>
<body>

  <header>
    <h1>Plateforme de Vote - Administrateur</h1>
    <button id="menu-toggle">&#9776;</button>
  </header>

  <aside id="sidebar">
    <nav>
      <ul>
        <li><a href="#">Statistiques</a></li>
        <li><a href="#">Structure</a></li>
        <li><a href="#">Paramètres</a></li>
        <li><a href="#">Utilisateurs</a></li>
        <li><a href="#">Déconnexion</a></li>
      </ul>
    </nav>
  </aside>

  <main>
    <section class="login-box">
      <h2>Connexion Admin</h2>
      <form>
        <label for="email">Email</label>
        <input type="email" id="email" placeholder="Email admin" required>

        <label for="password">Mot de passe</label>
        <input type="password" id="password" placeholder="Mot de passe" required>

        <button type="submit">Se connecter</button>
      </form>
    </section>
  </main>

  <script src="js/js.js"></script>
</body>
</html>
