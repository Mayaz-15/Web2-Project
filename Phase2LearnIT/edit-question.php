<?php
// --- DB + session (your local creds) ---
$conn = mysqli_connect("localhost", "root", "root", "dblearnit");
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }
mysqli_set_charset($conn, "utf8mb4");

session_start();
// Dev stub (remove when real login exists)
if (!isset($_SESSION['id'])) {
  $_SESSION['id'] = 1;
  $_SESSION['userType'] = 'educator';
}

// --- Inputs ---
$qid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($qid <= 0) { die("Missing or invalid question id."); }

// --- Load question + quiz + topic for GET and sticky POST ---
$sql = "SELECT qq.id, qq.quizID, qq.question, qq.questionFigureFileName,
               qq.answerA, qq.answerB, qq.answerC, qq.answerD, qq.correctAnswer,
               t.topicName
        FROM quizquestion qq
        JOIN quiz q ON q.id = qq.quizID
        JOIN topic t ON t.id = q.topicID
        WHERE qq.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $qid);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (!$res || !mysqli_num_rows($res)) { die("Question not found."); }
$row = mysqli_fetch_assoc($res);

$quizID     = (int)$row['quizID'];
$topicName  = $row['topicName'];
$oldImgName = $row['questionFigureFileName'];

// For sticky fields (prefill with DB values by default)
$old = [
  'qtext'   => $_POST['qtext'] ?? $row['question'],
  'c1'      => $_POST['c1'] ?? $row['answerA'],
  'c2'      => $_POST['c2'] ?? $row['answerB'],
  'c3'      => $_POST['c3'] ?? $row['answerC'],
  'c4'      => $_POST['c4'] ?? $row['answerD'],
  'correct' => $_POST['correct'] ?? $row['correctAnswer'],
];
$errors = [];

// --- Handle POST: update fields, optionally replace image ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (trim($old['qtext']) === '') $errors[] = "Question text is required.";
  if (trim($old['c1'])   === '') $errors[] = "Choice A is required.";
  if (trim($old['c2'])   === '') $errors[] = "Choice B is required.";
  if (trim($old['c3'])   === '') $errors[] = "Choice C is required.";
  if (trim($old['c4'])   === '') $errors[] = "Choice D is required.";
  if (!in_array($old['correct'], ['A','B','C','D'], true)) $errors[] = "Choose a correct answer (A–D).";

  $newImgName = $oldImgName; // default: keep current image

  // If a new image is uploaded, validate & replace
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

      $newImgName = "q{$quizID}-" . time() . "." . $ext;
      $dest = $uploadDir . "/" . $newImgName;
      if (!move_uploaded_file($_FILES['qfigure']['tmp_name'], $dest)) {
        $errors[] = "Failed to move uploaded file.";
      } else {
        // delete old file if existed
        if (!empty($oldImgName)) {
          $oldPath = $uploadDir . "/" . $oldImgName;
          if (is_file($oldPath)) { @unlink($oldPath); }
        }
      }
    }
  }

  if (!$errors) {
    $upd = "UPDATE quizquestion
            SET question=?, questionFigureFileName=?, answerA=?, answerB=?, answerC=?, answerD=?, correctAnswer=?
            WHERE id=?";
    $ust = mysqli_prepare($conn, $upd);
    mysqli_stmt_bind_param(
      $ust, "sssssssi",
      $old['qtext'], $newImgName, $old['c1'], $old['c2'], $old['c3'], $old['c4'], $old['correct'], $qid
    );
    mysqli_stmt_execute($ust);

    header("Location: quiz.php?quizID=".$quizID);
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit-Question</title>
  <link rel="stylesheet" href="style.css" />
  <style>
  /* Same scoped system used in your original edit-question.html */
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
  form.flex-container{ display:flex; flex-direction:column; gap:0.938em; }
  fieldset{ border:0.062em solid #ddd; padding:1.25em; border-radius:0.5em; }
  legend{ font-weight:700; padding:0 0.625em; }
  .quiz-scope h2 { text-align:center; font-size:2.5rem; }
  .quiz-scope h3{ margin:var(--qz-gap-3) 0 var(--qz-gap-2); font-size:1.125rem; }
  .quiz-scope .q-help{ font-size:0.9rem; color:var(--qz-muted); }
  .quiz-scope .q-inline{ display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap; }
  .quiz-scope .q-row{ display:grid; gap:0.5rem; }
  .quiz-scope label{ font-size:0.95rem; color:var(--qz-muted); }
  .quiz-scope input[type="text"],
  .quiz-scope input[type="number"],
  .quiz-scope input[type="email"],
  .quiz-scope input[type="file"],
  .quiz-scope select,
  .quiz-scope textarea{
    width:100%; height:var(--qz-input-h); padding:0 var(--qz-gap-2);
    background:var(--qz-input-bg); border:0.08rem solid var(--qz-input-bd);
    border-radius:var(--qz-radius); color:inherit; outline:none;
  }
  .quiz-scope textarea{
    height:auto; min-height:8rem; padding:var(--qz-gap-2); resize:vertical;
  }
  .quiz-scope .q-choices{
    display:grid; grid-template-columns:repeat(2, minmax(0,1fr));
    gap:var(--qz-gap-2);
  }
  @media (max-width:40rem){
    .quiz-scope .q-choices{ grid-template-columns:1fr; }
  }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="images/logo.png" alt="LearnIT Logo" />
      <h1>LEARNIT</h1>
    </div>
    <nav>
      <a href="quiz.php?quizID=<?php echo (int)$quizID; ?>">Back to Quiz</a>
    </nav>
  </header>

  <main class="quiz-scope">
    <div class="card-container">
      <section class="q-panel">
        <h2>Edit Question • <?php echo htmlspecialchars($topicName); ?></h2>

        <div class="q-inline" style="margin-bottom:1rem;">
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

        <form class="flex-container" action="edit-question.php?id=<?php echo (int)$qid; ?>" method="post" enctype="multipart/form-data">
          <input type="hidden" name="qid" value="<?php echo (int)$qid; ?>" />

          <fieldset>
            <legend>Question Details</legend>

            <!-- Topic is derived from quiz; don’t let it drift -->
            <div class="q-row">
              <label>Topic</label>
              <input type="text" value="<?php echo htmlspecialchars($topicName); ?>" readonly
                     style="background:#f7f7f7; border:1px solid #e5e7eb;"/>
            </div>

            <div class="q-row">
              <label for="qtext">Question Text</label>
              <textarea id="qtext" name="qtext" required><?php echo htmlspecialchars($old['qtext']); ?></textarea>
            </div>

            <div class="q-row">
              <label>Current Figure</label>
              <?php if (!empty($oldImgName)): ?>
                <img src="uploads/<?php echo htmlspecialchars($oldImgName); ?>" alt=""
                     style="max-width:240px; max-height:160px; display:block; margin-bottom:0.5rem;">
                <p class="q-help">Upload a new image to replace the current one.</p>
              <?php else: ?>
                <p class="q-help">No current figure. You can upload one below.</p>
              <?php endif; ?>
            </div>

            <div class="q-row">
              <label for="qfigure">Optional New Figure (replace to update)</label>
              <input id="qfigure" name="qfigure" type="file" accept="image/*" />
              <p class="q-help">Leave empty to keep the current figure.</p>
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
            <button class="submit" type="submit">Update Question</button>
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
