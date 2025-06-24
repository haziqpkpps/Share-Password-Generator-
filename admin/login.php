<?php
require '../db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $u = $_POST['username'] ?? '';
        $p = $_POST['password'] ?? '';
        $stmt = $pdo->prepare('SELECT * FROM admin WHERE username = ?');
        $stmt->execute([$u]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin && password_verify($p, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin']      = true;
            $_SESSION['login_time'] = time();      // track login time
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Invalid credentials';
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Admin Login</title>
</head>
<body class="bg-gray-900 text-gray-100 flex items-center justify-center min-h-screen p-4">
  <div class="bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-sm space-y-4">
    <h1 class="text-2xl font-bold text-center">Admin Login</h1>
    <?php if ($error): ?>
      <p class="text-red-500 text-center"><?= htmlspecialchars($error) ?></p>
    <?php endif ?>
    <form method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
      <div>
        <label for="username" class="block text-sm mb-1">Username</label>
        <input
          id="username"
          name="username"
          required
          class="w-full bg-gray-700 text-gray-200 placeholder-gray-400 rounded-lg px-4 py-2
                 focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Admin username"
        />
      </div>
      <div>
        <label for="password" class="block text-sm mb-1">Password</label>
        <input
          id="password"
          name="password"
          type="password"
          required
          class="w-full bg-gray-700 text-gray-200 placeholder-gray-400 rounded-lg px-4 py-2
                 focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="••••••••"
        />
      </div>
      <button
        type="submit"
        class="w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2 rounded-lg transition"
      >
        Log In
      </button>
    </form>
  </div>
</body>
</html>

