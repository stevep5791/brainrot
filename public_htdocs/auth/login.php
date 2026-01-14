<?php
require_once __DIR__ . '/../../classes/Auth.php';

$auth = Auth::getInstance();
$auth->ensureAdminExists();

// Already logged in as admin? Go to admin panel
if ($auth->isAdmin()) {
    header('Location: /admin/');
    exit;
}

// Already logged in but not admin? Log them out and show error
if ($auth->isLoggedIn()) {
    $auth->logout();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = $auth->login($username, $password);
    if ($result['success']) {
        // Only admins can access - redirect to admin panel
        if ($auth->isAdmin()) {
            header('Location: /admin/');
        } else {
            $auth->logout();
            $error = 'Admin access required.';
        }
        exit;
    } else {
        $error = $result['error'];
    }
}

if (isset($_GET['error']) && $_GET['error'] === 'admin_required') {
    $error = 'Admin access required. Please log in with an admin account.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Brain Rot Detector</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
    <style>
        body.login-page {
            background: var(--navy-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            margin: 30px;
        }

        .login-card {
            background: var(--white);
            border: 1px solid var(--border);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .login-header {
            background: var(--navy-primary);
            color: var(--white);
            padding: 24px 30px;
            text-align: center;
            border-bottom: 4px solid var(--burgundy);
        }

        .login-header h1 {
            margin: 0;
            font-size: 1.3rem;
            color: var(--white);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-weight: 600;
            text-transform: none;
            letter-spacing: 0;
        }

        .login-header p {
            margin: 8px 0 0;
            font-size: 0.85rem;
            opacity: 0.8;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .login-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border);
            font-size: 1rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            transition: border-color 0.15s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--navy-primary);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--burgundy);
            color: white;
            border: none;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            transition: background 0.15s ease;
        }

        .btn-login:hover {
            background: var(--burgundy-light);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .back-link a {
            color: var(--navy-primary);
            text-decoration: none;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 0.9rem;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .error-msg {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px 16px;
            margin-bottom: 20px;
            border: 1px solid #fca5a5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <h1>Admin Login</h1>
                <p>Brain Rot ML Training Data Collection</p>
            </div>

            <div class="login-body">
                <?php if ($error): ?>
                    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required
                               autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn-login">Log In</button>
                </form>

                <div class="back-link">
                    <a href="/">← Back to Public Site</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
