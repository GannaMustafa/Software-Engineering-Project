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

        * {
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--secondary) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            padding: 50px 40px;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 420px;
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-section i {
            font-size: 50px;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .logo-section h2 {
            margin: 0;
            font-size: 28px;
            color: var(--text-dark);
        }

        .error-box {
            background: rgba(255, 77, 77, 0.1);
            border-left: 4px solid var(--error);
            color: var(--error);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .input-group {
            margin-bottom: 22px;
        }

        .input-wrapper {
            position: relative;
        }
.input-wrapper i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary);
}

.input-wrapper #lock {
    left: 16px;
}

.input-wrapper .toggle-password {
    right: 16px;
    left: auto;
    cursor: pointer;
    color: var(--secondary);
}
        

        .input-wrapper input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #d8ebe5;
            border-radius: 14px;
            outline: none;
            background: #f5faf8;
        }

        .input-wrapper input:focus {
            border-color: var(--primary);
            background: var(--white);
        }

        

        button[type="submit"] {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 14px;
            color: white;
            font-size: 16px;
            font-weight: 650;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(107, 181, 168, 0.4);
        }

        .suspend-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .suspend-overlay.show {
            display: flex;
        }

        .suspend-box {
            background: white;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .suspend-icon {
            width: 80px;
            height: 80px;
            background: #fed7d7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }

        .suspend-box h2 {
            color: #e53e3e;
            margin-bottom: 12px;
            font-size: 24px;
        }

        .suspend-box p {
            color: #4a5568;
            margin-bottom: 24px;
            line-height: 1.6;
            font-size: 15px;
        }

        .suspend-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .suspend-btn:hover {
            background: var(--error);
        }

        .footer-links {
            text-align: center;
            margin-top: 30px;
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<?php


$suspendMsg = $_SESSION['suspend_msg'] ?? '';
$isSuspended = isset($_GET['suspended']) || !empty($suspendMsg);
if ($isSuspended && empty($suspendMsg)) {
    $suspendMsg = "Your account has been suspended by the administrator. Please contact support for assistance.";
}
unset($_SESSION['suspend_msg']);
?>

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
                    <i class="fas fa-lock" id="lock"></i>
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



    <?php if ($suspendMsg): ?>
        <div id="suspendOverlay" class="suspend-overlay <?php echo $isSuspended ? 'show' : ''; ?>">
            <div class="suspend-box">
                <div class="suspend-icon"><i class="fas fa-ban"></i></div>
                <h2>Account Suspended</h2>
                <p><?php echo htmlspecialchars($suspendMsg); ?></p>
                <button class="suspend-btn" onclick="closeSuspendPopup()">Understood</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('suspendOverlay').classList.add('show');
            if (window.history && window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('suspended');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }
        });

        function closeSuspendPopup() {
            document.getElementById('suspendOverlay').classList.remove('show');
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }

        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#passwordField');
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>