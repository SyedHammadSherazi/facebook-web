<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Facebook Clone - Index</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: #f0f2f5;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: Arial, sans-serif;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      transition: transform 0.3s;
      text-align: center;
      padding: 50px 20px;
      cursor: pointer;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .login {
      background: linear-gradient(135deg, #1877f2, #145dbf);
      color: white;
    }
    .signup {
      background: linear-gradient(135deg, #42b72a, #36a420);
      color: white;
    }
    h2 {
      font-size: 2rem;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center g-4">
      <!-- Login Card -->
      <div class="col-md-5">
        <div class="card login">
          <h2  ><a href="login.php" style="text-decoration: none; color:aliceblue" >Login</a></h2>
        </div>
      </div>

      <!-- Signup Card -->
      <div class="col-md-5">
        <div class="card signup">
          <h2  ><a href="signup.php" style="text-decoration: none; color:aliceblue" >Signup</a></h2>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
