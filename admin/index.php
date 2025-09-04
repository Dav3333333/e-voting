<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>e-Voting Admin</title>
  <link rel="stylesheet" href="style/main.css">
</head>
<body>

  <header>
    <h1>E-Voting - Administrateur</h1>
    <button id="menu-toggle">&#9776;</button>
  </header>

  <aside id="sidebar">
    <nav>
      <ul>
        <li><a class="menu-link active" id="statistics" href="#statistics">Statistiques</a></li>
        <li><a class="menu-link" id="poll" href="#poll">Scrutin</a></li>
        <li><a class="menu-link" id="users" href="#users">Utilisateurs</a></li>
        <li><a class="menu-link" id="logout" href="#logout">Déconnexion</a></li>
      </ul>
    </nav>
  </aside>

  <main>
    
    <div></div>
    
    <dialog id="close-dialog" class="dialog">
  
    </dialog>
  
  </main>
  <script src="./js/js.js"></script>
  <script type="module" src="./js/admin.js"></script>
</body>
</html>
