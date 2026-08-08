<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

$downloadId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($downloadId <= 0) {
    die("Invalid file ID.");
}

$userId = $_SESSION['id'];

/*
|--------------------------------------------------------------------------
| Get file information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("SELECT *
    FROM downloads
    WHERE id = ?
");

$stmt->bind_param("i", $downloadId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("File not found.");
}

$file = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Convert database path to actual server path
|--------------------------------------------------------------------------
*/

$filePath = __DIR__ . '/' . $file['file_path'];

if (!file_exists($filePath)) {
    die("File no longer exists on the server.");
}


/*
|--------------------------------------------------------------------------
| Record the download
|--------------------------------------------------------------------------
*/

$checkStmt = $conn->prepare("SELECT id
    FROM user_downloads
    WHERE user_id = ?
    AND download_id = ?
");

$checkStmt->bind_param("ii", $userId, $downloadId);
$checkStmt->execute();

$checkResult = $checkStmt->get_result();


if ($checkResult->num_rows === 0) {

    $insertStmt = $conn->prepare("INSERT INTO user_downloads
        (user_id, download_id)
        VALUES (?, ?)
    ");

    $insertStmt->bind_param("ii", $userId, $downloadId);
    $insertStmt->execute();

    $insertStmt->close();
}

$checkStmt->close();


/*
|--------------------------------------------------------------------------
| Download the file
|--------------------------------------------------------------------------
*/

header("Content-Type: application/octet-stream");

header(
    "Content-Disposition: attachment; filename=\"" .
    basename($file['file_name']) .
    "\""
);

header("Content-Length: " . filesize($filePath));

header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

readfile($filePath);

exit();
?>