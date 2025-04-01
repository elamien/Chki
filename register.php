<?php
require_once "db_fixed.php";
session_start();

// Initialize variables
$username = $email = $displayName = '';
$errors = array();

// If user is already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Form processing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $displayName = trim(filter_input(INPUT_POST, 'display_name', FILTER_SANITIZE_STRING));

    // Validate username (letters, numbers, underscores only)
    if (empty($username)) {
        $errors['username'] = "Username is required";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors['username'] = "Username must be 3-20 characters and can only contain letters, numbers, and underscores";
    }

    // Validate email
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address";
    }

    // Validate password
    if (empty($password)) {
        $errors['password'] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters";
    }

    // Confirm passwords match
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    // If no validation errors, proceed with registration
    if (empty($errors)) {
        try {
            $conn = db_connect();
            
            // Check if username or email already exists
            $query = "SELECT * FROM users WHERE username = $1 OR email = $2";
            $result = db_query($conn, $query, array($username, $email));
            
            if (db_num_rows($result) > 0) {
                $existingUser = db_fetch_assoc($result);
                
                if ($existingUser['username'] === $username) {
                    $errors['username'] = "This username is already taken";
                }
                if ($existingUser['email'] === $email) {
                    $errors['email'] = "This email is already registered";
                }
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $query = "INSERT INTO users (username, email, password) VALUES ($1, $2, $3) RETURNING id";
                $result = db_query($conn, $query, array($username, $email, $hashedPassword));
                
                if ($result) {
                    // Get the new user's ID
                    $row = db_fetch_assoc($result);
                    $userId = $row['id'];
                    
                    // Set session variables
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;
                    $_SESSION['display_name'] = $username; // Default to username
                    
                    // Redirect to home page
                    header("Location: index.php");
                    exit();
                } else {
                    $errors['general'] = "Registration failed. Please try again.";
                }
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
    <title>Register - Chki</title>
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
                    <h1>Create Your Account</h1>
                    
                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="auth-form">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" 
                                class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" required>
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['username']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                                class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="display_name">Display Name (Optional)</label>
                            <input type="text" id="display_name" name="display_name" value="<?php echo htmlspecialchars($displayName); ?>" class="form-control">
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
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" required>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn primary-btn">Register</button>
                        </div>
                    </form>
                    
                    <div class="auth-links">
                        Already have an account? <a href="login.php">Log In</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 