<?php
session_start();
require_once 'connect.php';

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

// Get user stats for sidebar
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM downloads WHERE uploaded_by = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$uploadCount = $stmt->get_result()->fetch_assoc()['total'];


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;


$countResult = $conn->query("SELECT COUNT(*) as total FROM downloads");
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $perPage);


$stmt = $conn->prepare("
    SELECT d.*, u.firstName, u.surName 
    FROM downloads d 
    LEFT JOIN users u ON d.uploaded_by = u.id 
    ORDER BY d.uploaded_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$materials = $stmt->get_result();


if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL - Materials</title>
    <link rel="stylesheet" href="../Frontend/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .materials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .material-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            position: relative;
        }
        
        .material-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .material-card .file-icon {
            font-size: 40px;
            color: #3892ce;
            margin-bottom: 10px;
        }
        
        .material-card h3 {
            color: #1a1a2e;
            margin: 10px 0;
            font-size: 18px;
            font-family: 'Poppins', sans-serif;
        }
        
        .material-card p {
            color: #6c757d;
            font-size: 14px;
            margin: 10px 0;
            line-height: 1.5;
            font-family: 'Poppins', sans-serif;
        }
        
        .material-card .meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 13px;
            color: #888;
            font-family: 'Poppins', sans-serif;
        }
        
        .material-card .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .material-card .btn-view {
            flex: 1;
            display: inline-block;
            padding: 8px 18px;
            background: #3892ce;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: background 0.2s;
            text-align: center;
        }
        
        .material-card .btn-view:hover {
            background: #2d7bb3;
        }
        
        .material-card .btn-delete {
            padding: 8px 16px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .material-card .btn-delete:hover {
            background: #c82333;
        }
        
        .material-card .btn-delete i {
            font-size: 14px;
        }
        
        
        .material-card .btn-delete.own {
            background: #dc3545;
        }
        
        .material-card .btn-delete.own:hover {
            background: #c82333;
        }
        
        .material-card .btn-delete.hidden {
            display: none;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a {
            padding: 8px 16px;
            background: #f8f9fa;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }
        
        .pagination a.active {
            background: #3892ce;
            color: white;
            border-color: #3892ce;
        }
        
        .pagination a:hover:not(.active) {
            background: #e9ecef;
        }
        
        
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
        
        /* Delete Modal */
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
            from {
                transform: translateY(-30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
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
        
        .owner-badge {
            display: inline-block;
            background: #e9ecef;
            color: #6c757d;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-family: 'Poppins', sans-serif;
        }
        
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 10px;
            color: white;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            z-index: 2000;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            animation: slideInRight 0.3s ease;
            display: none;
        }
        
        .toast.success {
            background: #28a745;
        }
        
        .toast.error {
            background: #dc3545;
        }
        
        .toast.info {
            background: #3892ce;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .materials-grid {
                grid-template-columns: 1fr;
            }
            
            .material-card .actions {
                flex-direction: column;
            }
            
            .modal {
                padding: 25px;
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
            <a href="materials.php" class="active"><i class="fa-solid fa-book"></i> Materials</a>
            <a href="profile.php"><i class="fa-solid fa-circle-info"></i> Profile</a>
            <?php if ($uploadCount > 0): ?>
            <a href="track_my_growth.php"><i class="fa-solid fa-chart-line"></i> My Growth</a>
            <?php endif; ?>
        </aside>

        <main>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div>
                    <h2 style="color: #3892ce; margin: 0; font-family: 'Poppins', sans-serif;">
                        <i class="fa-solid fa-book-open"></i> Learning Materials
                    </h2>
                    <p style="color: #6c757d; margin: 5px 0 0 0; font-family: 'Poppins', sans-serif;">
                        Browse and download study materials shared by the community
                    </p>
                </div>
                <a href="uploads.php" style="padding: 10px 20px; background: #3892ce; color: white; text-decoration: none; border-radius: 8px; font-family: 'Poppins', sans-serif; font-weight: 500; transition: all 0.2s;">
                    <i class="fa-solid fa-cloud-upload-alt"></i> Upload New
                </a>
            </div>

            <?php if ($materials->num_rows > 0): ?>
            <div class="materials-grid">
                <?php while ($row = $materials->fetch_assoc()): ?>
                <div class="material-card" data-id="<?php echo $row['id']; ?>">
                    <div class="file-icon">
                        <?php
                        $icon = 'fa-file';
                        if (strpos($row['file_type'], 'pdf') !== false) $icon = 'fa-file-pdf';
                        elseif (strpos($row['file_type'], 'word') !== false) $icon = 'fa-file-word';
                        elseif (strpos($row['file_type'], 'excel') !== false) $icon = 'fa-file-excel';
                        elseif (strpos($row['file_type'], 'powerpoint') !== false) $icon = 'fa-file-powerpoint';
                        elseif (strpos($row['file_type'], 'image') !== false) $icon = 'fa-file-image';
                        ?>
                        <i class="fa-regular <?php echo $icon; ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($row['description'], 0, 80)) . (strlen($row['description']) > 80 ? '...' : ''); ?></p>
                    <div class="meta">
                        <span>
                            <i class="fa-regular fa-user"></i>
                            <?php echo htmlspecialchars($row['firstName'] ?? 'Unknown'); ?>
                        </span>
                        <span>
                            <i class="fa-regular fa-calendar"></i>
                            <?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?>
                        </span>
                    </div>
                    <div class="actions">
                        <a href="view_documents.php?id=<?php echo $row['id']; ?>" class="btn-view">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                        <?php if ($_SESSION['id'] == $row['uploaded_by']): ?>
                        <button class="btn-delete own" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['title'])); ?>')">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>"><i class="fa-solid fa-chevron-left"></i></a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>"><i class="fa-solid fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 10px; border: 2px dashed #e9ecef;">
                <i class="fa-solid fa-folder-open" style="font-size: 60px; color: #ddd;"></i>
                <h3 style="color: #666; margin-top: 20px; font-family: 'Poppins', sans-serif;">No materials available yet</h3>
                <p style="color: #999; font-family: 'Poppins', sans-serif;">Be the first to share a study material!</p>
                <a href="uploads.php" style="display: inline-block; margin-top: 15px; padding: 12px 28px; background: #3892ce; color: white; text-decoration: none; border-radius: 8px; font-family: 'Poppins', sans-serif; font-weight: 500;">
                    <i class="fa-solid fa-cloud-upload-alt"></i> Upload Now
                </a>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
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

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <span id="toastMessage"></span>
    </div>

    <footer>
        <div class="logo">ESCHOOL</div>
        <div class="footer-links">
            <a href="#">Terms & Conditions</a>
            <a href="#">Customer Support</a>
            <a href="#">Privacy Policy</a>
        </div>
    </footer>

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
                    showToast('Document deleted successfully!', 'success');
                    
                    const card = document.querySelector(`.material-card[data-id="${deleteId}"]`);
                    if (card) {
                        card.style.transition = 'all 0.3s ease';
                        card.style.transform = 'scale(0.8)';
                        card.style.opacity = '0';
                        setTimeout(() => {
                            card.remove();
                        
                            if (document.querySelectorAll('.material-card').length === 0) {
                                location.reload();
                            }
                        }, 300);
                    } else {
                        location.reload();
                    }
                } else {
                    showToast(data.message || 'Failed to delete document.', 'error');
                }
                closeDeleteModal();
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
                closeDeleteModal();
            })
            .finally(() => {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fa-solid fa-trash"></i> Delete';
            });
        }
        
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            
            toast.className = 'toast ' + type;
            toastMessage.textContent = message;
            toast.style.display = 'block';
            
            setTimeout(() => {
                toast.style.animation = 'slideInRight 0.3s ease reverse';
                setTimeout(() => {
                    toast.style.display = 'none';
                    toast.style.animation = '';
                }, 300);
            }, 3000);
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