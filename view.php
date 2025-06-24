<?php
require 'db.php';

$token = $_GET['token'] ?? '';
$stmt  = $pdo->prepare('SELECT * FROM shares WHERE token = ?');
$stmt->execute([$token]);
$share = $stmt->fetch(PDO::FETCH_ASSOC);

// If link is invalid, expired by time, or already consumed (for view_once), show expired page
if (
    !$share
    || ($share['expires_at'] && new DateTime() > new DateTime($share['expires_at']))
    || ($share['view_once'] && !empty($share['consumed_at']))
) {
    ?>
    <!DOCTYPE html>
    <html lang="en" class="dark">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width,initial-scale=1.0">
      <script src="https://cdn.tailwindcss.com"></script>
      <title>Link Expired</title>
    </head>
    <body class="bg-gray-900 text-gray-100 flex items-center justify-center min-h-screen p-4">
      <div class="bg-gray-800 rounded-2xl shadow-xl p-8 max-w-sm text-center">
        <h1 class="text-2xl font-bold mb-4">Oops!</h1>
        <p class="mb-6 text-gray-400">This link is invalid or has expired.</p>
        <a href="index.php"
           class="inline-block bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg transition">
          Generate a New Link
        </a>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// Mark as consumed on first view
if ($share['view_once'] && empty($share['consumed_at'])) {
	$pdo->prepare('UPDATE shares SET consumed_at = NOW(), consumed_ip = ? WHERE id = ?')
	    ->execute([$_SERVER['REMOTE_ADDR'], $share['id']]);
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Password for <?= htmlspecialchars($share['username']) ?></title>
</head>
<body class="bg-gray-900 text-gray-100 flex items-center justify-center min-h-screen p-4">
  <div class="bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-sm space-y-4">
    <h2 class="text-xl font-semibold text-center">
      Password for <span class="text-blue-400"><?= htmlspecialchars($share['username']) ?></span>
    </h2>
    <div class="relative mb-4">
      <input
        id="pwdField"
        type="text"
        readonly
        class="w-full bg-gray-700 text-gray-200 rounded-lg px-4 py-2 pr-24 focus:outline-none"
        value="<?= htmlspecialchars($share['password']) ?>"
      />
      <button
        id="copyPwd"
        class="absolute top-1/2 right-2 transform -translate-y-1/2 bg-green-600 hover:bg-green-500 px-4 py-1 rounded-lg text-sm font-medium transition"
      >Copy</button>
    </div>
    <p class="text-sm text-gray-400 text-center">
      Expires at <?= htmlspecialchars($share['expires_at']) ?>
    </p>
  </div>

  <script>
    const btn = document.getElementById('copyPwd');
    btn.addEventListener('click', async () => {
      const orig = btn.textContent;
      await navigator.clipboard.writeText(
        document.getElementById('pwdField').value
      );
      btn.textContent = 'Copied!';
      setTimeout(() => btn.textContent = orig, 2000);
    });
  </script>
</body>
</html>

