<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "teacher") {
    header("Location: ../auth/login.php");
    exit;
}

include_once '../config.php';

if (!isset($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$course_id = intval($_GET['id']);

// Ensure the course belongs to the logged-in teacher
$stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $course_id, $_SESSION["user_id"]);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    header("Location: ../dashboard.php?error=notfound");
    exit;
}
$stmt->close();

// Delete the course
$stmt = $conn->prepare("DELETE FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $course_id, $_SESSION["user_id"]);
$stmt->execute();
$stmt->close();

header("Location: ../dashboard.php?deleted=1");
exit;
?>