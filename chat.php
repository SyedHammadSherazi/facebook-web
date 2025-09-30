<?php
session_start();
include("partial/databasecon.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$currentUser = $_SESSION['user_id'];
$friendId = intval($_GET['friend_id']);

// Check if they are friends
$checkSql = "SELECT id FROM friend_requests 
             WHERE ((sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?))
             AND status='accepted'";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("iiii", $currentUser, $friendId, $friendId, $currentUser);
$checkStmt->execute();
$isFriend = $checkStmt->get_result();

if ($isFriend->num_rows == 0) {
    die("You are not friends with this user.");
}

// Handle new message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    if (!empty($msg)) {
        $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $currentUser, $friendId, $msg);
        $stmt->execute();
    }
}

// Fetch friend's name
$stmt = $conn->prepare("SELECT username FROM users WHERE id=?");
$stmt->bind_param("i", $friendId);
$stmt->execute();
$res = $stmt->get_result();
$friendName = $res->fetch_assoc()['username'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Chat with <?php echo htmlspecialchars($friendName); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .chat-box { height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 8px; background: #f9f9f9; }
    .msg-sent { text-align: right; }
    .msg-received { text-align: left; }
    .msg-sent .bubble { background: #1877f2; color: white; display: inline-block; padding: 8px 12px; border-radius: 12px; margin: 3px 0; }
    .msg-received .bubble { background: #e4e6eb; display: inline-block; padding: 8px 12px; border-radius: 12px; margin: 3px 0; }
  </style>
</head>
<body>
<div class="container mt-4">
  <h4>Chat with <?php echo htmlspecialchars($friendName); ?></h4>
  <div class="chat-box mb-3" id="chatBox">
    <?php
    // Mark received messages as seen
    $conn->query("UPDATE messages SET seen=1 WHERE receiver_id=$currentUser AND sender_id=$friendId");

    // Load messages
    $sql = "SELECT * FROM messages 
            WHERE (sender_id=? AND receiver_id=?) 
               OR (sender_id=? AND receiver_id=?) 
            ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $currentUser, $friendId, $friendId, $currentUser);
    $stmt->execute();
    $msgs = $stmt->get_result();

    while ($m = $msgs->fetch_assoc()) {
        if ($m['sender_id'] == $currentUser) {
            echo "<div class='msg-sent'><span class='bubble'>" . htmlspecialchars($m['message']) . "</span></div>";
        } else {
            echo "<div class='msg-received'><span class='bubble'>" . htmlspecialchars($m['message']) . "</span></div>";
        }
    }
    ?>
  </div>
  <form method="post" class="d-flex">
    <input type="text" name="message" class="form-control me-2" placeholder="Type a message..." required>
    <button class="btn btn-primary">Send</button>
  </form>
  <a href="main.php" class="btn btn-link mt-3">⬅ Back</a>
</div>
<script>
  let chatBox = document.getElementById("chatBox");
  chatBox.scrollTop = chatBox.scrollHeight;
</script>
</body>
</html>
