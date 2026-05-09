<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Join Us | Paw Hubs</title>

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
            padding: 20px;
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--secondary) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 550px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            font-size: 28px;
            color: var(--text-dark);
        }

        .error-list {
            background: rgba(255, 77, 77, 0.1);
            border-left: 4px solid var(--error);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .error-list ul {
            margin: 0;
            padding-left: 20px;
            color: var(--error);
            font-size: 14px;
        }

        .success-box {
            background: rgba(107, 181, 168, 0.14);
            border-left: 4px solid var(--primary);
            color: var(--text-dark);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 600;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .full-width {
            grid-column: span 2;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
        }

        .input-wrapper input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 2px solid #d8ebe5;
            border-radius: 12px;
            outline: none;
            background: #f5faf8;
        }

        .upload-box {
            border: 2px dashed var(--primary);
            border-radius: 16px;
            background: #f5faf8;
            padding: 18px;
            display: grid;
            grid-template-columns: 58px 1fr;
            align-items: center;
            gap: 14px;
            cursor: pointer;
        }

        .upload-box i {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--bg-color);
            color: var(--primary);
            font-size: 24px;
        }

        .upload-box strong {
            display: block;
            color: var(--text-dark);
            font-size: 15px;
            margin-bottom: 4px;
        }

        .upload-box span {
            color: #718096;
            font-size: 13px;
        }

        .upload-box input {
            display: none;
        }

        .rules-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 18px;
            color: #4a5568;
            font-size: 14px;
            line-height: 1.45;
        }

        .rules-row input {
            width: 18px;
            height: 18px;
            margin-top: 1px;
            accent-color: var(--primary);
            flex: 0 0 auto;
        }

        .rules-link {
            border: 0;
            background: transparent;
            color: var(--primary-dark);
            font: inherit;
            font-weight: 700;
            padding: 0;
            cursor: pointer;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(26, 48, 48, 0.42);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 50;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .rules-modal {
            width: min(620px, 100%);
            max-height: 85vh;
            overflow-y: auto;
            background: #fff;
            border-radius: 22px;
            padding: 28px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.22);
        }

        .rules-modal h3 {
            margin: 0 0 14px;
            color: var(--text-dark);
            font-size: 24px;
        }

        .rules-modal ul {
            margin: 0;
            padding-left: 22px;
            color: #4a5568;
            line-height: 1.6;
        }

        .rules-modal li {
            margin-bottom: 9px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 22px;
        }

        .modal-btn {
            border: 0;
            border-radius: 10px;
            padding: 11px 18px;
            cursor: pointer;
            font-weight: 700;
        }

        .modal-btn.primary {
            background: var(--primary);
            color: #fff;
        }

        button[type="submit"] {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 14px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(107, 181, 168, 0.4);
        }

        .footer-link {
            text-align: center;
            margin-top: 25px;
        }

        .footer-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .form-note {
            grid-column: span 2;
            color: #718096;
            font-size: 12px;
            line-height: 1.4;
            margin-top: -4px;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="header">
            <h2>Create Account</h2>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="success-box">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <form action="index.php?url=auth/register" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="input-wrapper full-width">
                    <i class="far fa-user"></i>
                    <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
                </div>

                <label class="upload-box full-width" for="profileImage">
                    <i class="far fa-image"></i>
                    <span>
                        <strong id="uploadLabel">Upload profile picture</strong>
                        <span>JPG, PNG, or WEBP. Max size 2MB.</span>
                    </span>
                    <input type="file" id="profileImage" name="profile_image" accept="image/jpeg,image/png,image/webp">
                </label>

                <div class="input-wrapper">
                    <i class="far fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                </div>

                <div class="input-wrapper">
                    <i class="fas fa-phone"></i>
                    <input type="text" name="phone_number" placeholder="Phone Number" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" required>
                </div>

                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="pass" placeholder="Password" required title="Password must include letters and numbers">
                </div>

                <div class="input-wrapper">
                    <i class="fas fa-check-circle"></i>
                    <input type="password" name="confirm_pass" placeholder="Confirm Password" required>
                </div>

                <div class="input-wrapper full-width">
                    <i class="fas fa-house"></i>
                    <input type="text" name="address" placeholder="Full Address" value="<?= htmlspecialchars($old['address'] ?? '') ?>" required>
                </div>

                <p class="form-note">Phone must be Egyptian, like 01012345678. Password must include letters and numbers.</p>
            </div>

            <div class="rules-row">
                <input type="checkbox" name="accept_rules" required>
                <span>
                    I agree to Paw Hubs
                    <button type="button" class="rules-link" id="openRules">rules for pets, vets, and legal age</button>.
                </span>
            </div>

            <button type="submit" style="margin-top: 20px;">Create Account</button>
        </form>

        <div class="footer-link">
            Already have an account? <a href="index.php?url=auth/login">Sign In</a>
        </div>
    </div>

    <div class="modal-backdrop" id="rulesModal" aria-hidden="true">
        <div class="rules-modal" role="dialog" aria-modal="true" aria-labelledby="rulesTitle">
            <h3 id="rulesTitle">Paw Hubs Rules</h3>
            <ul>
                <li>Pets listed on Paw Hubs must be dogs or cats that are treated kindly and kept in safe living conditions.</li>
                <li>Owners must provide accurate health information, vaccine status, age, and any known medical conditions.</li>
                <li>Never give human medicine, unsafe food, or home treatment to a dog or cat without asking a licensed veterinarian.</li>
                <li>Vet appointments must be respected. If you cannot attend, cancel or reschedule as early as possible.</li>
                <li>Doctors and service providers must be treated respectfully. Aggressive behavior or fake information can lead to account suspension.</li>
                <li>Users must be at least 18 years old to create an account, book paid services, or make marketplace purchases.</li>
                <li>Any adoption, sale, or service request must follow local animal welfare laws and must not involve abuse, neglect, or illegal breeding.</li>
                <li>Profile images and pet images must be appropriate and must not include violent, harmful, or misleading content.</li>
            </ul>
            <div class="modal-actions">
                <button type="button" class="modal-btn" id="closeRules">Close</button>
                <button type="button" class="modal-btn primary" id="acceptRules">Accept Rules</button>
            </div>
        </div>
    </div>

    <script>
        const profileImage = document.getElementById('profileImage');
        const uploadLabel = document.getElementById('uploadLabel');
        const rulesModal = document.getElementById('rulesModal');
        const openRules = document.getElementById('openRules');
        const closeRules = document.getElementById('closeRules');
        const acceptRules = document.getElementById('acceptRules');
        const acceptCheckbox = document.querySelector('input[name="accept_rules"]');

        profileImage.addEventListener('change', function() {
            uploadLabel.textContent = this.files.length ? this.files[0].name : 'Upload profile picture';
        });

        openRules.addEventListener('click', function() {
            rulesModal.classList.add('show');
            rulesModal.setAttribute('aria-hidden', 'false');
        });

        closeRules.addEventListener('click', function() {
            rulesModal.classList.remove('show');
            rulesModal.setAttribute('aria-hidden', 'true');
        });

        acceptRules.addEventListener('click', function() {
            acceptCheckbox.checked = true;
            rulesModal.classList.remove('show');
            rulesModal.setAttribute('aria-hidden', 'true');
        });

        rulesModal.addEventListener('click', function(event) {
            if (event.target === rulesModal) {
                rulesModal.classList.remove('show');
                rulesModal.setAttribute('aria-hidden', 'true');
            }
        });
    </script>
</body>

</html>