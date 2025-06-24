<?php
require '../db.php';
requireAdmin();

// Search filter
$search = trim((string)($_GET['q'] ?? ''));
$params = [];
$sql    = '
  SELECT
    id,
    username,
    password,
    created_at,
    expires_at,
    view_once,
    consumed_at,
    consumed_ip
  FROM shares
';
if ($search !== '') {
    $sql .= ' WHERE username LIKE ?';
    $params[] = "%{$search}%";
}
$sql .= ' ORDER BY created_at DESC LIMIT 40';
$stmt  = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helpers
function humanTiming($dt) {
    $t = strtotime($dt);
    $d = time() - $t;
    if ($d < 3600) {
        $m = max(1, (int)floor($d/60));
        return "{$m} minute" . ($m > 1 ? 's' : '') . " ago";
    } elseif ($d < 86400) {
        $h = (int)floor($d/3600);
        $m = (int)floor(($d % 3600)/60);
        $s = "{$h} hour" . ($h > 1 ? 's' : '');
        if ($m) {
            $s .= " and {$m} minute" . ($m > 1 ? 's' : '');
        }
        return $s . " ago";
    }
    return date('gA jS M y', $t);
}
function humanRemaining($dt) {
    $t = strtotime($dt);
    $d = $t - time();
    if ($d <= 0) return 'Expired';
    if ($d < 3600) {
        $m = max(1, (int)ceil($d/60));
        return "Remaining {$m} minute" . ($m > 1 ? 's' : '');
    } elseif ($d < 86400) {
        $h = (int)floor($d/3600);
        $m = (int)floor(($d % 3600)/60);
        $s = "Remaining {$h} hour" . ($h > 1 ? 's' : '');
        if ($m) {
            $s .= " and {$m} minute" . ($m > 1 ? 's' : '');
        }
        return $s;
    }
    $days = (int)floor($d/86400);
    $hrs  = (int)floor(($d % 86400)/3600);
    $s    = "Remaining {$days} day" . ($days > 1 ? 's' : '');
    if ($hrs) {
        $s .= " and {$hrs} hour" . ($hrs > 1 ? 's' : '');
    }
    return $s;
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Admin Dashboard</title>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen p-4">
  <div class="max-w-6xl mx-auto space-y-6">
    <header class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-2 sm:space-y-0">
      <h1 class="text-2xl font-bold">Password Share History</h1>
      <div class="flex items-center space-x-2">
        <form method="GET" class="flex">
          <input
            type="text" name="q" value="<?= htmlspecialchars($search) ?>"
            placeholder="Search username"
            class="bg-gray-700 text-gray-200 placeholder-gray-400 rounded-l-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <button type="submit"
                  class="bg-blue-600 hover:bg-blue-500 text-white px-4 rounded-r-lg transition">
            Search
          </button>
        </form>
        <a href="change_password.php" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg transition">Change Password</a>
        <a href="logout.php" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-lg transition">Logout</a>
      </div>
    </header>

    <div class="overflow-x-auto">
      <table class="w-full table-auto text-sm text-left">
        <thead>
          <tr class="bg-gray-700 text-gray-200">
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Username</th>
            <th class="px-4 py-2">Created</th>
            <th class="px-4 py-2">Expires In</th>
            <th class="px-4 py-2">View Once</th>
            <th class="px-4 py-2">Consumed</th>
            <th class="px-4 py-2">IP</th>
            <th class="px-4 py-2">Password</th>
            <th class="px-4 py-2">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r):
            $pw        = $r['password'];
            $maskCount = max(0, strlen($pw) - 4);
            $displayDots = min($maskCount, 16);
            $mask      = str_repeat('•', $displayDots);
            $last4     = substr($pw, -4);
            $masked    = $mask . htmlspecialchars($last4);

            if ($r['view_once']) {
              $cons = $r['consumed_at']
                      ? humanTiming($r['consumed_at'])
                      : '<span class="text-yellow-400">Pending</span>';
            } else {
              $cons = '-';
            }
            $ip = $r['consumed_ip'] ?? '-';
          ?>
          <tr class="border-t border-gray-700 even:bg-gray-800">
            <td class="px-4 py-2"><?= htmlspecialchars($r['id']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($r['username']) ?></td>
            <td class="px-4 py-2"><?= humanTiming($r['created_at']) ?></td>
            <td class="px-4 py-2"><?= humanRemaining($r['expires_at']) ?></td>
            <td class="px-4 py-2"><?= $r['view_once'] ? 'Yes' : 'No' ?></td>
            <td class="px-4 py-2"><?= $cons ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($ip) ?></td>
            <td class="px-4 py-2 font-mono"><?= $masked ?></td>
            <td class="px-4 py-2">
              <button
                data-full="<?= htmlspecialchars($pw) ?>"
                class="copy-pass-btn bg-green-600 hover:bg-green-500 text-white px-3 py-1 rounded text-xs transition"
              >Copy</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    document.querySelectorAll('.copy-pass-btn').forEach(btn => {
      btn.addEventListener('click', async () => {
        const full = btn.getAttribute('data-full'), orig = btn.textContent;
        await navigator.clipboard.writeText(full);
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = orig, 2000);
      });
    });
  </script>
</body>
</html>

