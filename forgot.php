<?php
session_start();

$forgotMessage = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Here you would check if the email exists in your user database,
        // then send a password reset email with a token/link.
        // For this demo, we just simulate success.

        $forgotMessage = "If the email is registered, a password reset link has been sent.";
    } else {
        $error = "Please enter a valid email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Forgot Password</title>
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
  .forgot-container {
    background: white;
    padding: 2rem 3rem;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    width: 350px;
    text-align: center;
  }
  .forgot-container h2 {
    margin-bottom: 1.5rem;
    color: #9b59b6;
  }
  input[type="email"] {
    width: 100%;
    padding: 0.75rem;
    margin: 0.5rem 0 1.25rem;
    border: 1.5px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
  }
  input[type="email"]:focus {
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
    padding: 0.75rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    font-size: 0.9rem;
  }
  .error {
    background: #f8d7da;
    color: #842029;
  }
  .forgot-message {
    background: #d1e7dd;
    color: #0f5132;
  }
  .back-link {
    display: block;
    margin-top: 1rem;
    color: #9b59b6;
    text-decoration: underline;
    font-size: 0.9rem;
  }
</style>
</head>
<body>
  <div class="forgot-container">
    <h2>Forgot Password?</h2>

    <?php if ($error): ?>
      <div class="error"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <?php if ($forgotMessage): ?>
      <div class="forgot-message"><?=htmlspecialchars($forgotMessage)?></div>
    <?php else: ?>
      <form method="POST" action="">
        <input type="email" name="email" placeholder="Enter your email" required autofocus />
        <button type="submit">Send Reset Link</button>
      </form>
    <?php endif; ?>

    <a href="index.php" class="back-link">&larr; Back to Login</a>
  </div>
</body>
</html>
