<?php
$query = $_GET['q'] ?? '';
$token = $_GET['token'] ?? '';
if ($token) {
    header('Location: menu.php?token=' . urlencode($token) . '&q=' . urlencode($query));
} else {
    header('Location: index.php');
}
exit;