<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}
include_once 'config.php';
include_once 'includes/header.php';

// Get user info
$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_name"];
$user_role = $_SESSION["user_role"];
?>

<div class="container py-5">
    <h3 class="text-center text-primary mb-4 fw-bold" style="font-family: 'Segoe UI', Arial, sans-serif;">Live Group Chat</h3>
    <div id="chat-box" class="bg-light border rounded p-3 mb-3" style="height:400px; overflow-y:scroll; font-size:1.15rem;">
        <!-- Messages will be loaded here -->
    </div>
    <form id="chat-form" class="d-flex">
        <input type="text" id="message" class="form-control me-2 fw-semibold" placeholder="Type your message..." autocomplete="off" required>
        <button type="submit" class="btn btn-success fw-bold">Send</button>
    </form>
</div>

<style>
    .chat-message {
        margin-bottom: 1.1rem;
        max-width: 75%;
        word-break: break-word;
        font-family: 'Segoe UI', Arial, sans-serif;
    }
    .chat-right {
        margin-left: auto;
        text-align: right;
    }
    .chat-left {
        margin-right: auto;
        text-align: left;
    }
    .chat-student {
        background: #e3f2fd;
        color: #1565c0;
        border-radius: 1.2rem 1.2rem 1.2rem 0.4rem;
        font-weight: 600;
        padding: 0.7rem 1.2rem;
        box-shadow: 0 2px 8px rgba(21,101,192,0.07);
    }
    .chat-teacher {
        background: #fff3e0;
        color: #e65100;
        border-radius: 1.2rem 1.2rem 0.4rem 1.2rem;
        font-weight: 700;
        padding: 0.7rem 1.2rem;
        box-shadow: 0 2px 8px rgba(230,81,0,0.07);
    }
    .chat-name {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.2rem;
        letter-spacing: 0.5px;
    }
    .chat-time {
        font-size: 0.85rem;
        color: #888;
        margin-top: 0.2rem;
        font-weight: 500;
    }
</style>

<script>
function loadMessages() {
    fetch('fetch_messages.php')
        .then(res => res.text())
        .then(data => {
            document.getElementById('chat-box').innerHTML = data;
            document.getElementById('chat-box').scrollTop = document.getElementById('chat-box').scrollHeight;
        });
}
document.getElementById('chat-form').onsubmit = function(e) {
    e.preventDefault();
    fetch('send_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(document.getElementById('message').value)
    }).then(() => {
        document.getElementById('message').value = '';
        loadMessages();
    });
};
setInterval(loadMessages, 2000);
window.onload = loadMessages;
</script>

<?php include_once 'includes/footer.php'; ?>