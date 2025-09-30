<?php
session_start();
include("partial/databasecon.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $senderId   = $_SESSION['user_id'];
    $receiverId = intval($_POST['friend_id']);

    $checkSql = "SELECT * FROM friend_requests 
                 WHERE (sender_id = ? AND receiver_id = ?) 
                 OR (sender_id = ? AND receiver_id = ?)";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("iiii", $senderId, $receiverId, $receiverId, $senderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $sql = "INSERT INTO friend_requests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $senderId, $receiverId);
        $stmt->execute();
    }
}

header("Location: main.php");
exit;
?>
