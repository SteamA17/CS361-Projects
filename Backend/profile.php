<?php
session_start();
require_once 'connect.php';

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_destroy();
    header("Location: index.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();

$userId = $_SESSION['id'];
$message = '';
$messageType = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $firstName = sanitize($_POST['firstName']);
    $surName = sanitize($_POST['surName']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Validate
    $errors = [];
    if (strlen($firstName) < 2) $errors[] = "First name must be at least 2 characters.";
    if (strlen($surName) < 2) $errors[] = "Surname must be at least 2 characters.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    
    if (!empty($errors)) {
        $message = implode("<br>", $errors);
        $messageType = 'error';
    } else {
        // Check if email is taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $message = "Email already in use by another account.";
            $messageType = 'error';
        } else {
            $stmt = $conn->prepare("UPDATE users SET firstName = ?, surName = ?, email = ? WHERE id = ?");
            $stmt->bind_param("sssi", $firstName, $surName, $email, $userId);
            if ($stmt->execute()) {
                $_SESSION['firstName'] = $firstName;
                $_SESSION['email'] = $email;
                $message = "Profile updated successfully!";
                $messageType = 'success';
            } else {
                $message = "Update failed. Please try again.";
                $messageType = 'error';
            }
        }
    }
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();


$stats = [];
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM downloads WHERE uploaded_by = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stats['uploads'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_downloads WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stats['downloads'] = $stmt->get_result()->fetch_assoc()['total'];


if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL - Profile</title>
    <link rel="stylesheet" href="../Frontend/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .profile-container {
            max-width: 650px;
            margin: 0 auto;
            background: #fff;
            padding: 35px 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .profile-header .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3892ce, #2d7bb3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 40px;
            color: white;
        }
        
        .profile-header h2 {
            color: #1a1a2e;
            margin: 0;
            font-size: 22px;
            font-family: 'Poppins', sans-serif;
        }
        
        .profile-header p {
            color: #6c757d;
            margin: 5px 0 0;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }
        
        /* Form Layout */
        .form-row {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
            padding: 8px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .form-row:last-of-type {
            border-bottom: none;
        }
        
        .form-row label {
            min-width: 120px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            flex-shrink: 0;
        }
        
        .form-row label i {
            width: 20px;
            color: #3892ce;
            margin-right: 8px;
        }
        
        .form-row .input-wrapper {
            flex: 1;
            position: relative;
        }
        
        .form-row input {
            width: 100%;
            padding: 10px 16px;
            border: 2px solid transparent;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            transition: all 0.3s ease;
            color: #1a1a2e;
        }
        
        .form-row input:focus {
            outline: none;
            border-color: #3892ce;
            background: white;
            box-shadow: 0 0 0 4px rgba(56, 146, 206, 0.08);
        }
        
        .form-row input:disabled {
            background: #f0f0f0;
            color: #888;
            cursor: not-allowed;
        }
        
        .form-row input:hover:not(:disabled) {
            background: #f0f7ff;
        }
        
        /* Button styling */
        .btn-update {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #3892ce;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            margin-top: 10px;
        }
        
        .btn-update:hover {
            background: #2d7bb3;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(56, 146, 206, 0.3);
        }
        
        .btn-update:active {
            transform: translateY(0);
        }
        
        .btn-update i {
            margin-right: 8px;
        }
        
        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #e9ecef;
        }
        
        .stat-box {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: background 0.2s;
        }
        
        .stat-box:hover {
            background: #f0f7ff;
        }
        
        .stat-box .number {
            font-size: 28px;
            font-weight: 700;
            color: #3892ce;
            font-family: 'Poppins', sans-serif;
        }
        
        .stat-box .label {
            color: #6c757d;
            font-size: 14px;
            margin-top: 4px;
            font-family: 'Poppins', sans-serif;
        }
        
        .stat-box .label i {
            margin-right: 6px;
            color: #3892ce;
        }
        
        /* Message */
        .message {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Logout link */
        .logout-link {
            color: #3892ce !important;
            text-decoration: none;
            margin-left: 10px;
            transition: color 0.2s ease;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
        }
        
        .logout-link:hover {
            color: #2d7bb3 !important;
        }
        
        .logout-link i {
            margin-right: 4px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .profile-container {
                padding: 20px;
            }
            
            .form-row {
                flex-direction: column;
                align-items: stretch;
                padding: 12px 0;
            }
            
            .form-row label {
                min-width: auto;
                margin-bottom: 6px;
                font-size: 13px;
            }
            
            .form-row input {
                padding: 10px 14px;
                font-size: 14px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-header .avatar {
                width: 80px;
                height: 80px;
                font-size: 32px;
            }
        }
        
        @media (max-width: 480px) {
            .profile-container {
                padding: 15px;
            }
            
            .form-row label {
                font-size: 12px;
            }
            
            .form-row input {
                font-size: 13px;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">ESCHOOL</div>
        <div class="profile">
            <h4 style="color: rgb(56, 146, 206); font-family: 'Poppins', sans-serif; font-weight: 500; font-size: 15px;">
                <?php echo htmlspecialchars($_SESSION['email']); ?>
            </h4>
            <i class="fa-solid fa-circle-user" style="font-size: 28px; color: #555;"></i>
            <a href="logout.php" class="logout-link">
                <i class="fa-solid fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </header>

    <div class="container">
        <aside>
            <a href="dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</a>
            <a href="lecture_videos.php"><i class="fa-solid fa-video"></i> Lecture Videos</a>
            <a href="download_history.php"><i class="fa-solid fa-download"></i> Downloads</a>
            <a href="materials.php"><i class="fa-solid fa-book"></i> Materials</a>
            <a href="profile.php" class="active"><i class="fa-solid fa-circle-info"></i> Profile</a>
        </aside>

        <main>
            <div class="profile-container">
                <div class="profile-header">
                    <div class="avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <h2><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['surName']); ?></h2>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>

                <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-row">
                        <label for="firstName">
                            <i class="fa-regular fa-user"></i> First Name
                        </label>
                        <div class="input-wrapper">
                            <input type="text" id="firstName" name="firstName" 
                                   value="<?php echo htmlspecialchars($user['firstName']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <label for="surName">
                            <i class="fa-regular fa-user"></i> Surname
                        </label>
                        <div class="input-wrapper">
                            <input type="text" id="surName" name="surName" 
                                   value="<?php echo htmlspecialchars($user['surName']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <label for="email">
                            <i class="fa-regular fa-envelope"></i> Email
                        </label>
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="update" class="btn-update">
                        <i class="fa-solid fa-save"></i> Update Profile
                    </button>
                </form>

                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="number"><?php echo $stats['uploads']; ?></div>
                        <div class="label"><i class="fa-solid fa-upload"></i> Uploads</div>
                    </div>
                    <div class="stat-box">
                        <div class="number"><?php echo $stats['downloads']; ?></div>
                        <div class="label"><i class="fa-solid fa-download"></i> Downloads</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <footer>
        <div class="logo">ESCHOOL</div>
        <div class="footer-links">
            <a href="#">Terms & Conditions</a>
            <a href="#">Customer Support</a>
            <a href="#">Privacy Policy</a>
        </div>
    </footer>
</body>
</html>