<?php
session_start();

// Hardcoded user credentials (username + hashed password)
// Password: secret123
$users = [
    'user1' => '$2y$10$Hdfkq1N9iHt5b2PZLyXQ5OYqZP0zJW3sZ7bH77YB5kW5Gl2fGz7rW'
];

// If already logged in, redirect to welcome page
if (isset($_SESSION['username'])) {
    header('Location: welcome.php');
    exit();
}

$error = '';
$forgotMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (isset($users[$username]) && password_verify($password, $users[$username])) {
            $_SESSION['username'] = $username;
            header('Location: welcome.php');
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    }

    if (isset($_POST['forgot'])) {
        $email = $_POST['email'] ?? '';
        // Here you would normally send a reset email
        // We'll just simulate success if the email contains '@'
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $forgotMessage = "If the email is registered, a password reset link has been sent.";
        } else {
            $forgotMessage = "Please enter a valid email address.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Unique PHP Login</title>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #71b7e6, #9b59b6);
    height: 100vh;
    margin: 0;
    display: flex; 
    justify-content: center; 
    align-items: center;
  }
  .login-container {
    background: white;
    padding: 2rem 3rem;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    width: 350px;
    text-align: center;
    position: relative;
  }
  .login-container h2 {
    margin-bottom: 1.5rem;
    color: #9b59b6;
  }
  input[type="text"], input[type="password"], input[type="email"] {
    width: 100%;
    padding: 0.75rem;
    margin: 0.5rem 0 1.25rem;
    border: 1.5px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
  }
  input[type="text"]:focus, input[type="password"]:focus, input[type="email"]:focus {
    border-color: #9b59b6;
    outline: none;
  }
  button {
    background: #9b59b6;
    color: white;
    border: none;
    padding: 0.75rem 0;
    width: 100%;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.3s ease;
  }
  button:hover {
    background: #8e44ad;
  }
  .error, .forgot-message {
    background: #f8d7da;
    color: #842029;
    padding: 0.75rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    font-size: 0.9rem;
  }
  .forgot-message {
    background: #d1e7dd;
    color: #0f5132;
  }
  .forgot-link {
    margin-top: 1rem;
    display: block;
    color: #9b59b6;
    cursor: pointer;
    font-size: 0.9rem;
    text-decoration: underline;
  }

  /* Modal styles */
  .modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
  }
  .modal-content {
    background: white;
    margin: 10% auto;
    padding: 2rem;
    border-radius: 12px;
    width: 320px;
    position: relative;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    text-align: center;
  }
  .close-btn {
    position: absolute;
    top: 10px; right: 15px;
    font-size: 1.5rem;
    font-weight: bold;
    color: #888;
    cursor: pointer;
  }
  .close-btn:hover {
    color: #9b59b6;
  }
</style>
</head>
<body>
<div class="login-container">
  <h2>Login</h2>

  <?php if ($error): ?>
    <div class="error"><?=htmlspecialchars($error)?></div>
  <?php endif; ?>

  <?php if ($forgotMessage): ?>
    <div class="<?= strpos($forgotMessage, 'sent') !== false ? 'forgot-message' : 'error' ?>">
      <?=htmlspecialchars($forgotMessage)?>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <input type="text" name="username" placeholder="Username" required autofocus />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit" name="login">Log In</button>
  </form>

  <span class="forgot-link" id="forgotPwdBtn">Forgot Password?</span>
</div>

<!-- Modal -->
<div id="forgotModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" id="closeModal">&times;</span>
    <h3>Reset Password</h3>
    <form method="POST" action="">
      <input type="email" name="email" placeholder="Enter your email" required />
      <button type="submit" name="forgot">Send Reset Link</button>
    </form>
  </div>
</div>

<script>
  const modal = document.getElementById('forgotModal');
  const btn = document.getElementById('forgotPwdBtn');
  const closeBtn = document.getElementById('closeModal');

  btn.onclick = function() {
    modal.style.display = "block";
  }

  closeBtn.onclick = function() {
    modal.style.display = "none";
  }

  window.onclick = function(event) {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  }
</script>

</body>
</html>
