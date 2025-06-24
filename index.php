<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Generate Password Share</title>
</head>
<body class="bg-gray-900 text-gray-100 flex items-center justify-center min-h-screen p-4">
  <div class="bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-md space-y-6">
    <h1 class="text-2xl font-bold text-center">Generate Password Share</h1>
    <form id="psForm" method="POST" action="generate.php" class="space-y-5">
      <!-- CSRF token must stay inside the form -->
      <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

      <!-- Username -->
      <div>
        <label for="username" class="block text-sm font-medium mb-1">Username</label>
        <input id="username" name="username" type="text" required
               class="w-full bg-gray-700 text-gray-200 placeholder-gray-400 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="Enter username">
      </div>

      <!-- Password Type -->
      <div>
        <label for="type" class="block text-sm font-medium mb-1">Password Type</label>
        <select id="type" name="type"
                class="w-full bg-gray-700 text-gray-200 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="random">Random Password</option>
          <option value="memorable">Memorable Password</option>
          <option value="pin">PIN Code</option>
        </select>
      </div>

      <!-- Length Slider -->
      <div>
        <label for="length" class="block text-sm font-medium mb-1">
          Length: <span id="lenVal" class="font-semibold">12</span>
        </label>
        <input type="range" id="length" name="length" min="4" max="64" value="12" class="w-full"
               oninput="document.getElementById('lenVal').textContent = this.value">
      </div>

      <!-- Numbers & Symbols -->
      <div class="flex justify-between">
        <label for="include_numbers" class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" id="include_numbers" name="include_numbers" class="sr-only peer">
          <div class="w-11 h-6 bg-gray-600 rounded-full peer-checked:bg-green-600 transition-colors"></div>
          <div class="absolute top-0.5 left-0.5 bg-white w-5 h-5 rounded-full peer-checked:translate-x-5 transition-transform"></div>
          <span class="ml-3 text-sm">Numbers</span>
        </label>

        <label for="include_symbols" class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" id="include_symbols" name="include_symbols" class="sr-only peer">
          <div class="w-11 h-6 bg-gray-600 rounded-full peer-checked:bg-green-600 transition-colors"></div>
          <div class="absolute top-0.5 left-0.5 bg-white w-5 h-5 rounded-full peer-checked:translate-x-5 transition-transform"></div>
          <span class="ml-3 text-sm">Symbols</span>
        </label>
      </div>

      <!-- Validity -->
      <div>
        <label for="validity" class="block text-sm font-medium mb-1">Validity</label>
        <select id="validity" name="validity"
                class="w-full bg-gray-700 text-gray-200 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="1">1 hour</option>
          <option value="24">24 hours</option>
          <option value="168">7 days</option>
        </select>
      </div>

      <!-- View Once Only -->
      <div class="flex items-center">
        <label for="view_once" class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" id="view_once" name="view_once" class="sr-only peer">
          <div class="w-11 h-6 bg-gray-600 rounded-full peer-checked:bg-green-600 transition-colors"></div>
          <div class="absolute top-0.5 left-0.5 bg-white w-5 h-5 rounded-full peer-checked:translate-x-5 transition-transform"></div>
          <span class="ml-3 text-sm">View Once Only</span>
        </label>
      </div>

      <!-- Generate -->
      <button type="submit"
              class="w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2 rounded-lg transition">
        Generate
      </button>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Elements
      const typeEl    = document.getElementById('type');
      const lengthEl  = document.getElementById('length');
      const lenVal    = document.getElementById('lenVal');
      const numbersEl = document.getElementById('include_numbers');
      const symbolsEl = document.getElementById('include_symbols');
      const validityEl= document.getElementById('validity');
      const viewOnce  = document.getElementById('view_once');

      // Restore from localStorage
      if (localStorage.ps_type)    typeEl.value      = localStorage.ps_type;
      if (localStorage.ps_length)  { lengthEl.value  = localStorage.ps_length; lenVal.textContent = localStorage.ps_length; }
      numbersEl.checked = localStorage.ps_numbers === undefined ? true : (localStorage.ps_numbers === 'true');
      symbolsEl.checked = localStorage.ps_symbols  === 'true';
      if (localStorage.ps_validity) validityEl.value = localStorage.ps_validity;
      viewOnce.checked  = localStorage.ps_view_once === 'true';

      // Save on change
      typeEl.addEventListener('change',    () => localStorage.ps_type       = typeEl.value);
      lengthEl.addEventListener('input',   () => { localStorage.ps_length = lengthEl.value; lenVal.textContent = lengthEl.value });
      numbersEl.addEventListener('change', () => localStorage.ps_numbers    = numbersEl.checked);
      symbolsEl.addEventListener('change', () => localStorage.ps_symbols    = symbolsEl.checked);
      validityEl.addEventListener('change',() => localStorage.ps_validity   = validityEl.value);
      viewOnce .addEventListener('change', () => localStorage.ps_view_once  = viewOnce.checked);
    });
  </script>
</body>
</html>

