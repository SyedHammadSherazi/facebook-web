<?php
session_start();
include("partial/databasecon.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$currentUser = $_SESSION['user_id'];
$search = "";

// Search
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $sql = "SELECT id, username FROM users WHERE id != ? AND username LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchParam = "%" . $search . "%";
    $stmt->bind_param("is", $currentUser, $searchParam);
} else {
    $sql = "SELECT id, username FROM users WHERE id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $currentUser);
}
$stmt->execute();
$result = $stmt->get_result();

// Friend Requests
$reqSql = "SELECT fr.id AS req_id, u.id AS sender_id, u.username 
           FROM friend_requests fr
           JOIN users u ON fr.sender_id = u.id
           WHERE fr.receiver_id = ? AND fr.status = 'pending'";
$reqStmt = $conn->prepare($reqSql);
$reqStmt->bind_param("i", $currentUser);
$reqStmt->execute();
$friendRequests = $reqStmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Main Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body { background: #f0f2f5; font-family: "Segoe UI", Arial, sans-serif; }
    .navbar { background: #1877f2; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
    .navbar-brand { font-weight: bold; }
    .navbar-brand, .nav-link, .navbar-text { color: #fff !important; }
    .sidebar { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); min-height: 85vh; }
    .sidebar h5 { font-weight: bold; margin-bottom: 20px; }
    .sidebar a { color: #555; text-decoration: none; display: block; padding: 10px 12px; border-radius: 8px; margin-bottom: 8px; transition: 0.3s; }
    .sidebar a:hover { background: #f0f2f5; color: #1877f2; font-weight: 600; }
    .card { border-radius: 12px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 25px; }
    h5 { font-weight: 600; }
    .friend-item, .request-item, .message-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #eee; }
    .friend-item:last-child, .request-item:last-child, .message-item:last-child { border-bottom: none; }
    .friend-item span, .request-item span { font-weight: 500; color: #333; }
    .btn-sm { border-radius: 20px; padding: 5px 15px; }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg px-3">
    <a class="navbar-brand" href="#">MyFacebook</a>
    <div class="ms-auto d-flex align-items-center">
      <span class="navbar-text me-3"> Welcome, <?php echo $_SESSION['user_name']; ?>!</span>
      <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
    </div>
  </nav>

  <div class="container-fluid mt-4">
    <div class="row g-4">
      
      <!-- Sidebar -->
      <div class="col-lg-3">
        <div class="sidebar">
          <h5>Navigation</h5>
          <a href="#"> Home</a>
          <a href="#"> Find Friends</a>
          <a href="#"> Friend Requests</a>
          <a href="#"> Messages</a>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-lg-9">

        <!-- Find Friends -->
        <div class="card p-3">
          <h5> Find Friends</h5>
          <form method="get" class="d-flex mb-3">
            <input type="text" name="search" class="form-control me-2" placeholder="Search friends..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
          </form>

          <?php if ($result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
              <div class="friend-item">
                <span><?php echo htmlspecialchars($row['username']); ?></span>
                <?php
                $checkSql = "SELECT id, sender_id, receiver_id, status FROM friend_requests 
                             WHERE (sender_id = ? AND receiver_id = ?) 
                                OR (sender_id = ? AND receiver_id = ?)";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param("iiii", $currentUser, $row['id'], $row['id'], $currentUser);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();

                if ($checkResult->num_rows > 0) {
                    $req = $checkResult->fetch_assoc();

                    if ($req['status'] == 'pending' && $req['sender_id'] == $currentUser) {
                        echo '<button class="btn btn-secondary btn-sm" disabled>Pending</button>';
                    } elseif ($req['status'] == 'pending' && $req['sender_id'] == $row['id']) {
                        echo '<button class="btn btn-warning btn-sm" disabled>Request Received</button>';
                    } elseif ($req['status'] == 'accepted') {
                        echo '<button class="btn btn-success btn-sm" disabled>Friends</button>';
                    } elseif ($req['status'] == 'rejected') {
                        echo '<button class="btn btn-danger btn-sm" disabled>Rejected</button>';
                    }
                } else {
                ?>
                  <form method="post" action="sendrequest.php" class="m-0">
                    <input type="hidden" name="friend_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" class="btn btn-primary btn-sm">Add Friend</button>
                  </form>
                <?php } ?>
              </div>
            <?php } ?>
          <?php } else { ?>
            <p class="text-muted">No users found.</p>
          <?php } ?>
        </div>

        <!-- Friend Requests -->
        <div class="card p-3">
          <h5> Friend Requests</h5>
          <?php if ($friendRequests->num_rows > 0) { ?>
            <?php while ($reqRow = $friendRequests->fetch_assoc()) { ?>
              <div class="request-item">
                <span><?php echo htmlspecialchars($reqRow['username']); ?></span>
                <div>
                  <form method="post" action="request_action.php" class="d-inline">
                    <input type="hidden" name="req_id" value="<?php echo $reqRow['req_id']; ?>">
                    <input type="hidden" name="action" value="accept">
                    <button type="submit" class="btn btn-success btn-sm">Accept</button>
                  </form>
                  <form method="post" action="request_action.php" class="d-inline">
                    <input type="hidden" name="req_id" value="<?php echo $reqRow['req_id']; ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                  </form>
                </div>
              </div>
            <?php } ?>
          <?php } else { ?>
            <p class="text-muted">No requests yet.</p>
          <?php } ?>
        </div>

        <!-- Messages -->
        <div class="card p-3">
          <h5>💬 Messages</h5>
          <?php
          $friendsSql = "SELECT u.id, u.username 
                         FROM friend_requests fr
                         JOIN users u ON (u.id = fr.sender_id OR u.id = fr.receiver_id)
                         WHERE (fr.sender_id = ? OR fr.receiver_id = ?)
                         AND fr.status = 'accepted'
                         AND u.id != ?";
          $friendsStmt = $conn->prepare($friendsSql);
          $friendsStmt->bind_param("iii", $currentUser, $currentUser, $currentUser);
          $friendsStmt->execute();
          $friends = $friendsStmt->get_result();

          if ($friends->num_rows > 0) {
              while ($f = $friends->fetch_assoc()) {
                  echo '<div class="message-item">';
                  echo '<span>' . htmlspecialchars($f['username']) . '</span>';
                  echo '<a href="chat.php?friend_id=' . $f['id'] . '" class="btn btn-primary btn-sm">Chat</a>';
                  echo '</div>';
              }
          } else {
              echo '<p class="text-muted">No friends yet.</p>';
          }
          ?>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
