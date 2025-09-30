<?php
session_start();
include("partial/databasecon.php");

if (isset($_POST['signup'])) {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        $_SESSION['user_id']   = $stmt->insert_id;
        $_SESSION['user_name'] = $username;
        header("Location: main.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Signup</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f0f2f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-family: Arial, sans-serif;
    }
    .card {
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      background: #fff;
      width: 100%;
      max-width: 400px;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #1877f2;
      font-weight: bold;
    }
    .form-control {
      border-radius: 8px;
      padding: 12px;
    }
    .btn-primary {
      background: #1877f2;
      border: none;
      border-radius: 8px;
      padding: 12px;
      font-weight: bold;
    }
    .btn-primary:hover {
      background: #145dbf;
    }
    p {
      text-align: center;
      margin-top: 15px;
    }
    a {
      color: #1877f2;
      text-decoration: none;
      font-weight: bold;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>Create Account</h2>
    <form method="post">
      <div class="mb-3">
        <input type="text" name="username" class="form-control" placeholder="Username" required>
      </div>
      <div class="mb-3">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
      </div>
      <div class="mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
      </div>
      <button type="submit" name="signup" class="btn btn-primary w-100">Signup</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
