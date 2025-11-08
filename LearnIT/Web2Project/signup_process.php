<?php
session_start();
require_once 'connect.php'; // الاتصال بقاعدة البيانات (MySQLi)

// تأكد أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.php');
    exit;
}

// (1) تحديد نوع المستخدم (Learner OR Educator)
$userType = $_POST['userType'] ?? 'learner';

if ($userType === 'learner') {
    $first = trim($_POST['firstName'] ?? '');
    $last = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    $topics = []; // learner ما يختار توبكس
} else {
    $first = trim($_POST['firstNameEdu'] ?? '');
    $last = trim($_POST['lastNameEdu'] ?? '');
    $email = trim($_POST['emailEdu'] ?? '');
    $pass = trim($_POST['passwordEdu'] ?? '');
    $topics = isset($_POST['topics']) ? $_POST['topics'] : [];
}

// تحقق من القيم
if ($first === ''||$email === '' || $pass === '') {
    header('Location: signup.php?error=missing_fields');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: signup.php?error=invalid_email');
    exit;
}

// (2) معالجة الصورة (افتراضية أو مرفوعة)
$photoFile = 'default_profile.png';
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
    $uniqueName = time() . "_" . basename($_FILES['photo']['name']);
    $targetDir = 'uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }
    $targetFile = $targetDir . $uniqueName;
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
        $photoFile = $uniqueName;
    }
}

// (3) التحقق من الإيميل (هل موجود مسبقاً؟)
$check = $conn->prepare("SELECT id FROM user WHERE emailAddress = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();
if ($result->num_rows > 0) {
    header("Location: signup.php?error=email_exists");
    exit;
}
$check->close();

// (4) تشفير كلمة المرور
$hashedPass = password_hash($pass, PASSWORD_DEFAULT);

// (5) إدخال المستخدم الجديد
$insert = $conn->prepare("INSERT INTO user (firstName, lastName, emailAddress, password, photoFileName, userType)
                          VALUES (?, ?, ?, ?, ?, ?)");
$insert->bind_param("ssssss", $first, $last, $email, $hashedPass, $photoFile, $userType);
$insert->execute();
$newUserID = $insert->insert_id;
$insert->close();

// (6) تخزين معلومات المستخدم في السيشن
$_SESSION['user_id'] = $newUserID;
$_SESSION['user_type'] = $userType;

// (7) إذا كان Educator أنشئ Quiz لكل Topic
if ($userType === 'educator' && !empty($topics)) {
    $quizInsert = $conn->prepare("INSERT INTO quiz (educatorID, topicID) VALUES (?, ?)");
    foreach ($topics as $topicID) {
        $quizInsert->bind_param("ii", $newUserID, $topicID);
        $quizInsert->execute();
    }
    $quizInsert->close();
}

// (8) التوجيه حسب نوع المستخدم
if ($userType === 'learner') {
    header("Location: learner_homepage.php");
} else {
    header("Location: educator_homepage.php");
}
exit;
?>
