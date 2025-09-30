<?php
session_start();
include("partial/databasecon.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['req_id'], $_POST['action'])) {
    $req_id = intval($_POST['req_id']);
    $action = $_POST['action'];

    if ($action == "accept") {
        $status = "accepted";
    } elseif ($action == "reject") {
        $status = "rejected";
    } else {
        $status = "pending";
    }

    $sql = "UPDATE friend_requests SET status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $req_id);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Request $status!";
    } else {
        $_SESSION['msg'] = "Error updating request.";
    }
}
header("Location: main.php");
exit;
