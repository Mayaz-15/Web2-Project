<?php
session_start();
require_once 'connect.php'; 


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}


$email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = trim($_POST['password'] ?? '');


if ($email === '' || $password === '') {
    header('Location: login.php?error=' . urlencode('Please enter email and password.'));
    exit;
}


$sql  = "SELECT id, firstName, lastName, emailAddress, password, photoFileName, userType
         FROM user
         WHERE emailAddress = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$res  = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    
    if (password_verify($password, $row['password'])) {

        
        $_SESSION['user_id']    = (int)$row['id'];
        $_SESSION['user_type']  = $row['userType'];      // 'learner' أو 'educator'
        $_SESSION['first_name'] = $row['firstName'];
        $_SESSION['last_name']  = $row['lastName'];
        $_SESSION['email']      = $row['emailAddress'];
        $_SESSION['photo']      = $row['photoFileName'];

        
        if ($_SESSION['user_type'] === 'learner') {
            header('Location: learner.php');
        } else {
            header('Location: educator.php');
        }
        $stmt->close();
        exit;
    }
}


$stmt->close();
header('Location: login.php?error=' . urlencode('Incorrect email or password.'));
exit;
