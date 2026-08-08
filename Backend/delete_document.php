<?php
session_start();
require_once 'connect.php';


header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Please login to delete documents.']);
    exit();
}


$documentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($documentId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid document ID.']);
    exit();
}

$userId = $_SESSION['id'];


$stmt = $conn->prepare("SELECT id, file_path, file_name, uploaded_by FROM downloads WHERE id = ?");
$stmt->bind_param("i", $documentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Document not found.']);
    exit();
}

$document = $result->fetch_assoc();


if ($document['uploaded_by'] != $userId) {
    echo json_encode(['success' => false, 'message' => 'You don\'t have permission to delete this document.']);
    header("Location: track_my_growth.php?deleted=success");
    exit();
}


$conn->begin_transaction();

try {
    
    $stmt = $conn->prepare("DELETE FROM document_views WHERE document_id = ?");
    $stmt->bind_param("i", $documentId);
    $stmt->execute();
    
    
    $stmt = $conn->prepare("DELETE FROM user_downloads WHERE download_id = ?");
    $stmt->bind_param("i", $documentId);
    $stmt->execute();
    
    
    $stmt = $conn->prepare("DELETE FROM downloads WHERE id = ?");
    $stmt->bind_param("i", $documentId);
    $stmt->execute();
    
    
    $filePath = $document['file_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    
    $conn->commit();
    
    
    error_log("User $userId deleted document: " . $document['file_name'] . " (ID: $documentId)");
    
    echo json_encode([
        'success' => true,
        'message' => 'Document deleted successfully.'
    ]);
    header("Location: track_my_growth.php?deleted=success");
    
} catch (Exception $e) {
    
    $conn->rollback();
    error_log("Error deleting document: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete document. Please try again.'
    ]);
    header("Location: track_my_growth.php?deleted=success");
}

$conn->close();
?>