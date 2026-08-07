<?php
    session_start();

    if(!isset($_SESSION['id'])){
        header("Location: index.php");
        exit();
    }

    include "connect.php";

    $userId = $_SESSION['id'];

    if(isset($_GET['id'])){
        $documentId = $_GET['id'];

        // First get the file path (to delete the actual file)
        $stmt = $conn->prepare("SELECT file_path FROM downloads 
            WHERE id = ? AND uploaded_by = ?"
        );

        $stmt->bind_param("ii", $documentId, $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows > 0){
            $document = $result->fetch_assoc();

            // Delete physical file
            if(file_exists($document['file_path'])){
                unlink($document['file_path']);
            }

            // Delete views
            $stmt = $conn->prepare(
                "DELETE FROM document_views WHERE document_id = ?"
            );

            $stmt->bind_param("i",$documentId);
            $stmt->execute();

            // Delete downloads history
            $stmt = $conn->prepare(
                "DELETE FROM user_downloads WHERE download_id = ?"
            );

            $stmt->bind_param("i",$documentId);
            $stmt->execute();

            // Delete the document itself
            $stmt = $conn->prepare(
                "DELETE FROM downloads 
                WHERE id = ? AND uploaded_by = ?"
            );

            $stmt->bind_param("ii",$documentId,$userId);
            $stmt->execute();

            header("Location: track_my_growth.php?deleted=success");
            exit();
        }else{
            echo "You cannot delete this document.";
        }
    }
?>