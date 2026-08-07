<?php 
session_start();
require_once "connect.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("No document selected.");
}

$documentId = (int)$_GET['id'];
$userId = $_SESSION['id'];

$stmt = $conn->prepare("SELECT * FROM downloads WHERE id = ?");
$stmt->bind_param("i", $documentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Document not found.");
}

$document = $result->fetch_assoc();


if ($userId != $document['uploaded_by']) {
    $stmt = $conn->prepare("
        INSERT INTO document_views (document_id, viewer_id)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ii", $documentId, $userId);
    $stmt->execute();
}

$isOwner = ($userId == $document['uploaded_by']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL - Document Details</title>
    <link rel="stylesheet" href="../Frontend/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .document-container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .document-container h1 {
            color: #1a1a2e;
            margin: 0 0 10px 0;
            font-family: 'Poppins', sans-serif;
        }
        
        .document-container .meta {
            color: #6c757d;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 20px;
        }
        
        .document-container .description {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .document-container .actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        
        .btn-download {
            flex: 1;
            padding: 14px 24px;
            background: #3892ce;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-download:hover {
            background: #2d7bb3;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(56, 146, 206, 0.3);
        }
        
        .btn-delete-page {
            padding: 14px 24px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-delete-page:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        
        .btn-back {
            padding: 14px 24px;
            background: #e9ecef;
            color: #495057;
            text-decoration: none;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-back:hover {
            background: #dee2e6;
        }
        
        .owner-badge {
            display: inline-block;
            background: #e9ecef;
            color: #6c757d;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body>
    <div class="document-container">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap;">
            <div>
                <h1><?php echo htmlspecialchars($document['title']); ?></h1>
                <div class="meta">
                    <i class="fa-regular fa-user"></i> 
                    <?php echo $isOwner ? 'You' : 'Shared by user #' . $document['uploaded_by']; ?>
                    <?php if ($isOwner): ?>
                    <span class="owner-badge"><i class="fa-solid fa-crown"></i> Owner</span>
                    <?php endif; ?>
                    &nbsp;|&nbsp;
                    <i class="fa-regular fa-calendar"></i>
                    <?php echo date('M d, Y', strtotime($document['uploaded_at'])); ?>
                </div>
            </div>
            <div>
                <span style="background: #f8f9fa; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-family: 'Poppins', sans-serif; color: #6c757d;">
                    <?php echo htmlspecialchars($document['file_type']); ?>
                </span>
            </div>
        </div>

        <div class="description">
            <?php echo nl2br(htmlspecialchars($document['description'])); ?>
        </div>

        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin: 15px 0; font-family: 'Poppins', sans-serif; color: #6c757d; font-size: 14px;">
            <i class="fa-regular fa-file"></i> 
            File: <?php echo htmlspecialchars($document['file_name']); ?>
        </div>

        <div class="actions">
            <a href="download.php?id=<?php echo $document['id']; ?>" class="btn-download">
                <i class="fa-solid fa-download"></i> Download File
            </a>
            <?php if ($isOwner): ?>
            <button onclick="confirmDelete(<?php echo $document['id']; ?>, '<?php echo htmlspecialchars(addslashes($document['title'])); ?>')" class="btn-delete-page">
                <i class="fa-solid fa-trash"></i> Delete
            </button>
            <?php endif; ?>
            <a href="materials.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back to Materials
            </a>
        </div>
    </div>

    <!-- Delete Confirmation Modal (same as materials.php) -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <div class="modal-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3>Delete Document?</h3>
            <p id="deleteMessage">Are you sure you want to delete "<span id="deleteTitle"></span>"? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="btn-confirm-delete" onclick="deleteDocument()">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal {
            background: white;
            padding: 35px;
            border-radius: 15px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease;
        }
        @keyframes modalSlideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal .modal-icon {
            text-align: center;
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 15px;
        }
        .modal h3 {
            text-align: center;
            color: #1a1a2e;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 10px;
        }
        .modal p {
            text-align: center;
            color: #6c757d;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        .modal .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        .modal .modal-actions button {
            padding: 10px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
        }
        .modal .modal-actions .btn-cancel {
            background: #e9ecef;
            color: #495057;
        }
        .modal .modal-actions .btn-cancel:hover {
            background: #dee2e6;
        }
        .modal .modal-actions .btn-confirm-delete {
            background: #dc3545;
            color: white;
        }
        .modal .modal-actions .btn-confirm-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        .btn-cancel, .btn-confirm-delete {
            cursor: pointer !important;
        }
    </style>

    <script>
        let deleteId = null;
        
        function confirmDelete(id, title) {
            deleteId = id;
            document.getElementById('deleteTitle').textContent = title;
            document.getElementById('deleteModal').classList.add('active');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            deleteId = null;
        }
        
        function deleteDocument() {
            if (!deleteId) return;
            
            const confirmBtn = document.querySelector('.btn-confirm-delete');
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';
            
            fetch('delete_document.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + deleteId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'materials.php?deleted=1';
                } else {
                    alert(data.message || 'Failed to delete document.');
                }
                closeDeleteModal();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                closeDeleteModal();
            })
            .finally(() => {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fa-solid fa-trash"></i> Delete';
            });
        }
        
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>