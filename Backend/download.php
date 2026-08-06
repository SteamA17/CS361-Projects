<?php 
    session_start();

    if(!isset($_SESSION['id'])){
        header("Location: index.php");
        exit();
    }

    include 'connect.php';
    
    if (!isset($_GET['id'])) {
        die("No file selected.");
    }
    $userId= $_SESSION['id'];
    $downloadId = $_GET['id'];

    // Get file information
    $sql = "SELECT * FROM downloads WHERE id = $downloadId";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
        die("File not found.");
    }

    $file = mysqli_fetch_assoc($result);

    // Record download
    $sql = "INSERT INTO user_downloads (user_id, download_id)
            VALUES ($userId, $downloadId)";
    mysqli_query($conn, $sql);

    // File path
    $filePath = $file['file_path'];

    if (!file_exists($filePath)) {
        die("File does not exist.");
    }

    // Download file
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"" . basename($filePath) . "\"");
    header("Content-Length: " . filesize($filePath));

    readfile($filePath);
    exit();
?>

