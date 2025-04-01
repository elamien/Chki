<?php
require_once "db_fixed.php";
session_start();

// Initialize variables
$username = '';
$errors = array();

// If user is already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Form processing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($username)) {
        $errors['username'] = "Username is required";
    }
    
    if (empty($password)) {
        $errors['password'] = "Password is required";
    }
    
    // If no validation errors, proceed with login
    if (empty($errors)) {
        try {
            $conn = db_connect();
            
            // Query for user
            $query = "SELECT * FROM users WHERE username = $1 OR email = $2";
            $result = db_query($conn, $query, array($username, $username));
            
            if (db_num_rows($result) > 0) {
                $user = db_fetch_assoc($result);
                
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['display_name'] = $user['username']; // Default to username
                    
                    // Set a cookie for persistent login (30 days)
                    setcookie('remember_user', $user['id'], time() + (30 * 24 * 60 * 60), '/');
                    
                    // Redirect to home page
                    header("Location: index.php");
                    exit();
                } else {
                    $errors['login'] = "Invalid username or password";
                }
            } else {
                $errors['login'] = "Invalid username or password";
            }
        } catch (Exception $e) {
            $errors['general'] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="authors" content="Ahmed Elamin">
    <title>Login - Chki</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <nav class="main-nav">
            <div class="nav-section left">
                <a href="index.php" class="logo">Chki</a>
            </div>
        </nav>

        <main class="content-area">
            <div class="auth-container">
                <div class="auth-form-container">
                    <h1>Log In</h1>
                    
                    <?php if (isset($errors['general']) || isset($errors['login'])): ?>
                        <div class="alert alert-danger">
                            <?php echo isset($errors['general']) ? $errors['general'] : $errors['login']; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="auth-form">
                        <div class="form-group">
                            <label for="username">Username or Email</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" 
                                class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" required>
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['username']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" 
                                class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" required>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-container">
                                <input type="checkbox" id="remember" name="remember" checked>
                                <label for="remember">Remember me</label>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn primary-btn">Log In</button>
                        </div>
                    </form>
                    
                    <div class="auth-links">
                        Don't have an account? <a href="register.php">Sign Up</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 