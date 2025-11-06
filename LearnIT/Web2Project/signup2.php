<?php 
include "db.php"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - LearnIT</title>

  <style>

    /* ✅ خلفية السماوية الفاتحة */
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: linear-gradient(to right, #e1f3f7, #d3eef3);
    }

    /* ✅ الهيدر */
    header {
      background-color: #163a4d;
      color: white;
      padding: 20px 60px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header .logo {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    header img {
      height: 45px;
    }

    header nav a {
      color: white;
      text-decoration: none;
      margin-left: 25px;
      font-size: 18px;
    }

    header nav a:hover {
      text-decoration: underline;
    }

    /* ✅ صندوق المحتوى الأبيض */
    .card-container {
      max-width: 900px;
      margin: 50px auto;
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    h2 {
      color: #1f3b4d;
      font-size: 2.4em;
      text-align: center;
      margin-bottom: 30px;
    }

    .box {
      background: white;
      padding: 20px;
      border-radius: 10px;
      border: 1px solid #ddd;
      margin-bottom: 20px;
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

    /* ✅ زر واحد */
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

    .submit-btn:hover {
      background: #072a45;
    }

    .hidden {
      display: none;
    }

    /* ✅ الفوتر */
    footer {
      text-align: center;
      padding: 20px;
      background: white;
      margin-top: 40px;
      font-size: 14px;
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

  <!-- ✅ الهيدر النهائي -->
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

  <!-- ✅ المحتوى -->
  <main class="card-container">

    <h2>Create Your Account</h2>

    <!-- ✅ اختيار نوع المستخدم -->
    <div class="box">
      <fieldset>
        <legend>User Type:</legend>
        <label><input type="radio" name="userType" id="learnerRadio" value="learner" checked onclick="toggleForms()"> Learner</label>
        <label><input type="radio" name="userType" id="educatorRadio" value="educator" onclick="toggleForms()"> Educator</label>
      </fieldset>
    </div>

    <!-- ✅ النموذج كامل -->
    <form action="signup_process.php" method="POST" enctype="multipart/form-data">

      <!-- ✅ Learner -->
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

      <!-- ✅ Educator -->
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

      <!-- ✅ الزر الوحيد -->
      <button type="submit" class="submit-btn">Sign Up</button>

    </form>

  </main>

 <footer style="
    background-color: #163a4d;
    color: white;
    text-align: center;
    padding: 20px;
    margin-top: 40px;
    font-size: 14px;">
    © 2025 learnIT. All rights reserved.
</footer>


</body>
</html>
