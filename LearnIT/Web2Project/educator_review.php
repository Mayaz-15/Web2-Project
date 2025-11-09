<?php
// ===== Processor for reviewing RecommendedQuestion (part 6d) =====
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/connect.php';  // defines $conn (PDO or MySQLi)

// --- Auth ---
if (empty($_SESSION['user_id']) || empty($_SESSION['user_type'])) {
  header('Location: index.php?error=login_required'); exit;
}
if ($_SESSION['user_type'] !== 'educator') {
  header('Location: login.php?error=not_educator'); exit;
}
$userId = (int)$_SESSION['user_id'];

// Accept only POST with required fields
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: educator.php'); exit;
}

$recId    = (int)($_POST['rec_id'] ?? 0);
$decision = strtolower(trim($_POST['decision'] ?? ''));
$comment  = trim($_POST['comment'] ?? '');

if ($recId <= 0 || !in_array($decision, ['approved','disapproved'], true)) {
  header('Location: educator.php?msg=invalid_input'); exit;
}

// 1) Load the recommendation and ensure it belongs to this educator
if ($conn instanceof PDO) {
  $stmt = $conn->prepare(
    "SELECT rq.*, q.educatorID
     FROM RecommendedQuestion rq
     JOIN Quiz q ON q.id = rq.quizID
     WHERE rq.id = ? LIMIT 1"
  );
  $stmt->execute([$recId]);
  $rec = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} else {
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
  $stmt = $conn->prepare(
    "SELECT rq.*, q.educatorID
     FROM RecommendedQuestion rq
     JOIN Quiz q ON q.id = rq.quizID
     WHERE rq.id = ? LIMIT 1"
  );
  $stmt->bind_param('i', $recId);
  $stmt->execute();
  $rec = $stmt->get_result()->fetch_assoc();
}

if (!$rec || (int)$rec['educatorID'] !== $userId) {
  header('Location: educator.php?msg=not_allowed'); exit;
}

// 2) Update status + comments
if ($conn instanceof PDO) {
  $u = $conn->prepare("UPDATE RecommendedQuestion SET status=?, comments=? WHERE id=?");
  $u->execute([$decision, $comment, $recId]);
} else {
  $u = $conn->prepare("UPDATE RecommendedQuestion SET status=?, comments=? WHERE id=?");
  $u->bind_param('ssi', $decision, $comment, $recId);
  $u->execute();
}

// 3) If approved, add to QuizQuestion
if ($decision === 'approved') {
  $quizID  = (int)$rec['quizID'];
  $qText   = $rec['question'];
  $qFigure = $rec['questionFigureFileName']; // nullable
  $aA = $rec['answerA']; $aB = $rec['answerB']; $aC = $rec['answerC']; $aD = $rec['answerD'];
  $correct = strtoupper(trim($rec['correctAnswer']));

  if ($conn instanceof PDO) {
    $ins = $conn->prepare(
      "INSERT INTO QuizQuestion
       (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $ins->execute([$quizID, $qText, $qFigure, $aA, $aB, $aC, $aD, $correct]);
  } else {
    $ins = $conn->prepare(
      "INSERT INTO QuizQuestion
       (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $ins->bind_param('isssssss', $quizID, $qText, $qFigure, $aA, $aB, $aC, $aD, $correct);
    $ins->execute();
  }
}

// PRG redirect back to the dashboard
header('Location: educator.php?msg=review_saved');
exit;