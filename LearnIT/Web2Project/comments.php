<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}


if ($_SESSION['user_type'] !== 'educator') {
    header("Location: index.php");
    exit();
}



include "connect.php";   

   
$quizId = 0;
if (isset($_GET['quizID']))   { $quizId = (int)$_GET['quizID']; }
elseif (isset($_GET['quiz_id'])) { $quizId = (int)$_GET['quiz_id']; }

$comments = [];
if ($quizId > 0) {
    $sql  = "SELECT id, comments, date
             FROM quizfeedback
             WHERE quizID = ?
             ORDER BY date DESC, id DESC";  
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $comments[] = $row;
    }
    $stmt->close();
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comments</title>
  <link rel="stylesheet" href="style.css">
  
  <style>
   
.comments-section  {
      width:70rem;
      margin: 2em auto;
      padding: 2.5rem;
      background: #fff;
      border-radius: 0.625rem;
     box-shadow: 0 0.125em 0.3125em rgba(0,0,0,0.1);

      max-height: 37.5rem;
      overflow-y: auto;
    }
	

    .comments-section h2 {
      text-align: center;
      margin-bottom: 1.5625rem;
      font-size: 2.5rem;
     
    }

    .comment {
      padding: 1.25rem;
	  
      margin-bottom: 0.9375rem;
      border: 1px solid #ddd;
      border-radius: 0.375rem;
      background: #f9f9f9;
      transition: 0.3s;
    }

    
    .comment:hover {
      background: #f1f9f6;  
      box-shadow: 0 1.125rem 0.5rem rgba(0,0,0,0.1);
    }

    .comment .author,
    .comment .date,
    .comment .text {
     
    }
.comment .author {
  font-weight: bold;
  color: #333;
}

.comment .date {
  font-size: 0.9em;
  color: #777;  
}

.comment .text {
  margin-top: 0.5rem;
  color: #444;
}






 .comments-section::-webkit-scrollbar {
  width: 8px;
}

 .comments-section::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 0.625rem;
}

 .comments-section::-webkit-scrollbar-thumb {
  background: #003366; 
  border-radius: 0.625rem;
}

 .comments-section::-webkit-scrollbar-thumb:hover {
  background: #4fc3f7; 
}


.comments-section  {
  scrollbar-width: thin;
  scrollbar-color: #003366 #f1f1f1;
}

.commentback {
text-align: right;
margin-bottom:15px;
}

	 	 
 
	 
	 
	 

  </style>

  
</head>
<body>
    <div class="comments-wrapper" > 
  <header>
    <div class="logo">
      <img src="images/logo.png" alt="LearnIT Logo">
      <h1>LearnIT</h1>
    </div>
    <nav>
      <a href="educator.php">Home</a>
      
    </nav>
  </header>
  
   

<section class="comments-section">
  <h2>Quiz Comments</h2>

  <div class="commentback">
    <a class="takeHome" href="educator.php">Back to Educator Home</a>
  </div>

  <?php if ($quizId === 0): ?>
    <div class="comment">
      <div class="text">No quiz selected. Open this page from the “Comments” link of a quiz.</div>
    </div>
  <?php elseif (empty($comments)): ?>
    <div class="comment">
      <div class="text">No comments yet.</div>
    </div>
  <?php else: ?>
    <?php foreach ($comments as $c): ?>
      <div class="comment">
        <div class="author">💬 Anonymous</div>
        <div class="date">
          <?=
            htmlspecialchars(
              date('M j, Y – g:i A', strtotime($c['date']))
            );
          ?>
        </div>
        <div class="text"><?= nl2br(htmlspecialchars($c['comments'])) ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>



 

  
  <footer>
    <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
  </footer>
    </div>     
</body>
</html>


<style> 
 
.comments-wrapper {
  min-height: 100vh;         
  display: flex;
  flex-direction: column;
}

.comments-wrapper header {
  flex: 0 0 auto;
}

.comments-wrapper .comments-section {
  flex: 1 0 auto;            
}

.comments-wrapper footer {
  margin-top: auto;          
}

</style> 
  
