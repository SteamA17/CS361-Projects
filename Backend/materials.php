<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Pagination
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
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 20px;
            transition: transform 0.2s;
        }
        .material-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
        .material-card .file-icon {
            font-size: 40px;
            color: #3892ce;
            margin-bottom: 10px;
        }
        .material-card h3 {
            color: #333;
            margin: 10px 0;
            font-size: 18px;
        }
        .material-card p {
            color: #666;
            font-size: 14px;
            margin: 10px 0;
            line-height: 1.5;
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
        }
        .material-card .btn-view {
            display: inline-block;
            padding: 8px 18px;
            background: #3892ce;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.2s;
        }
        .material-card .btn-view:hover {
            background: #287bb3;
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
    </style>
</head>
<body>
    <header>
        <div class="logo">ESCHOOL</div>
        <div class="profile">
            <h4 style="color: rgb(56, 146, 206);"><?php echo htmlspecialchars($_SESSION['email']); ?></h4>
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
        </aside>

        <main>
            <h2 style="color: #3892ce; margin-bottom: 10px;">
                <i class="fa-solid fa-book-open"></i> Learning Materials
            </h2>
            <p style="color: #666; margin-bottom: 20px;">
                Browse and download study materials shared by the community
            </p>

            <?php if ($materials->num_rows > 0): ?>
            <div class="materials-grid">
                <?php while ($row = $materials->fetch_assoc()): ?>
                <div class="material-card">
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
                    <p><?php echo htmlspecialchars(substr($row['description'], 0, 80)) . '...'; ?></p>
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
                    <a href="view_documents.php?id=<?php echo $row['id']; ?>" class="btn-view">
                        View & Download
                    </a>
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
            <div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 10px;">
                <i class="fa-solid fa-folder-open" style="font-size: 60px; color: #ddd;"></i>
                <h3 style="color: #666; margin-top: 20px;">No materials available yet</h3>
                <p style="color: #999;">Be the first to share a study material!</p>
                <a href="uploads.php" style="display: inline-block; margin-top: 15px; padding: 10px 25px; background: #3892ce; color: white; text-decoration: none; border-radius: 5px;">
                    <i class="fa-solid fa-cloud-upload-alt"></i> Upload Now
                </a>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <footer>
        <div class="logo">ESCHOOL</div>
        <div class="footer-links">
            <a href="#">Terms & Conditions</a>
            <a href="#">Customer Support</a>
        </div>
    </footer>
</body>
</html>