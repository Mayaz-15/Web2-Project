<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

if ($_SESSION['user_type'] !== 'learner') {
    
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: login.php?error=not_learner'); 
    exit;
}


if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'], $_SESSION['user_type'])) {
    header('Location: index.php?error=notLoggedIn');
    exit();
}
if (strtolower(trim((string)$_SESSION['user_type'])) !== 'learner') {
    header('Location: index.php?error=wrongRole');
    exit();
}

require_once 'connect.php';
if (!$conn) { die('Database connection failed.'); }


/* (b) Fetch learner info */
$userId = (int) $_SESSION['user_id'];
$sql = "SELECT firstName, lastName, emailAddress,
               COALESCE(NULLIF(photoFileName,''),'default.png') AS photoFileName
        FROM user WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (!$res || mysqli_num_rows($res) === 0) {
    header('Location: index.php?error=userMissing');
    exit();
}
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$first = e($user['firstName']);
$last  = e($user['lastName']);
$email = e($user['emailAddress']);
$photo = 'images/' . e($user['photoFileName']);

/* (c) Topics dropdown */
$topics = [];
$tq = mysqli_query($conn, "SELECT id, topicName FROM topic ORDER BY topicName");
while ($row = mysqli_fetch_assoc($tq)) $topics[] = $row;
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
$selectedTopicId = $isPost ? trim((string)($_POST['topicID'] ?? '')) : '';

/* (d,e) Quizzes */
if ($isPost && $selectedTopicId !== '') {
    $quizSql = "
        SELECT q.id AS quizID,
               t.topicName,
               u.firstName, u.lastName,
               COALESCE(NULLIF(u.photoFileName,''),'default.png') AS educatorPhoto,
               COUNT(qq.id) AS questionCount
        FROM quiz q
        JOIN user u ON q.educatorID = u.id
        JOIN topic t ON q.topicID = t.id
        LEFT JOIN quizquestion qq ON q.id = qq.quizID
        WHERE q.topicID = ?
        GROUP BY q.id, t.topicName, u.firstName, u.lastName, u.photoFileName
        ORDER BY t.topicName, q.id";
    $stmt = mysqli_prepare($conn, $quizSql);
    mysqli_stmt_bind_param($stmt, "i", $selectedTopicId);
    mysqli_stmt_execute($stmt);
    $quizzes = mysqli_stmt_get_result($stmt);
} else {
    $quizSql = "
        SELECT q.id AS quizID,
               t.topicName,
               u.firstName, u.lastName,
               COALESCE(NULLIF(u.photoFileName,''),'default.png') AS educatorPhoto,
               COUNT(qq.id) AS questionCount
        FROM quiz q
        JOIN user u ON q.educatorID = u.id
        JOIN topic t ON q.topicID = t.id
        LEFT JOIN quizquestion qq ON q.id = qq.quizID
        GROUP BY q.id, t.topicName, u.firstName, u.lastName, u.photoFileName
        ORDER BY t.topicName, q.id";
    $quizzes = mysqli_query($conn, $quizSql);
}

/* (f) Recommended questions */
$recSql = "
    SELECT rq.id, rq.question, rq.questionFigureFileName,
           rq.answerA, rq.answerB, rq.answerC, rq.answerD, rq.correctAnswer,
           rq.status, rq.comments,
           t.topicName,
           u.firstName AS educatorFirst, u.lastName AS educatorLast,
           COALESCE(NULLIF(u.photoFileName,''),'default.png') AS educatorPhoto
    FROM recommendedquestion rq
    JOIN quiz q ON rq.quizID = q.id
    JOIN topic t ON q.topicID = t.id
    JOIN user u ON q.educatorID = u.id
    WHERE rq.learnerID = ?
    ORDER BY rq.id DESC";
$stmt2 = mysqli_prepare($conn, $recSql);
mysqli_stmt_bind_param($stmt2, "i", $userId);
mysqli_stmt_execute($stmt2);
$recRes = mysqli_stmt_get_result($stmt2);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Learner Homepage</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .learneredu, .learneredu2 { display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; }
    .learneredu img, .learneredu2 img { border-radius:50%; object-fit:cover; }
    .learneredu img { width:3rem; height:3rem; }
    .learneredu2 img { width:2rem; height:2rem; }
    .question-answers { padding-left:1rem; }
    .correct { background:#c8f7c5; font-weight:bold; border-radius:.25rem; padding:.1rem .3rem; }
    thead { background-color:#34495e; color:white; }
    th, td { border:1px solid black; padding:.6rem; vertical-align:top; }
    td{background-color: #f5fafc;}
    .Available_Quizzes-F, .recQS-F { width:100%; border-collapse:collapse; margin-top:1rem; }
    .RECQS { display:flex; justify-content:space-between; align-items:center; margin-top:2rem; margin-bottom:.5rem; }
    .filter { padding:.3rem .8rem; border-radius:.3rem; }
    .pfp-F { width:6rem; height:6rem; border-radius:50%; object-fit:cover; float:right; margin-right:1rem; }
  </style>
</head>

<body>
<header>
  <div class="logo">
    <img src="images/logo.png" alt="LearnIT Logo" style="width:50px; vertical-align:middle;">
    <h1 style="display:inline-block; margin-left:.5rem;">LearnIT</h1>
  </div>
  <nav><a href="learner.php">Home</a></nav>
</header>

<main class="card-container">
  <div style="text-align:right;"><a href="logout.php" class="filter">Log-out</a></div>
  <h2 class="learnerh2">Learner</h2>

  <div class="dashboardF" style="display:flex; gap:2rem;">
    <div class="welcomeF">
      <h4>Welcome <?= $first; ?></h4>
      <p>Learner dashboard with available quizzes and recommended questions.</p>
    </div>
    <div class="infoF" style="flex:1; border:1px solid #000; background:#f5fafc; padding:1rem;">
      <img src="<?= $photo; ?>" alt="Profile Picture" class="pfp-F">
      <p>First Name : <?= $first; ?></p>
      <p>Last Name : <?= $last; ?></p>
      <p>Email : <?= $email; ?></p>
    </div>
  </div>

  <!-- (c,d,e) Quizzes -->
  <div class="quiz-headerAvailableQizzes" style="display:flex; justify-content:space-between; align-items:center; margin-top:2rem;">
    <h3>All Available Quizzes</h3>
   
 
<select name="topicID" id="topicDD">
  <option value="">All topics</option>
  <?php foreach ($topics as $t): ?>
    <option value="<?= (int)$t['id'] ?>" <?= $t['id'] == $selectedTopicId ? 'selected' : '' ?>>
      <?= e($t['topicName']) ?>
    </option>
  <?php endforeach; ?>
</select>

  </div>

  
<table class="Available_Quizzes-F">
  <thead>
    <tr><th>Topic</th><th>Educator</th><th>Number of Questions</th><th></th></tr>
  </thead>
  <tbody id="quizzesBody"></tbody>
</table>

  <!-- (f) Recommended Questions -->
  <div class="RECQS">
    <h3>Recommend Questions</h3>
    <a href="recommend-question.php" id="recqslink">Recommend a Question</a>
  </div>
  <table class="recQS-F">
    <thead>
      <tr><th>Topic</th><th>Educator</th><th>Question</th><th>Status</th><th>Comments</th></tr>
    </thead>
    <tbody>
      <?php if ($recRes && mysqli_num_rows($recRes) > 0): ?>
        <?php while ($r = mysqli_fetch_assoc($recRes)): ?>
          <?php
            $topic = e($r['topicName']);
            $eduP = 'images/' . e($r['educatorPhoto']);
            $eduN = e($r['educatorFirst']);
            $qText = e($r['question']);
            $fig = trim($r['questionFigureFileName']);
            $A = e($r['answerA']); $B = e($r['answerB']); $C = e($r['answerC']); $D = e($r['answerD']);
            $correct = e($r['correctAnswer']);
          ?>
          <tr>
            <td><?= $topic ?></td>
            <td><div class="learneredu"><img src="<?= $eduP ?>" alt="Educator"><p><?= $eduN ?></p></div></td>
            <td>
              <?php if ($fig): ?><div class="question-photo"><img src="images/<?= e($fig) ?>" alt="Figure" style="width:90px;"></div><?php endif; ?>
              <div class="question-text"><?= $qText ?></div>
              <ol type="A" class="question-answers">
                <li<?= $correct=='A'?' class="correct"':'' ?>><?= $A ?></li>
                <li<?= $correct=='B'?' class="correct"':'' ?>><?= $B ?></li>
                <li<?= $correct=='C'?' class="correct"':'' ?>><?= $C ?></li>
                <li<?= $correct=='D'?' class="correct"':'' ?>><?= $D ?></li>
              </ol>
            </td>
            <td><?= e(ucfirst($r['status'])) ?></td>
            <td><?= e($r['comments']) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?><tr><td colspan="5">No recommended questions yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</main>

<footer>
  <p>&copy; 2025 LearnIT | Empowering Tech Learning</p>
</footer>
    
 <!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
(function(){
  const select = document.getElementById('topicDD');
  const tbody  = document.getElementById('quizzesBody');

  async function loadQuizzes() {
    
    const fd = new FormData();
    fd.append('topicID', select.value || '');

    try {
     const res = await fetch('learner_quizzes.php', {
    method: 'POST',
    body: fd,
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    },
    credentials: 'same-origin' 
});


      if (!res.ok) {
        tbody.innerHTML = `<tr><td colspan="4">Cannot load quizzes (code ${res.status}).</td></tr>`;
        return;
      }

      const data = await res.json();
      
      if (!Array.isArray(data) || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4">No quizzes found.</td></tr>`;
        return;
      }

    
      tbody.innerHTML = data.map(r => {
        const topic   = escapeHtml(r.topicName);
        const fname   = escapeHtml(r.educatorFirst);
        const lname   = escapeHtml(r.educatorLast);
        const photo   = 'images/' + (r.educatorPhoto || 'default.png');
        const count   = Number(r.questionCount) || 0;
        const qid     = Number(r.quizID) || 0;

        return `
          <tr>
            <td>${topic}</td>
            <td>
              <div class="learneredu2">
                <img src="${photo}" alt="Educator" />
                <p>${fname} ${lname}</p>
              </div>
            </td>
            <td>${count} Qs</td>
            <td>${count > 0 ? `<a href="quiz.php?quizID=${qid}">Take Quiz</a>` : `<span style="opacity:.6">No questions</span>`}</td>
          </tr>`;
      }).join('');
    } catch (e) {
      tbody.innerHTML = `<tr><td colspan="4">Network error.</td></tr>`;
      console.error(e);
    }
  }

  function escapeHtml(s){
    return String(s ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'",'&#039;');
  }

  select.addEventListener('change', loadQuizzes);
  document.addEventListener('DOMContentLoaded', loadQuizzes);
})();
</script>

    
    
</body>
</html>
