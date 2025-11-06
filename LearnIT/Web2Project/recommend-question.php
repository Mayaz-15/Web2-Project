<?php

session_start();



    include 'connect.php'; // should define $conn

    if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }
   

// --- Session check---
if (!isset($_SESSION['id']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'learner') {
    header("Location: login.php");
    exit();
}
$learnerID = (int) $_SESSION['id'];

// --- Handle form submission ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $topicID = intval($_POST['topic']);
    $educatorID = intval($_POST['prof']);
    $question = trim($_POST['Q']);
    $answerA = trim($_POST['a']);
    $answerB = trim($_POST['b']);
    $answerC = trim($_POST['c']);
    $answerD = trim($_POST['d']);
    $correct = strtoupper(trim($_POST['rightAns']));
    $fileName = null;

    // handle optional file upload
    if (!empty($_FILES['pic']['name'])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = basename($_FILES['pic']['name']);
        $targetPath = $uploadDir . $fileName;
        move_uploaded_file($_FILES['pic']['tmp_name'], $targetPath);
    }

    // find quizID matching this topic & educator (since recommendedquestion links to quiz)
    $quizID = null;
    $quizQuery = $conn->prepare("SELECT id FROM quiz WHERE topicID=? AND educatorID=? LIMIT 1");
    $quizQuery->bind_param("ii", $topicID, $educatorID);
    $quizQuery->execute();
    $quizQuery->bind_result($quizID);
    $quizQuery->fetch();
    $quizQuery->close();

    if (!$quizID) {
        echo "<script>alert('No quiz found for that topic and educator.');</script>";
    } else {
        // insert the recommended question
        $stmt = $conn->prepare("
            INSERT INTO recommendedquestion 
            (quizID, learnerID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param("iisssssss", $quizID, $learnerID, $question, $fileName, $answerA, $answerB, $answerC, $answerD, $correct);
        $stmt->execute();
        $stmt->close();

        header("Location: learner.php");
        exit();
    }
}

// --- Fetch topics ---
$topics = [];
$result = $conn->query("SELECT id, topicName FROM topic");
while ($row = $result->fetch_assoc()) $topics[] = $row;

// --- Fetch educators ---
$educators = [];
$result = $conn->query("SELECT id, CONCAT(firstName, ' ', lastName) AS fullName FROM user WHERE userType='educator'");
while ($row = $result->fetch_assoc()) $educators[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recommend-Question</title>
  <link rel="stylesheet" href="style.css">
  <style>
    h2 { text-align: center; font-size:2.5rem; }
    form {
      display: flex; flex-direction: column; gap: 0.938em;
      margin: 1.875em auto; padding: 1.25em;
      background-color: white; border-radius: 0.5em;
      box-shadow: 0 0.125em 0.312em rgba(0,0,0,0.1);
      width: 80%; max-width: 600px;
    }
    fieldset { border: 0.062em solid #ddd; padding: 1.25em; border-radius: 0.5em; }
    legend { font-size: 2em; text-align: center; margin-bottom: 1.25em; font-weight: bold; }
    label { display: block; margin: 0.625em 0 0.312em; }
    select, textarea, input {
      width: 100%; padding: 0.625em; border-radius: 0.312em; border: 0.062em solid #ccc;
    }
    textarea { min-height: 5.0em; resize: vertical; }
    #q { display:flex; align-items:center; gap: 0.75rem; flex-wrap: wrap; }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="images/logo.png" alt="LearnIT Logo">
      <h1>LearnIT</h1>
    </div>
    <nav>
      <a href="learner.php">Home</a>
    </nav>
  </header>
  
  <main>
    <div class="card-container">
      <h2>Recommend a Question</h2>

      <form action="" method="POST" enctype="multipart/form-data">
        <fieldset>
          <label>Select a Topic:</label>
          <select id="topic" name="topic" required>
            <option value="" disabled selected>Select topic</option>
            <?php foreach ($topics as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['topicName']) ?></option>
            <?php endforeach; ?>
          </select>

          <label>Choose a Professor:</label>
          <select id="prof" name="prof" required>
            <option value="" disabled selected>Select educator</option>
            <?php foreach ($educators as $e): ?>
              <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['fullName']) ?></option>
            <?php endforeach; ?>
          </select>

          <label>Write your Question here:</label>
          <textarea placeholder="Type your question here.." name="Q" required></textarea>

          <label>Upload figure (optional)</label>
          <input type="file" name="pic">

          <label>Answer A</label>
          <input type="text" name="a" required>

          <label>Answer B</label>
          <input type="text" name="b" required>

          <label>Answer C</label>
          <input type="text" name="c" required>

          <label>Answer D</label>
          <input type="text" name="d" required>

          <label>Select The right answer:</label>
          <select id="rightAns" name="rightAns" required>
            <option value="" disabled selected>Select right answer</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
          </select>
        </fieldset>

        <div id="q">
          <button type="submit" class="submit">Submit Question</button>
          <a href="learner.php" class="takeHome">Cancel</a>
        </div>
      </form>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
  </footer>
</body>
</html>
