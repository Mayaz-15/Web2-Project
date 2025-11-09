<?php
session_start();
include 'connect.php';

if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

if (!isset($_SESSION['id']) || !isset($_SESSION['userType']) || $_SESSION['userType'] !== 'learner') {
    header("Location: index.php");
    exit();
}

$learnerID = (int) $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $topicID = intval($_POST['topic']);
    $educatorID = intval($_POST['prof']);
    $question = trim($_POST['Q']);
    $answerA = trim($_POST['a']);
    $answerB = trim($_POST['b']);
    $answerC = trim($_POST['c']);
    $answerD = trim($_POST['d']);
    $correct = strtoupper(trim($_POST['rightAns']));
    $fileName = null;

    if (!empty($_FILES['pic']['name'])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = basename($_FILES['pic']['name']);
        $targetPath = $uploadDir . $fileName;
        move_uploaded_file($_FILES['pic']['tmp_name'], $targetPath);
    }

    $quizQuery = $conn->prepare("SELECT id FROM quiz WHERE topicID=? AND educatorID=? LIMIT 1");
    $quizQuery->bind_param("ii", $topicID, $educatorID);
    $quizQuery->execute();
    $quizQuery->bind_result($quizID);
    $quizQuery->fetch();
    $quizQuery->close();

    if (!$quizID) {
        echo "<script>alert('No quiz found for that topic and educator.'); window.history.back();</script>";
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO recommendedquestion 
        (quizID, learnerID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param("iisssssss", $quizID, $learnerID, $question, $fileName, $answerA, $answerB, $answerC, $answerD, $correct);
    $stmt->execute();
    $stmt->close();

    header("Location: learner.php");
    exit();
} else {
    echo "Invalid request.";
}
?>