<?php


$session_id = session_id();

require_once __DIR__ . '/../db/control.php';

$db = eco_get_database();
$query = $db->prepare('DELETE FROM cart_items WHERE session_id = :session_id');
$query->bindValue(':session_id', $session_id, SQLITE3_TEXT);
$query->execute();

session_unset();
session_destroy();

header('Location: ?page=home');
exit();
