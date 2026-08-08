<?php
session_start();
require_once 'connect.php';


function checkLoginAttempts($email) {
    $attemptsFile = sys_get_temp_dir() . '/login_attempts_' . md5($email);
    if (file_exists($attemptsFile)) {
        $data = json_decode(file_get_contents($attemptsFile), true);
        if ($data['count'] >= 5 && (time() - $data['last_attempt'] < 900)) {
            return false; 
        }
    }
    return true;
}

function recordLoginAttempt($email) {
    $attemptsFile = sys_get_temp_dir() . '/login_attempts_' . md5($email);
    $data = ['count' => 1, 'last_attempt' => time()];
    if (file_exists($attemptsFile)) {
        $data = json_decode(file_get_contents($attemptsFile), true);
        $data['count']++;
        $data['last_attempt'] = time();
    }
    file_put_contents($attemptsFile, json_encode($data));
}


if (isset($_POST['signUp'])) {
    $firstName = sanitize($_POST['fName']);
    $surName = sanitize($_POST['sName']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    
    $errors = [];
    if (strlen($firstName) < 2) $errors[] = "First name must be at least 2 characters.";
    if (strlen($surName) < 2) $errors[] = "Surname must be at least 2 characters.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "Password must contain at least one uppercase letter.";
    if (!preg_match('/[a-z]/', $password)) $errors[] = "Password must contain at least one lowercase letter.";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "Password must contain at least one number.";
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: index.php#signup");
        exit();
    }
    
    
    $stmt = $conn->prepare("SELECT id FROM users1 WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already registered. Please login.";
        header("Location: index.php#signin");
        exit();
    }
    
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    
    
    $stmt = $conn->prepare("INSERT INTO users1 (firstName, surName, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $firstName, $surName, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Account created successfully! Please login.";
        header("Location: index.php#signin");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: index.php#signup");
        exit();
    }
}


if (isset($_POST['signIn'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: index.php#signin");
        exit();
    }
    
    
    if (!checkLoginAttempts($email)) {
        $_SESSION['error'] = "Too many failed attempts. Please try again later.";
        header("Location: index.php#signin");
        exit();
    }
    
    
    $stmt = $conn->prepare("SELECT id, firstName, email, password FROM users1 WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            
            $attemptsFile = sys_get_temp_dir() . '/login_attempts_' . md5($email);
            if (file_exists($attemptsFile)) unlink($attemptsFile);
            
            session_regenerate_id(true);
            $_SESSION['id'] = $row['id'];
            $_SESSION['firstName'] = $row['firstName'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();
            
            header("Location: dashboard.php");
            exit();
        }
    }
    
    
    recordLoginAttempt($email);
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: index.php#signin");
    exit();
}
?>