<?php
// Gives the home page the offer settings that the owner sets in admin.php.
require __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode(settings());