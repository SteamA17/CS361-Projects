<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// CSRF Protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
}

// Configuration
$uploadDir = __DIR__ . '/uploads/';
$maxFileSize = 100 * 1024 * 1024; // 100MB
$allowedTypes = [
    'application/pdf' => '.pdf',
    'application/msword' => '.doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '.docx',
    'application/vnd.ms-powerpoint' => '.ppt',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '.pptx',
    'application/vnd.ms-excel' => '.xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '.xlsx',
    'image/jpeg' => '.jpg',
    'image/png' => '.png',
    'text/plain' => '.txt'
];

// Create upload directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$message = '';
$messageType = '';

if (isset($_POST['upload'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $year = sanitize($_POST['year']);
    $documentType = sanitize($_POST['document_type']);
    $uploadedBy = $_SESSION['id'];
    
    // Validate title and description
    if (empty($title) || strlen($title) < 3) {
        $message = "Title must be at least 3 characters.";
        $messageType = 'error';

    } elseif (empty($description) || strlen($description) < 10) {
        $message = "Description must be at least 10 characters.";
        $messageType = 'error';

    } elseif (empty($year)) {
        $message = "Please select an academic year.";
        $messageType = 'error';

    } elseif (empty($documentType)) {
        $message = "Please select a document type.";
        $messageType = 'error';

    } elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $message = "Description must be at least 10 characters.";
        $messageType = 'error';
    } elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $message = "File upload error: " . $_FILES['file']['error'];
        $messageType = 'error';
    } else {
        // Validate file type
        $fileType = $_FILES['file']['type'];
        if (!isset($allowedTypes[$fileType])) {
            $message = "File type not allowed. Please upload PDF, DOC, PPT, or image files.";
            $messageType = 'error';
        }
        // Validate file size
        elseif ($_FILES['file']['size'] > $maxFileSize) {
            $message = "File too large. Maximum size is 100MB.";
            $messageType = 'error';
        }
        // Validate file (scan for malicious content)
        else {
            $tmpFile = $_FILES['file']['tmp_name'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tmpFile);
            finfo_close($finfo);
            
            if ($mimeType !== $fileType) {
                $message = "File appears to be corrupted or has incorrect extension.";
                $messageType = 'error';
            } else {
                // Generate safe filename
                $extension = $allowedTypes[$fileType];
                $safeName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $title) . $extension;
                
                $filePath = $uploadDir . $safeName;
                $webPath = "uploads/" . $safeName;
                
                // Move file
                if (move_uploaded_file($tmpFile, $filePath)) {
                    // Save to database
                    $stmt = $conn->prepare(" INSERT INTO downloads 
                        (title, description, year, document_type, file_name, file_path, file_type, uploaded_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    $stmt->bind_param(
                        "sssssssi",
                        $title,
                        $description,
                        $year,
                        $documentType,
                        $safeName,
                        $webPath,
                        $fileType,
                        $uploadedBy
                    );
                    
                    if ($stmt->execute()) {
                        $message = "File uploaded successfully!";
                        $messageType = 'success';
                    } else {
                        unlink($filePath); // Delete file if database fails
                        $message = "Database error. Please try again.";
                        $messageType = 'error';
                    }
                    $stmt->close();
                } else {
                    $message = "File upload failed. Please check folder permissions.";
                    $messageType = 'error';
                }
            }
        }
    }
}

// Helper functions (if not defined in connect.php)
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
    <title>ESCHOOL - Upload Files</title>
    <link rel="stylesheet" href="../Frontend/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .upload-container {
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
            padding: 35px 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .upload-container h2 {
            color: #3892ce;
            margin-bottom: 30px;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 24px;
        }
        
        .upload-container h2 i {
            margin-right: 10px;
        }
        
        /* Form Row - Label on left, input on right */
        .form-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            padding: 8px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .form-row:last-of-type {
            border-bottom: none;
        }
        
        .form-row label {
            min-width: 130px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            flex-shrink: 0;
            padding-top: 10px;
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
        
        .form-row input[type="text"],
        .form-row textarea {
            width: 100%;
            padding: 10px 16px;
            border: 2px solid transparent;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            transition: all 0.3s ease;
            color: #1a1a2e;
            box-sizing: border-box;
        }
        
        .form-row textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-row input[type="text"]:focus,
        .form-row textarea:focus {
            outline: none;
            border-color: #3892ce;
            background: white;
            box-shadow: 0 0 0 4px rgba(56, 146, 206, 0.08);
        }
        
        /* File input styling */
        .file-input-wrapper {
            position: relative;
            width: 100%;
        }
        
        .file-input-wrapper input[type="file"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            cursor: pointer;
            background: #fafafa;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #555;
        }
        
        .file-input-wrapper input[type="file"]:hover {
            border-color: #3892ce;
            background: #f0f7ff;
        }
        
        .file-input-wrapper input[type="file"]::-webkit-file-upload-button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            background: #3892ce;
            color: white;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            margin-right: 10px;
            transition: background 0.2s;
        }
        
        .file-input-wrapper input[type="file"]::-webkit-file-upload-button:hover {
            background: #2d7bb3;
        }
        
        .file-info {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 13px;
            color: #6c757d;
            font-family: 'Poppins', sans-serif;
            border-left: 3px solid #3892ce;
        }
        
        .file-info i {
            margin-right: 8px;
            color: #3892ce;
        }
        
        /* Button styling */
        .btn-upload {
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
        
        .btn-upload:hover {
            background: #2d7bb3;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(56, 146, 206, 0.3);
        }
        
        .btn-upload:active {
            transform: translateY(0);
        }
        
        .btn-upload i {
            margin-right: 8px;
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
        .form-row select {
            width: 100%;
            padding: 10px 16px;
            border: 2px solid transparent;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            transition: all 0.3s ease;
            color: #1a1a2e;
            box-sizing: border-box;
            cursor: pointer;
        }

        .form-row select:focus {
            outline: none;
            border-color: #3892ce;
            background: white;
            box-shadow: 0 0 0 4px rgba(56, 146, 206, 0.08);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .upload-container {
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
                padding-top: 0;
                font-size: 13px;
            }
            
            .form-row input[type="text"],
            .form-row textarea {
                padding: 10px 14px;
                font-size: 14px;
            }
            
            .upload-container h2 {
                font-size: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .upload-container {
                padding: 15px;
            }
            
            .form-row label {
                font-size: 12px;
            }
            
            .form-row input[type="text"],
            .form-row textarea {
                font-size: 13px;
                padding: 8px 12px;
            }
            
            .file-input-wrapper input[type="file"] {
                font-size: 12px;
                padding: 10px;
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
            <a href="profile.php"><i class="fa-solid fa-circle-info"></i> Profile</a>
        </aside>

        <main>
            <div class="upload-container">
                <h2><i class="fa-solid fa-cloud-upload-alt"></i> Upload New Material</h2>
                
                <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>
                
                <form action="uploads.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-row">
                        <label for="title">
                            <i class="fa-regular fa-file"></i> Title *
                        </label>
                        <div class="input-wrapper">
                            <input type="text" id="title" name="title" placeholder="Enter file title" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <label for="description">
                            <i class="fa-regular fa-file-lines"></i> Description *
                        </label>
                        <div class="input-wrapper">
                            <textarea id="description" name="description" placeholder="Describe your file" required></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <label for="year">
                            <i class="fa-solid fa-calendar"></i> Academic Year *
                        </label>
                        <div class="input-wrapper">
                            <select id="year" name="year" required>
                                <option value="" selected disabled>Select Academic Year</option>
                                <option value="Year 1">Year 1</option>
                                <option value="Year 2">Year 2</option>
                                <option value="Year 3">Year 3</option>
                                <option value="Year 4">Year 4</option>
                                <option value="Year 5">Year 5</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <label for="document_type">
                            <i class="fa-solid fa-file-lines"></i> Document Type *
                        </label>
                        <div class="input-wrapper">
                            <select id="document_type" name="document_type" required>
                                <option value="" selected disabled>Select Document Type</option>
                                <option value="notes">Notes</option>
                                <option value="test">Test Paper</option>
                                <option value="sessional">Sessional Paper</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <label for="file">
                            <i class="fa-regular fa-folder-open"></i> Select File *
                        </label>
                        <div class="input-wrapper">
                            <div class="file-input-wrapper">
                                <input type="file" id="file" name="file" required>
                            </div>
                            <div class="file-info">
                                <i class="fa-solid fa-info-circle"></i>
                                Allowed: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG, TXT (Max: 10MB)
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="upload" class="btn-upload">
                        <i class="fa-solid fa-upload"></i> Upload File
                    </button>
                </form>
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