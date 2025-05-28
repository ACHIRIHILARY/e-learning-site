<?php
session_start();
include_once 'config.php';

$sql = "SELECT chat_messages.*, users.name, users.role, users.id AS uid 
        FROM chat_messages 
        JOIN users ON chat_messages.user_id = users.id 
        ORDER BY chat_messages.created_at ASC";
$result = $conn->query($sql);

if (!$result) {
    // Show error for debugging
    echo '<div class="alert alert-danger">SQL Error: ' . $conn->error . '</div>';
    exit;
}

while ($row = $result->fetch_assoc()) {
    $isMe = $row['uid'] == $_SESSION['user_id'];
    $isTeacher = $row['role'] === 'teacher';

    $alignClass = $isMe ? 'chat-right' : 'chat-left';
    $msgClass = $isTeacher ? 'chat-teacher' : 'chat-student';

    echo '<div class="chat-message '.$alignClass.'">';
    echo '<div class="chat-name" style="color:'.($isTeacher ? '#e65100' : '#1565c0').';">' . htmlspecialchars($row['name']);
    if ($isTeacher) echo ' <span class="badge bg-warning text-dark ms-1" style="font-size:0.85em;">Teacher</span>';
    echo '</div>';
    echo '<div class="'.$msgClass.'">'.nl2br(htmlspecialchars($row['message'])).'</div>';
    echo '<div class="chat-time">'.date('H:i', strtotime($row['created_at'])).'</div>';
    echo '</div>';
}
?>