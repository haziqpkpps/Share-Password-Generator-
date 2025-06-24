<?php
// /admin/index.php

// If you ever want to check login state here, you could:
// require '../db.php';
// if (isAdmin() && /* session still valid */) { header('Location: dashboard.php'); exit; }

header('Location: login.php');
exit;

