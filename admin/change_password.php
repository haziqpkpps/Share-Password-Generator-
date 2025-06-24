<?php
require '../db.php';
requireAdmin();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $newHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE admin SET password_hash = ? WHERE id = 1')
        ->execute([$newHash]);
    $message = 'Password updated successfully.';
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Change Admin Password</title>
</head>
<body class="bg-gray-900 text-gray-100 flex items-center justify-center min-h-screen p-4">
  <div class="bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-sm space-y-4">
    <header class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Change Password</h1>
      <a
        href="logout.php"
        class="bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded-lg transition text-sm"
      >Logout</a>
    </header>

    <?php if ($message): ?>
      <p class="text-green-400 text-center"><?= htmlspecialchars($message) ?></p>
    <?php endif ?>

    <form method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
      <div>
        <label for="new_password" class="block text-sm mb-1">New Password</label>
        <input
          id="new_password"
          name="new_password"
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
        Update Password
      </button>
    </form>
  </div>
</body>
</html>

