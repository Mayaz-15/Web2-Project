<?php
session_start();
require_once 'connect.php';

// لا يسمح بالدخول على الصفحة مباشرة
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: signup.php");
    exit;
}

// ✅ 1. تحديد نوع المستخدم
$userType = $_POST['userType'] ?? 'learner';

// ✅ 2. قراءة البيانات حسب النوع
if ($userType === 'learner') {
    $first = trim($_POST['firstName'] ?? '');
    $last  = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    $topics = [];
} else {
    $first = trim($_POST['firstNameEdu'] ?? '');
    $last  = trim($_POST['lastNameEdu'] ?? '');
    $email = trim($_POST['emailEdu'] ?? '');
    $pass  = trim($_POST['passwordEdu'] ?? '');
    $topics = $_POST['topics'] ?? [];
}

// ✅ 3. التحقق من المدخلات
if ($first === '' || $last === '' || $email === '' || $pass === '') {
    header("Location: signup.php?error=missing_fields");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: signup.php?error=invalid_email");
    exit;
}

// ✅ 4. التحقق من تكرار الإيميل
$check = $conn->prepare("SELECT id FROM user WHERE emailAddress = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    header("Location: signup.php?error=email_exists");
    exit;
}
$check->close();

// ✅ 5. معالجة الصورة
$photoFile = "default_profile.png";

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
    $uniqueName = time() . "_" . basename($_FILES['photo']['name']);
    $uploadDir = "uploads/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $target = $uploadDir . $uniqueName;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
        $photoFile = $uniqueName;
    }
}

// ✅ 6. تشفير كلمة المرور
$hashedPass = password_hash($pass, PASSWORD_DEFAULT);

// ✅ 7. إدخال المستخدم
$insert = $conn->prepare("
    INSERT INTO user (firstName, lastName, emailAddress, password, photoFileName, userType)
    VALUES (?, ?, ?, ?, ?, ?)
");
$insert->bind_param("ssssss", $first, $last, $email, $hashedPass, $photoFile, $userType);
$insert->execute();
$newUserID = $insert->insert_id;
$insert->close();

// ✅ 8. حفظ بيانات المستخدم في السيشن
$_SESSION['user_id'] = $newUserID;
$_SESSION['user_type'] = $userType;

// ✅ 9. إذا Educator → إنشاء Quiz
if ($userType === "educator" && !empty($topics)) {
    $quiz = $conn->prepare("INSERT INTO quiz (educatorID, topicID) VALUES (?, ?)");
    foreach ($topics as $topicID) {
        $quiz->bind_param("ii", $newUserID, $topicID);
        $quiz->execute();
    }
    $quiz->close();
}

// ✅ 10. إعادة التوجيه
if ($userType === "learner") {
    header("Location: learner.php");
} else {
    header("Location: educator.php");
}
exit;
?>
