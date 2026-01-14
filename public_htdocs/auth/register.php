<?php
require_once __DIR__ . '/../../classes/Auth.php';

$auth = Auth::getInstance();

// Already logged in?
if ($auth->isLoggedIn()) {
    header('Location: /');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if ($password !== $confirm) {
        $error = 'Passwords do not match';
    } else {
        $result = $auth->register($username, $email, $password);
        if ($result['success']) {
            header('Location: /');
            exit;
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Brain Rot Detector</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 60px auto;
            padding: 40px;
            background: var(--white);
            border: 1px solid var(--border);
        }
        .auth-container h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border);
            font-size: 1rem;
            font-family: inherit;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--navy-primary);
        }
        .btn-register {
            width: 100%;
            padding: 14px;
            background: var(--navy-primary);
            color: white;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-register:hover {
            background: var(--navy-medium);
        }
        .auth-links {
            text-align: center;
            margin-top: 20px;
        }
        .auth-links a {
            color: var(--navy-primary);
        }
        .error-msg {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #fca5a5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <h1>Create Account</h1>
            
            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required minlength="3"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn-register">Create Account</button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="/auth/login.php">Log In</a></p>
                <p><a href="/">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
