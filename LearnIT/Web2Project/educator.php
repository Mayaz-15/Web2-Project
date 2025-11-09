<?php
// ===== Educator Home (dashboard only; part D handled in educator_review.php) =====
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/connect.php';  // defines $conn (PDO or MySQLi)

// --- Auth ---
if (empty($_SESSION['id']) || empty($_SESSION['userType'])) {
  header('Location: index.php'); exit;
}
if ($_SESSION['userType'] !== 'educator') {
  header('Location: login.php'); exit;
}
$userId = (int)$_SESSION['user_id'];

// ---------- Helpers ----------
function fetch_one($conn, $sql, $params = []) {
  if ($conn instanceof PDO) {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
  }
  if ($conn instanceof mysqli) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $stmt = $conn->prepare($sql);
    if ($params) { $types = str_repeat('i', count($params)); $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $res = $stmt->get_result();
    return $res ? ($res->fetch_assoc() ?: []) : [];
  }
  return [];
}

function fetch_all($conn, $sql, $params = []) {
  if ($conn instanceof PDO) {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  if ($conn instanceof mysqli) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $stmt = $conn->prepare($sql);
    if ($params) { $types = str_repeat('i', count($params)); $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) { $rows[] = $row; }
    return $rows;
  }
  return [];
}

// ---------- Profile ----------
$row = fetch_one(
  $conn,
  "SELECT firstName, lastName, emailAddress, COALESCE(photoFileName,'') AS photoFileName
   FROM User WHERE id = ? LIMIT 1",
  [$userId]
);
if (!$row) {
  $row = fetch_one(
    $conn,
    "SELECT 
       COALESCE(firstName, first_name) AS firstName,
       COALESCE(lastName,  last_name)  AS lastName,
       COALESCE(emailAddress, email)   AS emailAddress,
       COALESCE(photoFileName, photo, '') AS photoFileName
     FROM users WHERE id = ? LIMIT 1",
    [$userId]
  );
}
$firstName     = $row['firstName']     ?? 'User';
$lastName      = $row['lastName']      ?? '';
$emailAddress  = $row['emailAddress']  ?? '';
$photoFileName = $row['photoFileName'] ?? '';

$firstNameSafe = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
$lastNameSafe  = htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8');
$emailSafe     = htmlspecialchars($emailAddress, ENT_QUOTES, 'UTF-8');
$photoPath     = trim($photoFileName) !== '' ? "images/" . $photoFileName : "images/pfp.png";
$photoPathSafe = htmlspecialchars($photoPath, ENT_QUOTES, 'UTF-8');

// ---------- 6(c): Your Quizzes ----------
$quizzes = fetch_all(
  $conn,
  "SELECT 
      q.id AS quiz_id,
      t.topicName AS topic_name,
      (SELECT COUNT(*) FROM QuizQuestion qq WHERE qq.quizID = q.id) AS questions_count,
      (SELECT COUNT(*) FROM TakenQuiz tq WHERE tq.quizID = q.id)      AS takers_count,
      (SELECT AVG(tq.score) FROM TakenQuiz tq WHERE tq.quizID = q.id) AS avg_score,
      (SELECT COUNT(*) FROM QuizFeedback f WHERE f.quizID = q.id)     AS feedback_count,
      (SELECT AVG(f.rating) FROM QuizFeedback f WHERE f.quizID = q.id) AS avg_rating
   FROM Quiz q
   JOIN Topic t ON t.id = q.topicID
   WHERE q.educatorID = ?",
  [$userId]
);

// ---------- 6(d): Pending Recommended Questions (display only) ----------
$recs = fetch_all(
  $conn,
  "SELECT 
     rq.id              AS rec_id,
     rq.quizID          AS quiz_id,
     rq.learnerID       AS learner_id,
     rq.question        AS q_text,
     rq.questionFigureFileName AS q_img,
     rq.answerA, rq.answerB, rq.answerC, rq.answerD,
     rq.correctAnswer   AS correct,
     t.topicName        AS topic_name,
     u.firstName        AS learner_first,
     u.lastName         AS learner_last,
     COALESCE(u.photoFileName,'') AS learner_photo
   FROM RecommendedQuestion rq
   JOIN Quiz   q  ON q.id = rq.quizID
   JOIN Topic  t  ON t.id = q.topicID
   JOIN User   u  ON u.id = rq.learnerID
   WHERE q.educatorID = ? AND LOWER(rq.status) = 'pending'
   ORDER BY rq.id DESC",
  [$userId]
);
?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educators HomePage</title>
    <link rel="stylesheet" href="style.css">
    <style>
      .learneredu { display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; height:100%; }
      .learneredu img { width:3rem; height:3rem; object-fit:cover; border-radius:50%; margin-bottom:.3rem; }
      .learneredu p { margin:0; }
      .learneredu2 { display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; height:100%; }
      .learneredu2 img { width:2rem; height:2rem; object-fit:cover; border-radius:50%; margin-bottom:.3rem; }
      .learneredu2 p { margin:0; }
      .comment_txtarea { display:flex; gap:1rem; }
      .ANSYorN { display:flex; gap:1rem; align-items:center; }
      .submit-btn { background:#38B2AC; border:none; padding:.625rem 1.25rem; border-radius:.375rem; color:#fff; font-weight:bold; cursor:pointer; transition:background .3s, transform .2s; }
      .submit-btn:hover { background:#319A95; transform:scale(1.05); }
      .question-answers { padding-left:1rem; }
      .correct { background-color:#d1f7d1; font-weight:bold; border-radius:.3125rem; padding:.125rem .25rem; }
      .CommentCell { padding-top:0; font-size:1rem; }
      header { display:flex; justify-content:space-between; align-items:center; }
      header h2 { padding-left:.525rem; }
      header p { padding-right:.525rem; font-weight:bold; font-size:1.25rem; }
      .Logout { float:right; font-size:1.25rem; font-weight:bold; }
      .dashboardF { display:flex; gap:1.875rem; align-items:flex-start; }
      .welcomeF { flex:1; }
      .infoF { padding-top:.625rem; padding-left:.625rem; border:.125rem solid black; flex:1; background-color:#f5fafc; }
      .infoF p { padding-bottom:.625rem; }
      .pfp-F { width:6rem; height:6rem; float:right; padding-right:1.25rem; padding-bottom:1.45rem; }
      .quiz-headerAvailableQizzes { display:flex; justify-content:space-between; align-items:center; margin-bottom:.625rem; }
      .quiz-headerAvailableQizzes h3 { margin:0; }
      .Available_Quizzes-F, .recQS-F { border:.0625rem solid black; width:100%; min-width:31.25rem; }
      thead { background-color:#34495e; color:white; }
      th, td { border:.0625rem solid black; padding:.625rem; }
      td { color:#00224B; background-color:#f5fafc; }
      .Available_Quizzes-F td:nth-child(2), .Available_Quizzes-F td:nth-child(4) { text-align:center; vertical-align:middle; white-space:nowrap; }
      .Available_Quizzes-F td:nth-child(3) table { margin:0 auto; }
      .Available_Quizzes-F td:nth-child(3) td { text-align:center; padding:.25rem .5rem; }
      .Available_Quizzes-F th:nth-child(3), .Available_Quizzes-F th:nth-child(4),
      .Available_Quizzes-F td:nth-child(3), .Available_Quizzes-F td:nth-child(4) { text-align:center; vertical-align:middle; }
      .recQS-F th:nth-child(4), .recQS-F td:nth-child(4) { text-align:center; vertical-align:middle; white-space:nowrap; }
      textarea { width:15rem; height:2rem; resize:none; overflow-y:auto; }
      input[type="radio"] { vertical-align:middle; position:relative; top:-.1rem; }
    </style>
  </head>

  <body>
     <header>
        <div class="logo">
          <img src="images/logo.png" alt="LearnIT Logo">
          <h1>LearnIT</h1>
        </div>
        <nav>
          <a href="educator.php">Home</a>
        </nav>
     </header>

     <main class="card-container">
        <div class="Logout"><a href="logout.php">Log-out</a></div>
        <h2 class="EducatorH2">Educator</h2>

        <div class="dashboardF">
          <div class="welcomeF">
            <h4>Welcome <?php echo $firstNameSafe; ?></h4>
            <br>
            <p>Educator dashboard with quizzes and recommended questions.</p>
          </div>

          <br>

          <div class="infoF">
            <p>First Name : <?php echo $firstNameSafe; ?></p>
            <img src="<?php echo $photoPathSafe; ?>" alt="Profile Picture" class="pfp-F">
            <p>Last Name : <?php echo $lastNameSafe; ?></p>
            <p>Email : <?php echo $emailSafe; ?></p>
          </div>
        </div>

        <!-- ===== 6(c): Your Quizzes ===== -->
        <table class="Available_Quizzes-F">
          <div class="quiz-headerAvailableQizzes"><h3>Your Quizzes</h3></div>
          <colgroup>
            <col style="width: 40%;"><col style="width: 20%;"><col style="width: 20%;"><col style="width: 20%;">
          </colgroup>
          <thead>
            <tr>
              <th>Topic</th>
              <th>Number Of Questions</th>
              <th>Quiz Statistics</th>
              <th>Quiz Feedback</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($quizzes)): ?>
            <tr><td colspan="4" style="text-align:center;">You have no quizzes yet.</td></tr>
          <?php else: foreach ($quizzes as $q):
            $quizId   = (int)$q['quiz_id'];
            $topic    = htmlspecialchars($q['topic_name'] ?? 'Topic', ENT_QUOTES, 'UTF-8');
            $qsCount  = (int)($q['questions_count'] ?? 0);
            $takers   = (int)($q['takers_count'] ?? 0);
            $avgScore = $q['avg_score'] !== null ? round((float)$q['avg_score'], 1) : null;
            $fbCount  = (int)($q['feedback_count'] ?? 0);
            $avgRate  = $q['avg_rating'] !== null ? round((float)$q['avg_rating'], 1) : null;
          ?>
            <tr>
              <td><a href="quiz.php?quiz_id=<?= $quizId ?>"><?= $topic ?></a></td>
              <td><?= $qsCount ?> QS</td>
              <td>
                <?php if ($takers === 0): ?>
                  quiz not taken yet
                <?php else: ?>
                  <table style="width:100%; border-collapse:collapse;">
                    <tr>
                      <td><strong>Takers:</strong> <?= $takers ?></td>
                      <td><strong>Avg:</strong> <?= $avgScore ?>%</td>
                    </tr>
                  </table>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($fbCount === 0): ?>
                  no feedback yet
                <?php else: ?>
                  <p><strong>Average Rating:</strong> <?= $avgRate ?> / 5</p>
                  <a href="comments.php?quiz_id=<?= $quizId ?>">Comments</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>

        <br><br><br>

        <!-- ===== 6(d): Pending Recommended Questions (review UI only) ===== -->
        <table class="recQS-F">
          <h3>Questions Recommendations</h3>
          <colgroup>
            <col style="width: 20%;">
            <col style="width: 12%;">
            <col style="width: 43%;">
            <col style="width: 25%;">
          </colgroup>
          <thead>
            <tr>
              <th>Topic</th>
              <th>Learner</th>
              <th>Question</th>
              <th>Review</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($recs)): ?>
            <tr><td colspan="4" style="text-align:center;">No pending recommendations.</td></tr>
          <?php else: foreach ($recs as $r):
            $recId   = (int)$r['rec_id'];
            $topic   = htmlspecialchars($r['topic_name'], ENT_QUOTES,'UTF-8');
            $lfn     = htmlspecialchars(trim(($r['learner_first'] ?? '').' '.($r['learner_last'] ?? '')), ENT_QUOTES,'UTF-8');
            $lphoto  = trim($r['learner_photo']) !== '' ? 'images/'.$r['learner_photo'] : 'images/pfp.png';
            $lphotoS = htmlspecialchars($lphoto, ENT_QUOTES,'UTF-8');

            $qText   = htmlspecialchars($r['q_text'] ?? '', ENT_QUOTES,'UTF-8');
            $ansA    = htmlspecialchars($r['answerA'] ?? '', ENT_QUOTES,'UTF-8');
            $ansB    = htmlspecialchars($r['answerB'] ?? '', ENT_QUOTES,'UTF-8');
            $ansC    = htmlspecialchars($r['answerC'] ?? '', ENT_QUOTES,'UTF-8');
            $ansD    = htmlspecialchars($r['answerD'] ?? '', ENT_QUOTES,'UTF-8');
            $correct = strtoupper(trim($r['correct'] ?? ''));

            $qImg = $r['q_img'] ?? '';
            $imgPath = $qImg ? 'images/'.trim((string)$qImg) : '';
            $imgHtml = $imgPath ? '<div class="question-photo"><img src="'.htmlspecialchars($imgPath,ENT_QUOTES,'UTF-8').'" alt="figure" style="max-width:11.25rem;max-height:7.5rem;border-radius:.375rem;"></div>' : '';
          ?>
            <tr>
              <td><?= $topic ?></td>
              <td>
                <div class="learneredu2">
                  <img src="<?= $lphotoS ?>" alt="Learner">
                  <p><?= $lfn ?></p>
                </div>
              </td>
              <td>
                <?= $imgHtml ?>
                <div class="question-text" style="margin:.4rem 0; font-weight:600;"><?= $qText ?></div>
                <ol type="A" class="question-answers">
                  <li<?= $correct==='A'?' class="correct"':'' ?>><?= $ansA ?></li>
                  <li<?= $correct==='B'?' class="correct"':'' ?>><?= $ansB ?></li>
                  <li<?= $correct==='C'?' class="correct"':'' ?>><?= $ansC ?></li>
                  <li<?= $correct==='D'?' class="correct"':'' ?>><?= $ansD ?></li>
                </ol>
              </td>
              <td class="CommentCell">
                
                  
                <form action="educator_review.php" method="post" class="review-form">
                  <input type="hidden" name="rec_id" value="<?= $recId ?>">
                  <div class="comment_txtarea">
                    <label for="c<?= $recId ?>">Comment:</label>
                    <textarea id="c<?= $recId ?>" name="comment" rows="3" cols="22" placeholder="Optional note to learner"></textarea>
                  </div>
                  <br>
                  <div class="ANSYorN">
                    <label><input type="radio" name="decision" value="approved" checked> Approve</label>
                    <label><input type="radio" name="decision" value="disapproved"> Disapprove</label>
                  </div>
                  <br>
                  <button type="submit" class="btn submit-btn">Submit</button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
     </main>

     <footer>
        <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
     </footer>
  </body>
</html>
