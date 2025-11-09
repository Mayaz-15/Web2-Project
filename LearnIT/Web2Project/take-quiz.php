<?php
// --- DB + session (match your local creds) ---
require_once 'connect.php';

session_start();


// 🔁 Map login.php session keys to the ones this page expects
if (isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    $_SESSION['id'] = $_SESSION['user_id'];
}
if (isset($_SESSION['user_type']) && !isset($_SESSION['userType'])) {
    $_SESSION['userType'] = $_SESSION['user_type'];
}

// ✅ Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['userType'])) {
    header("Location: index.php?error=unauthorized");
    exit;
}

// ✅ Check if the user is an learner
if ($_SESSION['userType'] !== 'learner') {
    header("Location: index.php?error=unauthorized");
    exit;
}

// inputs
$quizID = isset($_GET['quizID']) ? (int)$_GET['quizID'] : 1;

// fetch quiz meta: topic + educator
$meta = [
  'topicName' => '—',
  'educatorName' => '—'
];
$mres = mysqli_query($conn, "
  SELECT t.topicName, CONCAT(u.firstName, ' ', u.lastName) AS educatorName
  FROM quiz q
  JOIN topic t ON t.id = q.topicID
  JOIN user  u ON u.id = q.educatorID
  WHERE q.id = {$quizID} LIMIT 1
");
if ($mres && mysqli_num_rows($mres)) {
  $meta = mysqli_fetch_assoc($mres);
}

// random 5 (or fewer) questions
$qres = mysqli_query($conn, "
  SELECT id, question, questionFigureFileName, answerA, answerB, answerC, answerD
  FROM quizquestion
  WHERE quizID = {$quizID}
  ORDER BY RAND()
  LIMIT 5
");
$questions = [];
if ($qres) {
  while ($r = mysqli_fetch_assoc($qres)) $questions[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Take Quiz</title>
  <link rel="stylesheet" href="style.css"/>

  <!-- Scoped UI to match your add/edit/quiz pages -->
  <style>
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

    .quiz-scope h2 { text-align: center; font-size:2.5rem; }
    .quiz-scope .q-muted{ color: var(--qz-muted); font-size: 0.95rem; }
    .q-inline{ display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap; }

    .q-card{
      border: 0.08rem solid var(--qz-input-bd);
      border-radius: var(--qz-radius);
      padding: var(--qz-gap-2);
      margin: var(--qz-gap-2) 0;
      background: var(--qz-panel-bg);
    }
    .q-head{ display:flex; justify-content:space-between; align-items:center; gap:var(--qz-gap-2); margin-bottom:0.5rem; }
    .q-figure{ margin: 0.5rem 0; }
    .q-figure img{ max-width:100%; height:auto; border-radius: 0.5rem; border: 0.08rem solid var(--qz-input-bd); }

    .q-answers{ display:grid; gap:0.5rem; margin-top:0.5rem; }
    .q-option{
      display:flex; align-items:center; gap:0.6rem;
      border: 0.08rem solid var(--qz-input-bd);
      border-radius: 0.6rem;
      padding: 0.6rem 0.8rem;
    }
    .q-option input[type="radio"]{ transform: scale(1.05); }

    .q-actions{ display:flex; gap:0.75rem; align-items:center; margin-top: var(--qz-gap-3); }
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

  <main class="quiz-scope">
    <div class="card-container">
      <section class="q-panel">
        <h2>Take Quiz</h2>

        <div class="q-inline" style="justify-content:space-between;">
          <div>
            <strong>Topic:</strong> <?php echo htmlspecialchars($meta['topicName']); ?>
            <span class="q-muted">•</span>
            <strong>Educator:</strong> <?php echo htmlspecialchars($meta['educatorName']); ?>
          </div>
          <div class="q-inline">
            <a class="takeHome" href="learner.php">Back to Dashboard</a>
          </div>
        </div>

        <p class="q-muted" style="margin-top:0.5rem;">Answer all questions. One correct answer per question.</p>

        <?php if (!$questions): ?>
          <p style="margin-top:1rem;">This quiz has no questions yet.</p>
        <?php else: ?>
          <!-- Post to score page (Step 5) -->
          <form action="quiz-result.php" method="post">
            <input type="hidden" name="quizID" value="<?php echo (int)$quizID; ?>" />

            <?php $i=1; foreach($questions as $q): ?>
              <input type="hidden" name="questionID[]" value="<?php echo (int)$q['id']; ?>" />
              <article class="q-card">
                <div class="q-head">
                  <div><strong>Q<?php echo $i++; ?>.</strong> <?php echo htmlspecialchars($q['question']); ?></div>
                </div>

                <?php if (!empty($q['questionFigureFileName'])): ?>
                  <div class="q-figure">
                    <img src="uploads/<?php echo htmlspecialchars($q['questionFigureFileName']); ?>" alt="">
                  </div>
                <?php endif; ?>

                <div class="q-answers">
                  <label class="q-option">
                    <input type="radio" name="answer[<?php echo (int)$q['id']; ?>]" value="A" required>
                    A) <?php echo htmlspecialchars($q['answerA']); ?>
                  </label>
                  <label class="q-option">
                    <input type="radio" name="answer[<?php echo (int)$q['id']; ?>]" value="B">
                    B) <?php echo htmlspecialchars($q['answerB']); ?>
                  </label>
                  <label class="q-option">
                    <input type="radio" name="answer[<?php echo (int)$q['id']; ?>]" value="C">
                    C) <?php echo htmlspecialchars($q['answerC']); ?>
                  </label>
                  <label class="q-option">
                    <input type="radio" name="answer[<?php echo (int)$q['id']; ?>]" value="D">
                    D) <?php echo htmlspecialchars($q['answerD']); ?>
                  </label>
                </div>
              </article>
            <?php endforeach; ?>

            <div class="q-actions">
              <button class="submit" type="submit">Submit Quiz</button>
              <a class="takeHome" href="learner.php">Cancel</a>
            </div>
          </form>
        <?php endif; ?>

      </section>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
  </footer>
</body>
</html>
