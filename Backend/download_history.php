<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['id'];


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM user_downloads WHERE user_id = ?
");
$countStmt->bind_param("i", $userId);
$countStmt->execute();
$totalItems = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $perPage);

$stmt = $conn->prepare("SELECT 
        downloads.title,
        downloads.description,
        downloads.file_type,
        user_downloads.downloaded_at
    FROM user_downloads
    INNER JOIN downloads ON user_downloads.download_id = downloads.id
    WHERE user_downloads.user_id = ?
    ORDER BY user_downloads.downloaded_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $userId, $perPage, $offset);
$stmt->execute();
$history = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL - Download History</title>
    <link rel="stylesheet" href="../Frontend/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .history-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            color: #555;
            border-bottom: 2px solid #dee2e6;
        }
        .history-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .history-table tr:hover {
            background: #f8f9fa;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
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
        .file-type-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-pdf { background: #dc3545; color: white; }
        .badge-doc { background: #0d6efd; color: white; }
        .badge-image { background: #198754; color: white; }
        .badge-other { background: #6c757d; color: white; }
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
            <a href="download_history.php" class="active"><i class="fa-solid fa-download"></i> Downloads</a>
            <a href="materials.php"><i class="fa-solid fa-book"></i> Materials</a>
            <a href="profile.php"><i class="fa-solid fa-circle-info"></i> Profile</a>
        </aside>

        <main>
            <h2 style="color: #3892ce;">
                <i class="fa-solid fa-clock-rotate-left"></i> Download History
            </h2>
            <p style="color: #666; margin-bottom: 10px;">Track all the materials you've downloaded</p>

            <?php if ($history->num_rows > 0): ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>File Type</th>
                        <th>Downloaded At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $history->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars(substr($row['description'], 0, 50)); ?>...</td>
                        <td>
                            <?php
                            $badge = 'badge-other';
                            if (strpos($row['file_type'], 'pdf') !== false) $badge = 'badge-pdf';
                            elseif (strpos($row['file_type'], 'word') !== false || strpos($row['file_type'], 'document') !== false) $badge = 'badge-doc';
                            elseif (strpos($row['file_type'], 'image') !== false) $badge = 'badge-image';
                            ?>
                            <span class="file-type-badge <?php echo $badge; ?>">
                                <?php echo htmlspecialchars($row['file_type']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($row['downloaded_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

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
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <h3>No downloads yet</h3>
                <p>Start exploring materials and download resources to build your library.</p>
                <a href="materials.php" style="display: inline-block; margin-top: 15px; padding: 10px 25px; background: #3892ce; color: white; text-decoration: none; border-radius: 5px;">
                    <i class="fa-solid fa-book-open"></i> Browse Materials
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