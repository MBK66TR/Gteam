<?php
require_once 'config.php';

// Oturumu sonlandır
session_destroy();

// Ana sayfaya yönlendir
header('Location: login.php');
exit();
?> 