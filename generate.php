<?php
require 'db.php';

// 1) Validate request & CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(400);
    exit('Invalid request');
}

// 2) Gather inputs
$username       = trim((string)($_POST['username'] ?? ''));
$type           = $_POST['type'] ?? 'random';
$length         = max(4, min(64, (int)($_POST['length'] ?? 12)));
$includeNumbers = isset($_POST['include_numbers']);
$includeSymbols = isset($_POST['include_symbols']);
$validity       = max(1, (int)($_POST['validity'] ?? 1));
$view_once      = isset($_POST['view_once']) ? 1 : 0;

// 3) Password generator function
function generatePassword($type, $length, $incNum, $incSym) {
    $lower   = 'abcdefghijklmnopqrstuvwxyz';
    $upper   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $digits  = '0123456789';
    $symbols = '!@#$%^&*()-_=+[]{}<>?';

    if ($type === 'pin') {
        $chars = $digits;
    } elseif ($type === 'memorable') {
        $vowels     = 'aeiou';
        $consonants = 'bcdfghjklmnpqrstvwxyz';
        $pw = '';
        for ($i = 0; $i < $length; $i++) {
            $set = ($i % 2 === 0) ? $consonants : $vowels;
            $pw .= $set[random_int(0, strlen($set) - 1)];
        }
        return $pw;
    } else {
        $chars = $lower . $upper;
        if ($incNum) $chars .= $digits;
        if ($incSym) $chars .= $symbols;
    }

    $pw = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $pw .= $chars[random_int(0, $max)];
    }
    return $pw;
}

// 4) Generate creds & token
$password  = generatePassword($type, $length, $includeNumbers, $includeSymbols);
$token     = bin2hex(random_bytes(16));
$expiresAt = date('Y-m-d H:i:s', time() + $validity * 3600);

// 5) Insert into DB
$stmt = $pdo->prepare(
    'INSERT INTO shares (token, username, password, expires_at, view_once)
     VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$token, $username, $password, $expiresAt, $view_once]);

// 6) Build share link
$link = sprintf(
    '%s://%s/view.php?token=%s',
    $_SERVER['REQUEST_SCHEME'],
    $_SERVER['HTTP_HOST'],
    $token
);
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Share Generated</title>
</head>
<body class="bg-gray-900 text-gray-100 flex flex-col items-center justify-center min-h-screen p-4 space-y-6">

  <!-- Share Link Card -->
  <div class="bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-md space-y-4">
    <h1 class="text-2xl font-bold text-center">Your Share Link</h1>
    <div class="relative">
      <input
        id="shareLink"
        type="text"
        readonly
        class="w-full bg-gray-700 text-gray-200 rounded-lg px-4 py-2 pr-24 focus:outline-none"
        value="<?= htmlspecialchars($link) ?>"
      />
      <button
        id="copyLinkBtn"
        class="absolute top-1/2 right-2 transform -translate-y-1/2 bg-blue-600 hover:bg-blue-500 px-4 py-1 rounded-lg text-sm font-medium transition"
      >Copy</button>
    </div>
    <p class="mt-2 text-sm text-gray-400 text-center">
      Expires at <?= htmlspecialchars($expiresAt) ?>
    </p>
  </div>

  <!-- Generated Credentials Card -->
  <div class="bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-md space-y-4">
    <h2 class="text-xl font-semibold text-center">Generated Credentials</h2>

    <!-- Username -->
    <div class="flex items-center">
      <span class="w-24 font-medium">Username:</span>
      <input
        id="credUser"
        type="text"
        readonly
        class="flex-1 bg-gray-700 text-gray-200 rounded-lg px-4 py-2 mr-2 focus:outline-none"
        value="<?= htmlspecialchars($username) ?>"
      />
      <button
        id="copyUserBtn"
        class="bg-green-600 hover:bg-green-500 px-3 py-1 rounded-lg text-sm font-medium transition"
      >Copy</button>
    </div>

    <!-- Password -->
    <div class="flex items-center">
      <span class="w-24 font-medium">Password:</span>
      <input
        id="credPass"
        type="text"
        readonly
        class="flex-1 bg-gray-700 text-gray-200 rounded-lg px-4 py-2 mr-2 focus:outline-none"
        value="<?= htmlspecialchars($password) ?>"
      />
      <button
        id="copyPassBtn"
        class="bg-green-600 hover:bg-green-500 px-3 py-1 rounded-lg text-sm font-medium transition"
      >Copy</button>
    </div>
  </div>

  <script>
    // Share-link copy feedback
    document.getElementById('copyLinkBtn').addEventListener('click', async () => {
      const btn = document.getElementById('copyLinkBtn');
      await navigator.clipboard.writeText(document.getElementById('shareLink').value);
      btn.textContent = 'Copied!';
      setTimeout(() => btn.textContent = 'Copy', 2000);
    });

    // Credentials copy feedback
    ['copyUserBtn', 'copyPassBtn'].forEach(id => {
      const btn = document.getElementById(id);
      btn.addEventListener('click', async () => {
        const target = id === 'copyUserBtn' ? 'credUser' : 'credPass';
        await navigator.clipboard.writeText(document.getElementById(target).value);
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = 'Copy', 2000);
      });
    });
  </script>
</body>
</html>

