<?php
require_once __DIR__ . '/config.php';
sessionStart();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
