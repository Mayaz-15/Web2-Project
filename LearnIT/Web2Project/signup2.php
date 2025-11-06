<?php 
include "db.php"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="style.css">
  <title>Sign-Up</title>

  <style>
    h2 { color: #1f3b4d; margin-bottom: 1.25em; text-align: center; font-size: 2.5em; }

    .box {
      max-width: 46.875em; margin: 1.25em auto; background: #fff;
      border-radius: 0.5em; box-shadow: 0 0.125em 0.375em rgba(0,0,0,0.05); padding: 1.25em;
    }

    fieldset { border: 0.0625em solid #ddd; padding: 1.25em; border-radius: 0.5em; background: #fff; }
    legend { font-weight: bold; color: #1f3b4d; }

    label { display: block; margin-top: 0.625em; font-weight: 500; font-size: 0.9375em; color: #1f3b4d; }

    input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
      width: 100%; padding: 0.625em; margin-top: 0.3125em; 
      border: 0.0625em solid #ccc; border-radius: 0.375em; font-size: 0.9375em;
    }

    .radio-group { display: flex; align-items: center; gap: 1.25em; margin: 0.9375em 0; }
    .hidden { display:none; }

    .form-box fieldset {
      width: 100%; max-width: 46.875em; min-height: 28em;
      display: flex; flex-direction: column; justify-content: flex-start; margin: auto;
    }

    .submit-btn {
      display: block; width: 100%; text-align: center;
      background: #0A3D62; color: #fff; padding: 0.8em; 
      margin-top: 1.25em; border: none; border-radius: 0.375em;
      font-size: 1em; cursor: pointer;
    }
    .submit-btn:hover { background:#082a45; }
  </style>

  <script>
    function toggleForms(){
      const learnerForm = document.getElementById("learnerBox");
      const educatorForm = document.getElementById("educatorBox");

      if(document.getElementById("learnerRadio").checked){
        learnerForm.style.display = "block";
        educatorForm.style.display = "none";
      } else {
        learnerForm.style.display = "none";
        educatorForm.style.display = "block";
      }
    }
  </script>
</head>

<body>

  <!-- ✅ الهيدر الأصلي كما في كودك -->
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

  <main class="card-container">
    <h2>Create Your Account</h2>

    <div class="box">
      <fieldset>
        <legend>User Type:</legend>
        <div class="radio-group">
          <label><input type="radio" name="userType" id="learnerRadio" value="learner" checked onclick="toggleForms()"> Learner</label>
          <label><input type="radio" name="userType" id="educatorRadio" value="educator" onclick="toggleForms()"> Educator</label>
        </div>
      </fieldset>
    </div>

    <!-- ✅ فورم واحد فقط -->
    <form action="signup_process.php" method="POST" enctype="multipart/form-data" class="form-box">

      <!-- ✅ Learner form -->
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

      <!-- ✅ Educator form -->
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
            while($row = $stmt->fetch()){
              echo '<label><input type="checkbox" name="topics[]" value="'.$row["id"].'"> '.$row["topicName"].'</label>';
            }
          ?>
        </fieldset>
      </div>

      <!-- ✅ زر واحد فقط -->
      <button type="submit" class="submit-btn">Sign Up</button>

    </form>

  </main>

  <!-- ✅ الفوتر الأصلي كما في كودك -->
  <footer>
    &copy; 2025 learnIT. All rights reserved.
  </footer>

</body>
</html>
