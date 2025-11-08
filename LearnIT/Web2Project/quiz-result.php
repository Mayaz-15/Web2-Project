<?php

session_start();


    include 'connect.php'; // should create $conn (mysqli)

    if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }
 

// --- Session check---
if (!isset($_SESSION['id']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'learner') {
    header("Location: login.php");
    exit();
}
$learnerID = (int) $_SESSION['id'];

// --- Helper to get quizID from POST or GET  ---
$quiz_id = 0;
if (!empty($_POST['quizID'])) $quiz_id = (int) $_POST['quizID'];
elseif (!empty($_GET['quizID'])) $quiz_id = (int) $_GET['quizID'];

if ($quiz_id <= 0) {
    // If feedback submission posts quizID as hidden, that will also be processed below.
    // If no quizID, can't proceed.
    die("Quiz ID missing. Make sure the take-quiz form posts quizID.");
}

// --- If this is feedback submission (rating + comments), handle and redirect immediately ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating']) && isset($_POST['comments']) && !isset($_POST['answer'])) {
    $rating = intval($_POST['rating']);
    $comments = trim($_POST['comments']);

    // Insert feedback
    $stmt = $conn->prepare("INSERT INTO quizfeedback (quizID, rating, comments) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iis", $quiz_id, $rating, $comments);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: learner.php");
    exit();
}

// --- Fetch quiz meta (topic + educator) using your DB schema ---
$quiz = null;
if ($meta_stmt = $conn->prepare("
    SELECT q.id, t.topicName, CONCAT(u.firstName, ' ', u.lastName) AS educatorName
    FROM quiz q
    JOIN topic t ON q.topicID = t.id
    JOIN user u ON q.educatorID = u.id
    WHERE q.id = ?
    LIMIT 1
")) {
    $meta_stmt->bind_param("i", $quiz_id);
    $meta_stmt->execute();
    $meta_res = $meta_stmt->get_result();
    $quiz = $meta_res->fetch_assoc();
    $meta_stmt->close();
}
if (!$quiz) {
    die("Quiz not found.");
}

// --- Grade quiz only if answers are present (take-quiz posts answer[...] array) ---
$scorePercent = 0.00;
$totalQuestions = 0;
$correctAnswers = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['answer']) && is_array($_POST['answer'])) {
    // expected shape: $_POST['answer'][<questionId>] => 'A'|'B'|'C'|'D'
    foreach ($_POST['answer'] as $qid => $selected) {
        $qid = (int)$qid;
        $selectedOpt = strtoupper(trim($selected));
        if ($qid <= 0) continue;

        // Ensure this question belongs to the quiz (protect against tampering)
        if ($q_stmt = $conn->prepare("SELECT correctAnswer FROM quizquestion WHERE id = ? AND quizID = ? LIMIT 1")) {
            $q_stmt->bind_param("ii", $qid, $quiz_id);
            $q_stmt->execute();
            $q_stmt->bind_result($correctAnswer);
            if ($q_stmt->fetch()) {
                $totalQuestions++;
                if ($selectedOpt === strtoupper($correctAnswer)) $correctAnswers++;
            }
            $q_stmt->close();
        }
    }

    if ($totalQuestions > 0) {
        $scorePercent = round(($correctAnswers / $totalQuestions) * 100, 2);
    } else {
        $scorePercent = 0.00;
    }

    // Save result in takenquiz (schema: id, quizID, score)
    if ($ins = $conn->prepare("INSERT INTO takenquiz (quizID, score) VALUES (?, ?)")) {
        $ins->bind_param("id", $quiz_id, $scorePercent);
        $ins->execute();
        $ins->close();
    }
} else {
    // No posted answers — possible user navigated here without submitting
    header("Location: take-quiz.php?quizID={$quiz_id}");
    exit();
}

// --- Decide reaction video/messages (adjust files to match your uploads folder) ---
if ($scorePercent >= 90) {
    $video = "images/cheer.mp4";
    $message = "Excellent! You scored {$scorePercent}%!";
} elseif ($scorePercent >= 60) {
    $video = "images/goodjob.mp4";
    $message = "Good work! You scored {$scorePercent}%!";
} else {
    $video = "images/tryagain.mp4";
    $message = "Don't give up! You scored {$scorePercent}%!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Quiz Results | LearnIT</title>
  <link rel="stylesheet" href="style.css">
  <style>
    h2 { text-align: center; margin-bottom: 1.25em; }
    #result { text-align: center; font-size: 1.5rem; margin-bottom: 1.25em; }
    #vid { display: block; margin: 0 auto 1.875em auto; width: 60%;
           border: 0.125em solid #ccc; border-radius: 0.625em; }
    form { display: flex; flex-direction: column; gap: 0.938em; }
    fieldset { border: 0.062em solid #ddd; padding: 1.25em; border-radius: 0.5em; }
    legend { font-weight: bold; padding: 0 0.625em; }
    label { display: block; margin: 0.625em 0 0.312em; }
    select, textarea { width: 100%; padding: 0.625em; border-radius: 0.312em; border: 0.062em solid #ccc; }
    textarea { min-height: 5.0em; resize: vertical; }
    #q { display:flex; align-items:center; gap: 0.75rem; flex-wrap: wrap; }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="images/logo.png" alt="LearnIT Logo"/>
      <h1>LEARNIT</h1>
    </div>
    <nav>
      <a href="learner.php">Home</a>
    </nav>
  </header>

  <main>
    <div class="card-container">
      <h2>Quiz Results</h2>

      <p style="text-align:center">
        <strong>Topic:</strong> <?= htmlspecialchars($quiz['topicName']) ?> |
        <strong>Educator:</strong> <?= htmlspecialchars($quiz['educatorName']) ?>
      </p>

      <div id="result"><?= htmlspecialchars($message) ?></div>

      <video id="vid" src="<?= htmlspecialchars($video) ?>" autoplay muted loop>
        Sorry, your browser doesn't support embedded videos.
      </video>

      <!-- Feedback Form (posts rating + comments + hidden quizID) -->
      <form action="quiz-result.php" method="POST">
        <fieldset>
          <legend>Rate the Quiz!</legend>
          <input type="hidden" name="quizID" value="<?= (int)$quiz_id ?>">

          <label for="rating">How was the quiz?</label>
          <select id="rating" name="rating" required>
            <option value="" disabled selected>Select rating</option>
            <option value="1">Poor</option>
            <option value="2">Bad</option>
            <option value="3">Average</option>
            <option value="4">Good</option>
            <option value="5">Excellent</option>
          </select>

          <label for="comments">Would you like to leave a comment?</label>
          <textarea id="comments" name="comments" placeholder="Please share your thoughts!"></textarea>
        </fieldset>

        <div id="q">
          <button type="submit" class="submit">Submit Feedback</button>
          <a class="takeHome" href="learner.php">Return Home</a>
        </div>
      </form>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
  </footer>
</body>
</html>
