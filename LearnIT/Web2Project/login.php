<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
  
  <style>
    
    .login-section {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 3.75rem 0;

      min-height: calc(100vh - 7.5rem);  
    }
.login-section .login-container  {
  background: #fff;
  padding: 6.25rem 3.75rem;
  border-radius: 0.625rem;
  width: 70rem; 
  height:auto;
  text-align: center;
  margin: 2.5rem auto;
   box-shadow: 0 0.125em 0.3125em rgba(0,0,0,0.1);
}


.login-section .login-container h2 {
  margin-bottom: 1.5625rem;
  font-size: 2.5rem;
}

 .login-section .login-container input {
  width: 80%;
  padding: 0.875rem;
  margin: 0.8rem 0;
  border: 0.0625rem solid #ccc;
  border-radius: 0.375rem;
  font-size: 0.9375rem;
  box-sizing: border-box;
}


.btn{
margin: 40px 1%;

}






.login-section .btn-learner {
  background-color: #0A3D62;
  color: white;
}

.login-section .btn-educator {
  background-color: #38B2AC;
  color: white;
}




@media (max-width: 37.5rem) {
  .login-section .login-container {
    width: 90%;   
    padding: 1.875rem;
  }


}  




.error-msg { margin-top: 0.5rem; color: #b3261e; font-size: 0.95rem; }



.error-msg {
    color: #b30000;
    background-color: #ffe5e5;
    border: 1px solid #ffb3b3;
    padding: 10px 15px;
    border-radius: 8px;
    text-align: center;
    margin-top: 15px;
    width: fit-content;
    margin-left: auto;
    margin-right: auto;
    font-weight: bold;
    animation: fadeIn 0.6s ease-in-out;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}


  </style>

  
</head>
<body class="login-page">
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
  

  

<section class="login-section">
    <div class="login-container ">
      <h2>Login to LearnIT</h2>
      
      
      <form method="post" action="login_process.php" autocomplete="off">
  <input type="email" name="email" placeholder="Email" required><br>
  <input type="password" name="password" placeholder="Password" required><br>

  
  <button type="submit" class="btn">Log in</button>

  
  <?php if (!empty($_GET['error'])): ?>
  <p class="error-msg">
    <?= htmlspecialchars($_GET['error']) ?>
  </p>
<?php endif; ?>
    
    
</form>
      
      
    </div>
  </section>


  



  <footer>
    <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
  </footer>
</body>
</html>
