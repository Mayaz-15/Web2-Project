<?php
session_start();
include 'connect.php';

if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

if (!isset($_SESSION['id']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'learner') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quizID'], $_POST['rating'], $_POST['comments'])) {
    $quiz_id = intval($_POST['quizID']);
    $rating = intval($_POST['rating']);
    $comments = trim($_POST['comments']);

    $stmt = $conn->prepare("INSERT INTO quizfeedback (quizID, rating, comments) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iis", $quiz_id, $rating, $comments);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: learner.php");
    exit();
} else {
    echo "Invalid feedback submission.";
}
?>