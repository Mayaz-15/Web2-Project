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

$qid    = (int)($_POST['qid'] ?? 0);
$quizID = (int)($_POST['quizID'] ?? 0);

$qtext   = trim($_POST['qtext'] ?? '');
$c1      = trim($_POST['c1'] ?? '');
$c2      = trim($_POST['c2'] ?? '');
$c3      = trim($_POST['c3'] ?? '');
$c4      = trim($_POST['c4'] ?? '');
$correct = $_POST['correct'] ?? '';

if ($qid <= 0 || $quizID <= 0) {
    header("Location: educator.php?error=invalid_question");
    exit;
}

// Load current image name from DB
$sql = "SELECT questionFigureFileName FROM quizquestion WHERE id = ? AND quizID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $qid, $quizID);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (!$res || !mysqli_num_rows($res)) {
    header("Location: educator.php?error=question_not_found");
    exit;
}
$row = mysqli_fetch_assoc($res);
$oldImgName = $row['questionFigureFileName'];

// Basic validation
$errors = [];
if ($qtext === '') $errors[] = "Question text is required.";
if ($c1    === '') $errors[] = "Choice A is required.";
if ($c2    === '') $errors[] = "Choice B is required.";
if ($c3    === '') $errors[] = "Choice C is required.";
if ($c4    === '') $errors[] = "Choice D is required.";
if (!in_array($correct, ['A','B','C','D'], true)) $errors[] = "Choose a correct answer (A–D).";

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

if ($errors) {
    $msg = urlencode(implode(' | ', $errors));
    header("Location: edit-question.php?id={$qid}&error={$msg}");
    exit;
}

// Update DB
$upd = "UPDATE quizquestion
        SET question=?, questionFigureFileName=?, answerA=?, answerB=?, answerC=?, answerD=?, correctAnswer=?
        WHERE id=?";
$ust = mysqli_prepare($conn, $upd);
mysqli_stmt_bind_param(
  $ust, "sssssssi",
  $qtext, $newImgName, $c1, $c2, $c3, $c4, $correct, $qid
);
mysqli_stmt_execute($ust);

// Redirect back to quiz page
header("Location: quiz.php?quizID=".$quizID);
exit;
?>