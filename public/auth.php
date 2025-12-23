<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification</title>
</head>
<body>
    <style>
        body {
            background: #f4f6f8;
            font-family: Arial, sans-serif;
        }
        .login-container {
            background: #fff;
            max-width: 350px;
            margin: 60px auto;
            padding: 32px 28px 24px 28px;
            border-radius: 8px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 24px;
            color: #222;
        }
        .login-container label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-size: 15px;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 15px;
            background: #fafbfc;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .login-container button:hover {
            background: #125ea8;
        }
        .login-container .footer {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #888;
        }
    </style>
    <div class="login-container">
        <h2 id="form-title">Connexion</h2>
        <form id="auth-form" method="post" action="login_process.php">
            <div id="name-field" style="display:none;">
                <label for="name">Nom</label>
                <input type="text" id="name" name="name">
            </div>
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" id="submit-btn">Se connecter</button>
        </form>
        <div class="footer">
            <span id="toggle-link" style="cursor:pointer; color:#1976d2;">Créer un compte</span>
            <br>
            &copy; <?php echo date('Y'); ?> eVoting
        </div>
    </div>
    <script>
        const toggleLink = document.getElementById('toggle-link');
        const formTitle = document.getElementById('form-title');
        const authForm = document.getElementById('auth-form');
        const nameField = document.getElementById('name-field');
        const submitBtn = document.getElementById('submit-btn');

        let isRegister = false;

        toggleLink.addEventListener('click', function() {
            isRegister = !isRegister;
            if(isRegister) {
                formTitle.textContent = 'Inscription';
                nameField.style.display = 'block';
                submitBtn.textContent = "S'inscrire";
                authForm.action = 'register_process.php';
                toggleLink.textContent = 'Déjà un compte ? Se connecter';
            } else {
                formTitle.textContent = 'Connexion';
                nameField.style.display = 'none';
                submitBtn.textContent = "Se connecter";
                authForm.action = 'login_process.php';
                toggleLink.textContent = 'Créer un compte';
            }
        // Show/hide additional fields for registration
        const matriculeInput = document.createElement('input');
        matriculeInput.type = 'text';
        matriculeInput.id = 'matricule';
        matriculeInput.name = 'matricule';
        matriculeInput.placeholder = 'Matricule';
        matriculeInput.style.marginBottom = '18px';

        const matriculeLabel = document.createElement('label');
        matriculeLabel.htmlFor = 'matricule';
        matriculeLabel.textContent = 'Matricule';

        const emailInput = document.createElement('input');
        emailInput.type = 'text';
        emailInput.id = 'email';
        emailInput.name = 'email';
        emailInput.placeholder = 'Email';
        emailInput.style.marginBottom = '18px';

        const emailLabel = document.createElement('label');
        emailLabel.htmlFor = 'email';
        emailLabel.textContent = 'Email';

        const rfidInput = document.createElement('input');
        rfidInput.type = 'text';
        rfidInput.id = 'rfid';
        rfidInput.name = 'rfid';
        rfidInput.placeholder = 'RFID';
        rfidInput.style.marginBottom = '18px';

        const rfidLabel = document.createElement('label');
        rfidLabel.htmlFor = 'rfid';
        rfidLabel.textContent = 'RFID';

        function showRegisterFields() {
            // Insert after name field
            const nameFieldDiv = document.getElementById('name-field');
            // Matricule
            nameFieldDiv.insertAdjacentElement('afterend', matriculeInput);
            nameFieldDiv.insertAdjacentElement('afterend', matriculeLabel);
            // Email
            matriculeInput.insertAdjacentElement('afterend', emailInput);
            matriculeLabel.insertAdjacentElement('afterend', emailLabel);
            // RFID
            emailInput.insertAdjacentElement('afterend', rfidInput);
            emailLabel.insertAdjacentElement('afterend', rfidLabel);
        }

        function hideRegisterFields() {
            [matriculeInput, matriculeLabel, emailInput, emailLabel, rfidInput, rfidLabel].forEach(el => {
                if (el.parentNode) el.parentNode.removeChild(el);
            });
        }

        if(isRegister) {
            showRegisterFields();
        } else {
            hideRegisterFields();
        }
    });
    // On page load, ensure only login fields are shown
    hideRegisterFields();
    </script>
</body>
</html>