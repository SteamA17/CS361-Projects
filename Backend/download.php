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


$stmt = $conn->prepare("SELECT * FROM downloads WHERE id = ?");
$stmt->bind_param("i", $downloadId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("File not found.");
}

$file = $result->fetch_assoc();


if (!file_exists($file['file_path'])) {
    die("File no longer exists on the server.");
}


$checkStmt = $conn->prepare("SELECT id FROM user_downloads WHERE user_id = ? AND download_id = ?");
$checkStmt->bind_param("ii", $userId, $downloadId);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows === 0) {
    $insertStmt = $conn->prepare("INSERT INTO user_downloads (user_id, download_id) VALUES (?, ?)");
    $insertStmt->bind_param("ii", $userId, $downloadId);
    $insertStmt->execute();
}


error_log("User $userId downloaded file: " . $file['file_name']);


header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . basename($file['file_name']) . "\"");
header("Content-Length: " . filesize($file['file_path']));
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

readfile($file['file_path']);
exit();
?>