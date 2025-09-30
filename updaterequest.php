<?php
session_start();
include("partial/databasecon.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $requestId = intval($_POST['request_id']);
    $action    = $_POST['action'];

    if ($action === "accept") {
        $status = "accepted";
    } elseif ($action === "reject") {
        $status = "rejected";
    }

    $sql = "UPDATE friend_requests SET status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $requestId);
    $stmt->execute();
}

header("Location: main.php");
exit;
?>
