<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
   <link rel="stylesheet" href="style.css">
  <title>Sign-Up</title>
  <style>
   
    h2 {
      color: #1f3b4d;
      margin-bottom: 1.25em;
      text-align: center;
      font-size: 2.5em;
    }

    .box {
      max-width: 46.875em;
      margin: 1.25em auto;
      background: #fff;
      border-radius: 0.5em;
      box-shadow: 0 0.125em 0.375em rgba(0,0,0,0.05);
      padding: 1.25em;
    }

    fieldset {
      border: 0.0625em solid #ddd;
      padding: 1.25em;
      border-radius: 0.5em;
      background: #fff;
    }
    legend {
      font-weight: bold;
      color: #1f3b4d;
    }

    label {
      display: block;
      margin-top: 0.625em;
      font-weight: 500;
      font-size: 0.9375em;
      color: #1f3b4d;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="file"] {
      width: 100%;
      padding: 0.625em;
      margin-top: 0.3125em;
      border: 0.0625em solid #ccc;
      border-radius: 0.375em;
      font-size: 0.9375em;
    }

    a.btn {
      display: inline-block;
      text-align: center;
      font-size: 0.9375em;
    }
	

    .btn-learners {
      background: #0A3D62;
      margin-right: 0.625em;
    }
    .btn-learners:hover { background: #082a45; }

    .btn-educators {
      background: #38B2AC;
    }
    .btn-educators:hover { background: #2f8e87; }

 
    .radio-group {
      display: flex;
      align-items: center;
      gap: 1.25em;
      margin: 0.9375em 0;
    }

    .hidden { display: none; }

    .form-box fieldset {
      width: 100%;
      max-width: 46.875em;
      min-height: 32.5em;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      margin: auto;
    }


    .btn-group {
      display: flex;
      justify-content: center;
      gap: 0.9375em;
      margin-top: 1.25em;
    }

    @media (max-width: 600px) {
      main { padding: 1.25em; }
      .card-container { padding: 1em; }
      header, footer { padding: 0.625em 1.25em; }
    }
  </style>
</head>
<body>
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
          <label>
            <input type="radio" name="userType" value="learner" checked> Learner
          </label>
          <label>
            <input type="radio" name="userType" value="educator"> Educator
          </label>
        </div>
      </fieldset>
    </div>

    <form id="learnerForm" class="form-box">
      <fieldset>
        <legend>Learner Form</legend>
        <label>First Name:</label>
        <input type="text" placeholder=" Sara" required />
        <label>Last Name:</label>
        <input type="text" placeholder=" Al-Qahtani" required />
        <label>Profile Image:</label>
        <input type="file" id="learnerImage" accept="image/*" />
        <label>Email:</label>
        <input type="email" placeholder=" learner@example.com" required />
        <label>Password:</label>
        <input type="password" placeholder=" StrongPass123" required />
        <div class="btn-group">
          <a href="learner.php" class="btn btn-learners">Sign Up Learner’s Homepage</a>
          <a href="educator.php" class="btn btn-educators">Sign Up Educator’s Homepage</a>
        </div>
      </fieldset>
    </form>

    <form id="educatorForm" class="hidden form-box">
      <fieldset>
        <legend>Educator Form</legend>
        <label>First Name:</label>
        <input type="text" placeholder=" Ghala" required />
        <label>Last Name:</label>
        <input type="text" placeholder=" Al-Otaibi" required />
        <label>Profile Image:</label>
        <input type="file" id="educatorImage" accept="image/*" />
        <label>Email:</label>
        <input type="email" placeholder=" educator@example.com" required />
        <label>Password:</label>
        <input type="password" placeholder=" EduPass2025" required />
        <label>Specialized Topics:</label>
        <div>
          <input type="checkbox" name="topics" value="AI"> AI<br>
          <input type="checkbox" name="topics" value="IoT"> IoT<br>
          <input type="checkbox" name="topics" value="Cybersecurity"> Cybersecurity<br>
        </div>
        <div class="btn-group">
          <a href="learner.php" class="btn btn-learners">Sign Up Learner’s Homepage</a>
          <a href="educator.php" class="btn btn-educators">Sign Up Educator’s Homepage</a>
        </div> 
      </fieldset>
    </form>
  </main>

  <footer>
    &copy; 2025 learnIT. All rights reserved.
  </footer>

  <script>
    const learnerForm = document.getElementById("learnerForm");
    const educatorForm = document.getElementById("educatorForm");
    const radios = document.querySelectorAll('input[name="userType"]');

    radios.forEach(radio => {
      radio.addEventListener("change", () => {
        if (radio.value === "learner") {
          learnerForm.classList.remove("hidden");
          educatorForm.classList.add("hidden");
        } else {
          educatorForm.classList.remove("hidden");
          learnerForm.classList.add("hidden");
        }
      });
    });
  </script>
</body>
</html>
