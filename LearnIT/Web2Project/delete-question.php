<?php
require_once 'connect.php';
session_start();

if (!isset($_SESSION['id']) || $_SESSION['userType'] !== 'educator') {
    header("Location: index.php?error=unauthorized");
    exit;
}

$quizID = (int)($_GET['quizID'] ?? 0);
$qid = (int)($_GET['id'] ?? 0);

// get image file name
$res = mysqli_query($conn, "SELECT questionFigureFileName FROM quizquestion WHERE id=$qid AND quizID=$quizID");
if ($res && $row = mysqli_fetch_assoc($res)) {
    if (!empty($row['questionFigureFileName'])) {
        @unlink(__DIR__ . "/uploads/" . $row['questionFigureFileName']);
    }
}

mysqli_query($conn, "DELETE FROM quizquestion WHERE id=$qid AND quizID=$quizID");
header("Location: quiz.php?quizID=$quizID");
exit;
?>

