<?php
session_start();
include_once 'config.php';
if (isset($_POST['message']) && trim($_POST['message']) !== '' && isset($_SESSION['user_id'])) {
    $msg = trim($_POST['message']);
    $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $_SESSION['user_id'], $msg);
    $stmt->execute();
    $stmt->close();
}
?>