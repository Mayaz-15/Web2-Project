<?php
session_start();
include "db.php";   // الاتصال بقاعدة البيانات


// =======================================================
// (1) تحديد نوع المستخدم (Learner OR Educator)
// =======================================================
$userType = $_POST['userType'];

if ($userType === "learner") {

    $first = $_POST['firstName'];
    $last  = $_POST['lastName'];
    $email = $_POST['email'];
    $pass  = $_POST['password'];

} else {

    $first = $_POST['firstNameEdu'];
    $last  = $_POST['lastNameEdu'];
    $email = $_POST['emailEdu'];
    $pass  = $_POST['passwordEdu'];
}


// =======================================================
// (2) معالجة الصورة (اختيارية) + صورة افتراضية
// =======================================================
$photoFile = "default_profile.png";  // الصورة الافتراضية

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {

    // اسم فريد
    $uniqueName = time() . "_" . $_FILES['photo']['name'];

    // رفع الملف
    move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $uniqueName);

    // حفظ اسم الصورة
    $photoFile = $uniqueName;
}


// =======================================================
// (3) التحقق من الإيميل مكرر أم لا
// =======================================================
$stmt = $pdo->prepare("SELECT * FROM user WHERE emailAddress=?");
$stmt->execute([$email]);

if ($stmt->rowCount() > 0) {
    header("Location: signup.php?error=email_exists");
    exit;
}


// =======================================================
// (4) تشفير كلمة المرور
// =======================================================
$hashedPass = password_hash($pass, PASSWORD_DEFAULT);


// =======================================================
// (5) إضافة المستخدم في جدول User
// =======================================================
$stmt = $pdo->prepare("
    INSERT INTO user (firstName, lastName, emailAddress, password, photoFileName, userType)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->execute([$first, $last, $email, $hashedPass, $photoFile, $userType]);

// آخر ID تم تسجيله
$newUserID = $pdo->lastInsertId();


// =======================================================
// (6) تخزين معلومات المستخدم في الـ SESSION
// =======================================================
$_SESSION['userID'] = $newUserID;
$_SESSION['userType'] = $userType;


// =======================================================
// (7) إذا كان Educator → إنشاء quiz لكل topic مختار
// =======================================================
if ($userType === "educator" && isset($_POST['topics'])) {
    
    foreach ($_POST['topics'] as $topicID) {

        $stmt = $pdo->prepare("
            INSERT INTO quiz (educatorID, topicID)
            VALUES (?, ?)
        ");

        $stmt->execute([$newUserID, $topicID]);
    }
}


// =======================================================
// (8) إعادة التوجيه للصفحة المناسبة
// =======================================================
if ($userType === "learner") {
    header("Location: learner_homepage.php");
} else {
    header("Location: educator_homepage.php");
}

exit;

?>
