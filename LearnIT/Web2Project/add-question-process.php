<?php
require_once 'connect.php';
session_start();

// Map login session keys if needed
if (isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    $_SESSION['id'] = $_SESSION['user_id'];
}
if (isset($_SESSION['user_type']) && !isset($_SESSION['userType'])) {
    $_SESSION['userType'] = $_SESSION['user_type'];
}

// Security: must be logged-in educator
if (!isset($_SESSION['id']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'educator') {
    header("Location: index.php?error=unauthorized");
    exit;
}

$quizID = (int)($_POST['quizID'] ?? 0);
$qtext  = trim($_POST['qtext'] ?? '');
$c1     = trim($_POST['c1'] ?? '');
$c2     = trim($_POST['c2'] ?? '');
$c3     = trim($_POST['c3'] ?? '');
$c4     = trim($_POST['c4'] ?? '');
$correct = $_POST['correct'] ?? '';

if ($quizID <= 0) {
    header("Location: educator.php?error=invalid_quiz");
    exit;
}

// Basic validation (similar to your old one)
$errors = [];
if ($qtext === '') $errors[] = "Question text is required.";
if ($c1    === '') $errors[] = "Choice A is required.";
if ($c2    === '') $errors[] = "Choice B is required.";
if ($c3    === '') $errors[] = "Choice C is required.";
if ($c4    === '') $errors[] = "Choice D is required.";
if (!in_array($correct, ['A','B','C','D'], true)) $errors[] = "Choose the correct answer (A–D).";

// Optional image
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

if ($errors) {
    $msg = urlencode(implode(' | ', $errors));
    header("Location: add-question.php?quizID={$quizID}&error={$msg}");
    exit;
}

// Insert into DB
$sql = "INSERT INTO quizquestion
  (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param(
  $stmt, "isssssss",
  $quizID, $qtext, $figName, $c1, $c2, $c3, $c4, $correct
);
mysqli_stmt_execute($stmt);

// Redirect back to quiz page
header("Location: quiz.php?quizID=".$quizID);
exit;
?>