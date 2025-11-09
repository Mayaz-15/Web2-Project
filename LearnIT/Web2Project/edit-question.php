<?php
// --- DB + session (your local creds) ---
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

// ✅ Check if the user is an educator
if ($_SESSION['userType'] !== 'educator') {
    header("Location: index.php?error=unauthorized");
    exit;
}

// --- Inputs ---
$qid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($qid <= 0) { die("Missing or invalid question id."); }

// --- Load question + quiz + topic ---
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
      <a href="educator.php">Home</a>
    </nav>
  </header>

  <main class="quiz-scope">
    <div class="card-container">
      <section class="q-panel">
        <h2>Edit Question • <?php echo htmlspecialchars($topicName); ?></h2>

        <div class="q-inline" style="margin-bottom:1rem;">
          <a class="takeHome" href="quiz.php?quizID=<?php echo (int)$quizID; ?>">Back to Quiz</a>
        </div>

        <?php if (isset($_GET['error'])): ?>
          <div style="background:#ffecec;border:1px solid #ffb3b3;color:#b30000;padding:0.75rem;border-radius:0.5rem;margin-bottom:1rem;">
            <?php echo htmlspecialchars($_GET['error']); ?>
          </div>
        <?php endif; ?>

        <form class="flex-container" action="edit-question-process.php" method="post" enctype="multipart/form-data">
          <!-- Hidden IDs -->
          <input type="hidden" name="qid" value="<?php echo (int)$qid; ?>" />
          <input type="hidden" name="quizID" value="<?php echo (int)$quizID; ?>" />

          <fieldset>
            <legend>Question Details</legend>

            <!-- Topic is derived from quiz -->
            <div class="q-row">
              <label>Topic</label>
              <input type="text" value="<?php echo htmlspecialchars($topicName); ?>" readonly
                     style="background:#f7f7f7; border:1px solid #e5e7eb;"/>
            </div>

            <div class="q-row">
              <label for="qtext">Question Text</label>
              <textarea id="qtext" name="qtext" required><?php echo htmlspecialchars($row['question']); ?></textarea>
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
                <input id="c1" name="c1" type="text" required value="<?php echo htmlspecialchars($row['answerA']); ?>"/>
              </div>
              <div class="q-row">
                <label for="c2">Choice B</label>
                <input id="c2" name="c2" type="text" required value="<?php echo htmlspecialchars($row['answerB']); ?>"/>
              </div>
              <div class="q-row">
                <label for="c3">Choice C</label>
                <input id="c3" name="c3" type="text" required value="<?php echo htmlspecialchars($row['answerC']); ?>"/>
              </div>
              <div class="q-row">
                <label for="c4">Choice D</label>
                <input id="c4" name="c4" type="text" required value="<?php echo htmlspecialchars($row['answerD']); ?>"/>
              </div>
            </div>

            <div class="q-row">
              <label for="correct">Correct Answer</label>
              <select id="correct" name="correct" required>
                <option value="">— Choose correct option —</option>
                <option value="A" <?php echo $row['correctAnswer']==='A'?'selected':''; ?>>A</option>
                <option value="B" <?php echo $row['correctAnswer']==='B'?'selected':''; ?>>B</option>
                <option value="C" <?php echo $row['correctAnswer']==='C'?'selected':''; ?>>C</option>
                <option value="D" <?php echo $row['correctAnswer']==='D'?'selected':''; ?>>D</option>
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
