<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Paw Hubs</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary: #6BB5A8;
            --primary-dark: #4f9186;
            --green: #9BC870;
            --olive: #CAD7A5;
            --secondary: #94CDD3;
            --bg-color: #C8E4D6;
            --text-dark: #2f4f4f;
            --white: #ffffff;
            --error: #ff4d4d;
        }

        * { box-sizing: border-box; transition: all 0.3s ease; }

        body {
            margin: 0; padding: 0;
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--secondary) 100%);
            min-height: 100vh;
            display: flex; justify-content: center; align-items: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            padding: 50px 40px;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            width: 100%; max-width: 420px;
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-section { text-align: center; margin-bottom: 35px; }
        .logo-section i { font-size: 50px; color: var(--primary); margin-bottom: 15px; }
        .logo-section h2 { margin: 0; font-size: 28px; color: var(--text-dark); }

        .error-box {
            background: rgba(255, 77, 77, 0.1);
            border-left: 4px solid var(--error);
            color: var(--error);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex; align-items: center; gap: 10px;
        }

        .input-group { margin-bottom: 22px; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--primary); }
        .input-wrapper input {
            width: 100%; padding: 14px 16px 14px 48px;
            border: 2px solid #d8ebe5; border-radius: 14px;
            outline: none; background: #f5faf8;
        }
        .input-wrapper input:focus { border-color: var(--primary); background: var(--white); }

        .toggle-password { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--secondary); }

        button[type="submit"] {
            width: 100%; padding: 16px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none; border-radius: 14px;
            color: white; font-size: 16px; font-weight: 650;
            cursor: pointer; box-shadow: 0 10px 20px -5px rgba(107, 181, 168, 0.4);
        }

        .footer-links { text-align: center; margin-top: 30px; }
        .footer-links a { color: var(--primary); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <i class="fas fa-paw"></i>
            <h2>Welcome Back!</h2>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($errors[0]) ?></span>
            </div>
        <?php endif; ?>

        <form action="index.php?url=auth/login" method="post">
            <div class="input-group">
                <div class="input-wrapper">
                    <i class="far fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
            </div>

            <div class="input-group">
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="pass" id="passwordField" placeholder="Password" required>
                    <i class="far fa-eye toggle-password" id="togglePassword"></i>
                </div>
            </div>

            <button type="submit">Sign In</button>
        </form>

        <div class="footer-links">
            Don't have an account? <a href="index.php?url=auth/register">Create Account</a>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#passwordField');
        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
