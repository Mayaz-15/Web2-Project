<?php
// ajax/learner_quizzes.php

session_start();
header('Content-Type: application/json; charset=UTF-8');

// حراسة: لازم مستخدم داخل و نوعه learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'learner') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

// الاتصال بقاعدة البيانات
require_once 'connect.php'; // نفس connect.php اللي تستخدمونه في الصفحات الثانية

if (!$conn) {
    http_response_code(500);
    echo json_encode([]);
    exit;
}

// قراءة topicID المرسل من AJAX
$topicID = isset($_POST['topicID']) ? trim($_POST['topicID']) : '';
$rows    = [];

// SQL نفس اللي في learner.php بالضبط
if ($topicID !== '' && ctype_digit($topicID)) {
    // فلترة حسب توبك معيّن
    $sql = "
        SELECT 
            q.id AS quizID,
            t.topicName,
            u.firstName,
            u.lastName,
            COALESCE(NULLIF(u.photoFileName, ''), 'default.png') AS educatorPhoto,
            COUNT(qq.id) AS questionCount
        FROM quiz q
        JOIN user u ON q.educatorID = u.id
        JOIN topic t ON q.topicID = t.id
        LEFT JOIN quizquestion qq ON q.id = qq.quizID
        WHERE q.topicID = ?
        GROUP BY q.id, t.topicName, u.firstName, u.lastName, u.photoFileName
        ORDER BY t.topicName, q.id
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $topicID);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
    } else {
        $res = false;
    }
} else {
    // كل الكويزز (كل التوبيكات)
    $sql = "
        SELECT 
            q.id AS quizID,
            t.topicName,
            u.firstName,
            u.lastName,
            COALESCE(NULLIF(u.photoFileName, ''), 'default.png') AS educatorPhoto,
            COUNT(qq.id) AS questionCount
        FROM quiz q
        JOIN user u ON q.educatorID = u.id
        JOIN topic t ON q.topicID = t.id
        LEFT JOIN quizquestion qq ON q.id = qq.quizID
        GROUP BY q.id, t.topicName, u.firstName, u.lastName, u.photoFileName
        ORDER BY t.topicName, q.id
    ";

    $res = mysqli_query($conn, $sql);
}

// تحويل النتيجة لـ JSON
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $rows[] = [
            'quizID'        => (int)$r['quizID'],
            'topicName'     => (string)$r['topicName'],
            'educatorFirst' => (string)$r['firstName'],
            'educatorLast'  => (string)$r['lastName'],
            'educatorPhoto' => (string)$r['educatorPhoto'],
            'questionCount' => (int)$r['questionCount'],
        ];
    }
}

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
