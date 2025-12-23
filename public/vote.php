<?php 

// m form mode (true if is card mode and false if not)
if(!isset($_GET['id'], $_GET['c'])) die("<p> Une erreur interne a interrompu l'execution </p>");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Vote Manager - Scrutin</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="./style/formcard.css">
</head>
<body>
  <header style="display: flex; justify-content: space-between; align-items: center; padding: 10px;">
    <a href="./scrutin.php" class="back-btn">← Retour</a>
    <h1 id="title"></h1>
  </header>

  <main>
    <div id="candidatsContainer" class="card-list">
      
    </div>
  </main>

  <script type="module" src="./js/components/vote.js"></script>
</body>
</html>
