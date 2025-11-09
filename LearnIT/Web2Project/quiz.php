<?php
// --- DB + session ---
require_once 'connect.php';

session_start();


// 🔁 Map login.php session keys to the ones this page expects
if (isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    $_SESSION['id'] = $_SESSION['user_id'];
}
if (isset($_SESSION['user_type']) && !isset($_SESSION['userType'])) {
    $_SESSION['userType'] = $_SESSION['user_type'];
}

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['userType'])) {
    header("Location: index.php?error=unauthorized");
    exit;
}

// Check if the user is an educator
if ($_SESSION['userType'] !== 'educator') {
    header("Location: index.php?error=unauthorized");
    exit;
}
// --- inputs ---
// Support BOTH ?quizID= and ?quiz_id= so it works with educator.php
$quizID = 0;
if (isset($_GET['quizID'])) {
    $quizID = (int)$_GET['quizID'];
} elseif (isset($_GET['quiz_id'])) {
    $quizID = (int)$_GET['quiz_id'];
}

// Safety: if nothing is passed, stop instead of silently using 1
if ($quizID <= 0) {
    die("No quiz selected. (Missing quizID in URL)");
}
// optional role guard (during dev you can keep it soft)
if ($_SESSION['userType'] !== 'educator') {
  // header("Location: index.php"); exit;
}


// --- delete handler ---
if (isset($_GET['delete'])) {
  $qid = (int)$_GET['delete'];

  // fetch old image to delete file if exists
  $imgRes = mysqli_query($conn, "SELECT questionFigureFileName FROM quizquestion WHERE id=$qid AND quizID=$quizID");
  if ($imgRes && mysqli_num_rows($imgRes)) {
    $imgRow = mysqli_fetch_assoc($imgRes);
    if (!empty($imgRow['questionFigureFileName'])) {
      $path = __DIR__ . "/uploads/" . $imgRow['questionFigureFileName'];
      if (is_file($path)) { @unlink($path); }
    }
  }
  mysqli_query($conn, "DELETE FROM quizquestion WHERE id=$qid AND quizID=$quizID");
  header("Location: quiz.php?quizID=".$quizID); exit;
}

// --- fetch topic name (for header) ---
$topicName = "Quiz";
$tres = mysqli_query($conn,
  "SELECT t.topicName
     FROM quiz q
     JOIN topic t ON t.id = q.topicID
    WHERE q.id = $quizID LIMIT 1");
if ($tres && mysqli_num_rows($tres)) {
  $topicName = mysqli_fetch_assoc($tres)['topicName'];
}

// --- fetch questions ---
$qres = mysqli_query($conn, "
  SELECT id, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer
  FROM quizquestion
  WHERE quizID = $quizID
  ORDER BY id
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Quiz</title>
  <link rel="stylesheet" href="style.css"/>

  <style>
  /* Same variable system as add-question.html */
  .quiz-scope{
    --qz-gap-1: 0.5rem;
    --qz-gap-2: 1rem;
    --qz-gap-3: 1.5rem;
    --qz-gap-4: 2rem;
    --qz-radius: 0.8rem;
    --qz-input-h: 2.75rem;

    --qz-text: inherit;
    --qz-muted: color-mix(in oklab, currentColor 55%, transparent);
    --qz-accent: currentColor;
    --qz-panel-bg: var(--surface-1, #ffffff);
    --qz-panel-bd: color-mix(in oklab, currentColor 85%, transparent);
    --qz-input-bg: var(--surface-2, #ffffff);
    --qz-input-bd: color-mix(in oklab, currentColor 80%, transparent);
    --qz-ok-bd: color-mix(in oklab, #22c55e 40%, transparent);
    --qz-ok-bg: color-mix(in oklab, #22c55e 12%, transparent);
  }

  /* Headings & utilities to match */
  
    .quiz-scope h2 {
      text-align: center;
	  font-size:2.5rem;
    }
  .quiz-scope .q-help{ font-size:0.9rem; color:var(--qz-muted); }
  .quiz-scope .q-inline{ display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap; }

  /* Read-only quiz cards */
  .quiz-scope .q-card{
    border:0.08rem solid var(--qz-input-bd);
    border-radius:var(--qz-radius);
    padding:var(--qz-gap-2);
    margin:var(--qz-gap-2) 0;
    background:var(--qz-panel-bg);
  }
  .quiz-scope .q-head{
    display:flex; align-items:center; justify-content:space-between;
    gap:var(--qz-gap-2); margin-bottom:var(--qz-gap-2);
  }
  .quiz-scope .q-actions{ display:flex; gap:0.5rem; }
  .quiz-scope .q-answers{ display:grid; gap:0.5rem; margin-top:0.75rem; }
  .quiz-scope .q-answer{
    padding:0.6rem 0.8rem;
    border-radius:0.6rem;
    border:0.08rem solid var(--qz-input-bd);
  }
  .quiz-scope .q-answer.is-correct{
    border-color:var(--qz-ok-bd);
    background:var(--qz-ok-bg);
  }


  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="images/logo.png" alt="LearnIT Logo"/>
      <h1>LearnIT</h1>
    </div>
    <nav>
      <a href="educator.php">Home</a>
    </nav>
  </header>

  <main class="quiz-scope">
    <div class="card-container">
      <section class="q-panel">
        <h2><?php echo htmlspecialchars($topicName); ?> — Quiz #<?php echo (int)$quizID; ?></h2>
<div class="q-inline" style="margin-bottom:1rem;">
  <a class="btn submit" href="add-question.php?quizID=<?php echo (int)$quizID; ?>">
    + Add Question
  </a>

  <!-- Back to Educator Home instead of Preview / Take Quiz -->
  <a class="takeHome" href="educator.php">
    ← Back to Dashboard
  </a>
</div>

        <!-- Q -->
        <?php if ($qres && mysqli_num_rows($qres)): ?>
          <?php $n=1; while($row = mysqli_fetch_assoc($qres)): ?>
            <article class="q-card">
              <div class="q-head">
                <div><strong>Q<?php echo $n++; ?>.</strong> <?php echo htmlspecialchars($row['question']); ?></div>
                <div class="q-actions">
                  <a class="btn" href="edit-question.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
                  <a class="btn delete"
   href="delete-question.php?quizID=<?php echo $quizID; ?>&id=<?php echo $row['id']; ?>"
   onclick="return confirm('Delete this question?');">Delete</a>

                </div>
              </div>

              <?php if (!empty($row['questionFigureFileName'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($row['questionFigureFileName']); ?>"
                     alt="" style="max-width:240px; max-height:160px; display:block; margin-bottom:0.5rem;">
              <?php else: ?>
                <p class="q-help">No figure provided.</p>
              <?php endif; ?>

              <div class="q-answers">
                <div class="q-answer <?php echo ($row['correctAnswer']==='A'?'is-correct':''); ?>">A) <?php echo htmlspecialchars($row['answerA']); ?></div>
                <div class="q-answer <?php echo ($row['correctAnswer']==='B'?'is-correct':''); ?>">B) <?php echo htmlspecialchars($row['answerB']); ?></div>
                <div class="q-answer <?php echo ($row['correctAnswer']==='C'?'is-correct':''); ?>">C) <?php echo htmlspecialchars($row['answerC']); ?></div>
                <div class="q-answer <?php echo ($row['correctAnswer']==='D'?'is-correct':''); ?>">D) <?php echo htmlspecialchars($row['answerD']); ?></div>
              </div>
            </article>
          <?php endwhile; ?>
        <?php else: ?>
          <p>No questions yet for this quiz.</p>
        <?php endif; ?>

      </section>
    </div>
  </main> 

  <footer>
    <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
  </footer>
</body>
</html>
