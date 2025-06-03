<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'credential.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "All fields are required";
    } elseif ($username === $credentials['userName'] && $password === $credentials['password']) {
        session_unset();
        session_destroy();

        session_start();
        session_regenerate_id(true);

        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = date("Y-m-d H:i:s");

        header('Location: main.php?page=profile');
        exit;
    } else {
        $error = "Incorrect username or password";
    }
}

?>

<main class="login-page">
  <h1>🔐 Login</h1>

  <?php if (isset($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="post" action="main.php?page=login" class="login-form">
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" placeholder="Enter your username" required>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter your password" required>
    </div>

    <button type="submit" class="btn btn--primary">Sign In</button>
  </form>
</main>
