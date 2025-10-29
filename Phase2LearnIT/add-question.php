<?php
// --- DB + session (matches your local creds) ---
$conn = mysqli_connect("localhost", "root", "root", "dblearnit");
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }
mysqli_set_charset($conn, "utf8mb4");

session_start();
// Dev stub (remove when real login exists)
if (!isset($_SESSION['id'])) {
  $_SESSION['id'] = 1;
  $_SESSION['userType'] = 'educator';
}

// quizID from GET or POST (default 1)
$quizID = isset($_GET['quizID']) ? (int)$_GET['quizID'] : (int)($_POST['quizID'] ?? 1);

// fetch topic name (header display)
$topicName = "—";
$tres = mysqli_query($conn,
  "SELECT t.topicName
     FROM quiz q
     JOIN topic t ON t.id = q.topicID
    WHERE q.id = {$quizID} LIMIT 1");
if ($tres && mysqli_num_rows($tres)) {
  $topicName = mysqli_fetch_assoc($tres)['topicName'];
}

// sticky form + errors
$old = [
  'qtext' => $_POST['qtext'] ?? '',
  'c1'    => $_POST['c1'] ?? '',
  'c2'    => $_POST['c2'] ?? '',
  'c3'    => $_POST['c3'] ?? '',
  'c4'    => $_POST['c4'] ?? '',
  'correct' => $_POST['correct'] ?? '',
];
$errors = [];

// handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (trim($old['qtext']) === '') $errors[] = "Question text is required.";
  if (trim($old['c1'])   === '') $errors[] = "Choice A is required.";
  if (trim($old['c2'])   === '') $errors[] = "Choice B is required.";
  if (trim($old['c3'])   === '') $errors[] = "Choice C is required.";
  if (trim($old['c4'])   === '') $errors[] = "Choice D is required.";
  if (!in_array($old['correct'], ['A','B','C','D'], true)) $errors[] = "Choose the correct answer (A–D).";

  // optional image
  $figName = NULL;
  if (!empty($_FILES['qfigure']['name'])) {
    $allowed = ['png','jpg','jpeg','gif','webp'];
    $ext = strtolower(pathinfo($_FILES['qfigure']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
      $errors[] = "Invalid image type. Allowed: " . implode(', ', $allowed);
    }
    if ($_FILES['qfigure']['error'] !== UPLOAD_ERR_OK) {
      $errors[] = "Image upload error (code ".$_FILES['qfigure']['error'].").";
    }
    if ($_FILES['qfigure']['size'] > 3 * 1024 * 1024) {
      $errors[] = "Image too large (max 3MB).";
    }

    if (!$errors) {
      $uploadDir = __DIR__ . "/uploads";
      if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }
      $figName = "q{$quizID}-" . time() . "." . $ext;
      $dest = $uploadDir . "/" . $figName;
      if (!move_uploaded_file($_FILES['qfigure']['tmp_name'], $dest)) {
        $errors[] = "Failed to move uploaded file.";
      }
    }
  }

  if (!$errors) {
    $sql = "INSERT INTO quizquestion
      (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
      $stmt, "isssssss",
      $quizID, $old['qtext'], $figName, $old['c1'], $old['c2'], $old['c3'], $old['c4'], $old['correct']
    );
    mysqli_stmt_execute($stmt);

    header("Location: quiz.php?quizID=".$quizID);
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add-Question</title>
  <link rel="stylesheet" href="style.css">
  <style>
/* Scoped to quiz area only (from your original) */
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
form.flex-container { display:flex; flex-direction:column; gap:0.938em; }
fieldset { border:0.062em solid #ddd; padding:1.25em; border-radius:0.5em; }
legend { font-weight:bold; padding:0 0.625em; }
.quiz-scope h3{ margin: var(--qz-gap-3) 0 var(--qz-gap-2); font-size:1.125rem; }
/* Utilities */
.quiz-scope .q-help{ font-size:0.9rem; color:var(--qz-muted); }
.quiz-scope .q-inline{ display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap; }
/* Form layout & inputs */
.quiz-scope .q-form{ display:grid; gap: var(--qz-gap-2); }
.quiz-scope .q-row{ display:grid; gap: 0.5rem; }
.quiz-scope label{ font-size:0.95rem; color:var(--qz-muted); }
.quiz-scope input[type="text"], .quiz-scope input[type="number"], .quiz-scope input[type="email"],
.quiz-scope input[type="file"], .quiz-scope select, .quiz-scope textarea{
  width:100%; height:var(--qz-input-h); padding:0 var(--qz-gap-2);
  background:var(--qz-input-bg); border:0.08rem solid var(--qz-input-bd);
  border-radius:var(--qz-radius); color:inherit; outline:none;
}
.quiz-scope textarea{ height:auto; min-height:8rem; padding:var(--qz-gap-2); resize:vertical; }
/* Choice grid (A–D) */
.quiz-scope .q-choices{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:var(--qz-gap-2); }
/* Responsive */
@media (max-width: 40rem){ .quiz-scope .q-choices{ grid-template-columns:1fr; } }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="images/logo.png" alt="LearnIT Logo">
      <h1>LEARNIT</h1>
    </div>
    <nav>
      <a href="quiz.php?quizID=<?php echo (int)$quizID; ?>">Back to Quiz</a>
    </nav>
  </header>

  <main class="quiz-scope">
    <div class="card-container">
      <section class="q-panel">
        <h2>Add Question • <?php echo htmlspecialchars($topicName); ?></h2>

        <!-- top actions -->
        <div class="q-inline" style="margin-bottom: 1rem;">
          <a class="takeHome" href="quiz.php?quizID=<?php echo (int)$quizID; ?>">Back to Quiz</a>
        </div>

        <?php if ($errors): ?>
          <div style="background:#ffecec;border:1px solid #ffb3b3;color:#b30000;padding:0.75rem;border-radius:0.5rem;margin-bottom:1rem;">
            <strong>Please fix the following:</strong>
            <ul style="margin-left:1.25rem;">
              <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- your original layout, now posting to self -->
        <form class="flex-container" action="add-question.php?quizID=<?php echo (int)$quizID; ?>" method="post" enctype="multipart/form-data">
          <input type="hidden" name="quizID" value="<?php echo (int)$quizID; ?>"/>

          <fieldset>
            <legend>Question Details</legend>

            <!-- Topic display (replaces static select to avoid mismatches) -->
            <div class="q-row">
              <label>Topic</label>
              <input type="text" value="<?php echo htmlspecialchars($topicName); ?>" readonly
                     style="background:#f7f7f7; border:1px solid #e5e7eb;"/>
            </div>

            <div class="q-row">
              <label for="qtext">Question Text</label>
              <textarea id="qtext" name="qtext" required placeholder="Write the question..."><?php echo htmlspecialchars($old['qtext']); ?></textarea>
            </div>

            <div class="q-row">
              <label for="qfigure">Optional Figure</label>
              <input id="qfigure" name="qfigure" type="file" accept="image/*" />
              <p class="q-help">If no file is selected, the question will have no figure.</p>
            </div>

            <h3>Choices</h3>
            <div class="q-choices">
              <div class="q-row">
                <label for="c1">Choice A</label>
                <input id="c1" name="c1" type="text" required value="<?php echo htmlspecialchars($old['c1']); ?>"/>
              </div>
              <div class="q-row">
                <label for="c2">Choice B</label>
                <input id="c2" name="c2" type="text" required value="<?php echo htmlspecialchars($old['c2']); ?>"/>
              </div>
              <div class="q-row">
                <label for="c3">Choice C</label>
                <input id="c3" name="c3" type="text" required value="<?php echo htmlspecialchars($old['c3']); ?>"/>
              </div>
              <div class="q-row">
                <label for="c4">Choice D</label>
                <input id="c4" name="c4" type="text" required value="<?php echo htmlspecialchars($old['c4']); ?>"/>
              </div>
            </div>

            <div class="q-row">
              <label for="correct">Correct Answer</label>
              <select id="correct" name="correct" required>
                <option value="">— Choose correct option —</option>
                <option value="A" <?php echo $old['correct']==='A'?'selected':''; ?>>A</option>
                <option value="B" <?php echo $old['correct']==='B'?'selected':''; ?>>B</option>
                <option value="C" <?php echo $old['correct']==='C'?'selected':''; ?>>C</option>
                <option value="D" <?php echo $old['correct']==='D'?'selected':''; ?>>D</option>
              </select>
            </div>
          </fieldset>

          <div class="q-inline">
            <button class="submit" type="submit">Save Question</button>
            <a class="takeHome" href="quiz.php?quizID=<?php echo (int)$quizID; ?>">Cancel</a>
          </div>
        </form>
      </section>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
  </footer>
</body>
</html>
