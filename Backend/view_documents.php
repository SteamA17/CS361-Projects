<?php 
    session_start();

    if(!isset($_SESSION['id'])){
        header("Location: index.php");
        exit();
    }

    include "connect.php";

    if(!isset($_GET['id'])){
        die("No document Selected");
    }

    $documentId = (int)$_GET['id'];
    $userId = $_SESSION['id'];

    $stmt = $conn->prepare("SELECT * FROM downloads WHERE id = ?");
    $stmt->bind_param("i", $documentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 0){
        die("Document not found.");
    }

    $document = $result->fetch_assoc();

    if($userId != $document['uploaded_by']){
        $stmt = $conn->prepare("
        INSERT INTO document_views
        (document_id, viewer_id)
        VALUES (?,?)
        ");

        $stmt->bind_param(
            "ii",
            $documentId,
            $userId
        );

        $stmt->execute();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL: Documents</title>

    <style>
        .document-card{
            width:700px;
            margin:40px auto;
            background:white;
            padding:30px;
            border-radius:12px;
            box-shadow:0 5px 15px rgba(0,0,0,.1);
        }

        .document-card h1{
            color:#3892ce;
        }

        .download-btn{
            display:inline-block;
            margin-top:20px;
            padding:12px 24px;
            background:#3892ce;
            color:white;
            text-decoration:none;
            border-radius:8px;
        }

        .download-btn:hover{
            background:#2877b5;
        }
    </style>
</head>
<body>
    <div class="document-card">

        <h1>
            <?php echo htmlspecialchars($document['title']); ?>
        </h1>

        <p>
            <?php echo nl2br(htmlspecialchars($document['description'])); ?>
        </p>

        <hr>

        <p>
            <strong>File Type:</strong>
            <?php echo htmlspecialchars($document['file_type']); ?>
        </p>

        <a class="download-btn" href="download.php?id=<?php echo $document['id']; ?>">
            Download File
        </a>
    </div>
</body>
</html>