<?php 
require_once 'connect.php'; // الاتصال بقاعدة البيانات (MySQLi)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - LearnIT</title>

  <!-- ✅ CSS داخلي كامل -->
  <style>

    /* خلفية الصفحة */
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(to right, #eef5f9, #d9f0f3);
      color: #2e3a45;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      line-height: 1.6;
    }

    /* ✅ Header */
    header {
      background: #1f3b4d;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.938em 2.5em;
      border-bottom: 0.188em solid #d9f0f3;
      color: #fff;
    }

    header .logo { display: flex; align-items: center; }
    header .logo img { height: 2.812em; margin-right: 0.625em; }
    header .logo h1 { font-size: 1.375em; letter-spacing: 0.062em; }

    header nav a {
      margin: 0 0.938em;
      text-decoration: none;
      color: #eaf6fb;
      font-weight: 500;
      transition: color 0.3s;
    }
    header nav a:hover { color: #aed6f1; }

    /* ✅ White Card */
    .card-container {
      width: 70rem;
      max-width: 90%;
      margin: 3em auto;
      padding: 2em;
      background-color: #ffffff;
      border-radius: 0.5em;
      box-shadow: 0 0.125em 0.3125em rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      color: #1f3b4d;
      font-size: 2.2rem;
      margin-bottom: 1em;
    }

    fieldset {
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
    }

    legend {
      font-weight: bold;
      color: #1f3b4d;
      padding: 0 8px;
    }

    label {
      display: block;
      margin-top: 12px;
      color: #1f3b4d;
      font-size: 15px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="file"] {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-top: 4px;
      font-size: 15px;
    }

    /* ✅ Submit Button */
    .submit-btn {
      width: 100%;
      padding: 15px;
      background: #0A3D62;
      color: white;
      font-size: 18px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 25px;
    }
    .submit-btn:hover { background: #072a45; }

    .hidden { display: none; }

    /* ✅ Footer */
    footer {
      background: #1f3b4d;
      text-align: center;
      padding: 0.938em;
      border-top: 0.188em solid #d9f0f3;
      font-size: 0.875em;
      color: #d6eaf8;
      margin-top: auto;
      width: 100%;
    }

  </style>

  <script>
    function toggleForms() {
      let learnerBox = document.getElementById("learnerBox");
      let educatorBox = document.getElementById("educatorBox");

      if (document.getElementById("learnerRadio").checked) {
        learnerBox.style.display = "block";
        educatorBox.style.display = "none";
      } else {
        learnerBox.style.display = "none";
        educatorBox.style.display = "block";
      }
    }
  </script>

</head>
<body>

  <!-- ✅ Header -->
  <header>
    <div class="logo">
      <img src="images/logo.png" alt="LearnIT Logo">
      <h1>LearnIT</h1>
    </div>
    <nav>
      <a href="index.php">Home</a>
      <a href="login.php">Login</a>
      <a href="signup.php">Sign Up</a>
    </nav>
  </header>

  <!-- ✅ Content -->
  <main class="card-container">

    <h2>Create Your Account</h2>

    <fieldset>
      <legend>User Type:</legend>
      <label><input type="radio" name="userType" id="learnerRadio" value="learner" checked onclick="toggleForms()"> Learner</label>
      <label><input type="radio" name="userType" id="educatorRadio" value="educator" onclick="toggleForms()"> Educator</label>
    </fieldset>

    <form action="signup_process.php" method="POST" enctype="multipart/form-data">

      <!-- Learner -->
      <div id="learnerBox">
        <fieldset>
          <legend>Learner Form</legend>

          <label>First Name:</label>
          <input type="text" name="firstName" required>

          <label>Last Name:</label>
          <input type="text" name="lastName" required>

          <label>Profile Image (optional):</label>
          <input type="file" name="photo" accept="image/*">

          <label>Email:</label>
          <input type="email" name="email" required>

          <label>Password:</label>
          <input type="password" name="password" required>
        </fieldset>
      </div>

      <!-- Educator -->
      <div id="educatorBox" class="hidden">
        <fieldset>
          <legend>Educator Form</legend>

          <label>First Name:</label>
          <input type="text" name="firstNameEdu">

          <label>Last Name:</label>
          <input type="text" name="lastNameEdu">

          <label>Profile Image (optional):</label>
          <input type="file" name="photo" accept="image/*">

          <label>Email:</label>
          <input type="email" name="emailEdu">

          <label>Password:</label>
          <input type="password" name="passwordEdu">

          <label>Specialized Topics:</label>
          <?php
            $stmt = $pdo->query("SELECT * FROM topic");
            while ($row = $stmt->fetch()) {
              echo '<label><input type="checkbox" name="topics[]" value="'.$row["id"].'"> '.$row["topicName"].'</label>';
            }
          ?>
        </fieldset>
      </div>

      <button type="submit" class="submit-btn">Sign Up</button>

    </form>

  </main>

  <!-- ✅ Footer -->
  <footer>
    <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
  </footer>

</body>
</html>
