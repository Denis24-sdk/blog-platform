<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  unset($_SESSION['show_swipe_notification']);
  http_response_code(200);
  echo 'OK';
  exit;
}

http_response_code(405);
echo 'Method Not Allowed';
