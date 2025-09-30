<?php

include "connect.php";


$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
if ($quiz_id <= 0) {
    die("Invalid quiz ID.");
}

$quiz_sql = $conn->prepare("
    SELECT q.id, q.title, t.name AS topic, e.name AS educator
    FROM quizzes q
    JOIN topics t ON q.topic_id = t.id
    JOIN educators e ON q.educator_id = e.id
    WHERE q.id = ?
");
$quiz_sql->bind_param("i", $quiz_id);
$quiz_sql->execute();
$quiz = $quiz_sql->get_result()->fetch_assoc();
$quiz_sql->close();

if (!$quiz) {
    die("Quiz not found.");
}

$score = 0;
$total = 0;
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['answers'])) {
    $answers = $_POST['answers']; // array of q_id => chosen_answer_id

    foreach ($answers as $question_id => $selected_option) {
        $stmt = $conn->prepare("SELECT correct_option FROM questions WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $stmt->bind_result($correct_option);
        $stmt->fetch();
        $stmt->close();

        $total++;
        if ($selected_option == $correct_option) {
            $score++;
        }
    }
}

$percentage = $total > 0 ? round(($score / $total) * 100) : 0;

if ($percentage == 100) {
    $video = "images/cheer.mp4";
    $message = "Congratulations! You got 100%!";
} elseif ($percentage >= 60) {
    $video = "images/goodjob.mp4";
    $message = "Well done! You scored {$percentage}%!";
} else {
    $video = "images/tryagain.mp4";
    $message = "Keep practicing! You scored {$percentage}%!";
}


if (isset($_POST['rating'], $_POST['comments'])) {
    $rating = intval($_POST['rating']);
    $comments = trim($_POST['comments']);

    $stmt = $conn->prepare("INSERT INTO feedback (quiz_id, rating, comments) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $quiz_id, $rating, $comments);
    $stmt->execute();
    $stmt->close();

   
    header("Location: learner.php");
    exit();
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
	
    select, textarea {
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

  <div class="card-container">
  
    <h2>Quiz Results - <?= htmlspecialchars($quiz['title']) ?> </h2>
    <p style="text-align:center"><strong>Topic:</strong> <?= htmlspecialchars($quiz['topic']) ?> | <strong>Educator:</strong> <?= htmlspecialchars($quiz['educator']) ?></p>

    <div id="result"><?= $message ?></div>

    <video id="vid" src="<?= $video ?>" autoplay muted loop>
      Sorry, your browser doesn't support embedded videos.
    </video>

    <form action="" method="POST">
      <fieldset>
        <legend>Rate the Quiz!</legend>

        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

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

  <footer>
    <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
  </footer>
</body>
</html>
