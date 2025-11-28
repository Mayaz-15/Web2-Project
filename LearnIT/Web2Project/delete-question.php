<?php
require_once 'connect.php';
session_start();

// Map login.php session keys (same pattern as other pages)
if (isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    $_SESSION['id'] = $_SESSION['user_id'];
}
if (isset($_SESSION['user_type']) && !isset($_SESSION['userType'])) {
    $_SESSION['userType'] = $_SESSION['user_type'];
}

// Always return simple text
header('Content-Type: text/plain');

// Check authorization: must be logged in educator
if (!isset($_SESSION['id']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'educator') {
    echo '0';
    exit;
}

// Read inputs from POST (AJAX)
$quizID = isset($_POST['quizID']) ? (int)$_POST['quizID'] : 0;
$qid    = isset($_POST['id'])     ? (int)$_POST['id']     : 0;

if ($quizID <= 0 || $qid <= 0) {
    echo '0';
    exit;
}

// 1) Get image file name to delete file from uploads
$sql  = "SELECT questionFigureFileName FROM quizquestion WHERE id=? AND quizID=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $qid, $quizID);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if ($res && $row = mysqli_fetch_assoc($res)) {
    if (!empty($row['questionFigureFileName'])) {
        $path = __DIR__ . "/uploads/" . $row['questionFigureFileName'];
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
mysqli_stmt_close($stmt);

// 2) Delete the DB row
$del = mysqli_prepare($conn, "DELETE FROM quizquestion WHERE id=? AND quizID=?");
mysqli_stmt_bind_param($del, "ii", $qid, $quizID);
mysqli_stmt_execute($del);

if (mysqli_stmt_affected_rows($del) > 0) {
    echo '1';   // success for AJAX
} else {
    echo '0';   // failed
}

mysqli_stmt_close($del);

?>
