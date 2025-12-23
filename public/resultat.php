<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Vote Manager - Résultats</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <a href="./scrutin.php" class="back-btn">← Retour</a>
    <h1 style="font-size: small;">Résultats du scrutin  <span id="post-title"></span> </h1>
  </header>

  <main>
    <div id="resultsContainer" class="card-list">
      <!-- Résultats par poste seront injectés ici -->
    </div>
  </main>

  <script type="module" src="./js/components/resultat.js"></script>
</body>
</html>
