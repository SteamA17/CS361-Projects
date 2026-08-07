<?php
session_start();
require_once 'connect.php';

// Check authentication
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

// Get total files uploaded by user
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM downloads WHERE uploaded_by = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$uploadCount = $stmt->get_result()->fetch_assoc()['total'];

// Get total files downloaded by user using user_downloads table
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_downloads WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$downloadCount = $stmt->get_result()->fetch_assoc()['total'];

// Get recent downloads from user_downloads table
$stmt = $conn->prepare("
    SELECT d.title, ud.downloaded_at 
    FROM user_downloads ud
    INNER JOIN downloads d ON ud.download_id = d.id
    WHERE ud.user_id = ? 
    ORDER BY ud.downloaded_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$recentDownloads = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL - Dashboard</title>
    <link rel="stylesheet" href="../Frontend/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Override card styles to match blue/white theme */
        .stat-card {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(56, 146, 206, 0.08);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 20px;
            min-height: 100px;
        }
        
        .stat-card:hover {
            box-shadow: 0 5px 25px rgba(56, 146, 206, 0.15);
            transform: translateY(-3px);
            border-color: #3892ce;
        }
        
        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: white;
            background: linear-gradient(135deg, #3892ce, #2d7bb3);
            flex-shrink: 0;
        }
        
        .stat-card .stat-info {
            flex: 1;
        }
        
        .stat-card .stat-info h3 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a2e;
            margin: 0;
            line-height: 1.2;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .stat-card .stat-info p {
            font-size: 15px;
            color: #6c757d;
            margin: 4px 0 0 0;
            font-weight: 400;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            letter-spacing: 0.3px;
        }
        
        /* Stats Grid - ensure proper spacing */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }
        
        /* Quick Action Cards */
        .card {
            background: white;
            border-radius: 15px;
            border: 1px solid #e9ecef;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(56, 146, 206, 0.08);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 8px 30px rgba(56, 146, 206, 0.12);
            transform: translateY(-5px);
        }
        
        .card .image {
            height: 160px;
            background: linear-gradient(135deg, #3892ce, #2d7bb3) !important;
        }
        
        .card .text {
            padding: 22px 25px;
            text-align: center;
        }
        
        .card .text a {
            text-decoration: none;
            color: #1a1a2e;
            font-weight: 600;
            font-size: 16px;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: block;
            transition: color 0.2s ease;
        }
        
        .card .text a i {
            margin-right: 10px;
            color: #3892ce;
            font-size: 18px;
        }
        
        .card .text a:hover {
            color: #3892ce;
        }
        
        /* Logout button styling - blue like others */
        .logout-link {
            color: #3892ce !important;
            text-decoration: none;
            margin-left: 10px;
            transition: color 0.2s ease;
            font-weight: 500;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .logout-link:hover {
            color: #2d7bb3 !important;
        }
        
        .logout-link i {
            margin-right: 6px;
        }
        
        /* Recent downloads styling */
        .recent-list {
            background: white;
            border-radius: 15px;
            padding: 5px 0;
            margin-top: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
        }
        
        .recent-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 25px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s ease;
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .recent-item:hover {
            background: #f0f7ff;
        }
        
        .recent-item i {
            color: #3892ce;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        
        .recent-item span {
            flex: 1;
            color: #1a1a2e;
            font-weight: 500;
            font-size: 15px;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .recent-item small {
            color: #999;
            font-size: 13px;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        /* Headings */
        h3 {
            color: #1a1a2e;
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 15px;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        hr {
            border: none;
            border-top: 2px solid #e9ecef;
            margin: 30px 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px;
                min-height: 80px;
            }
            
            .stat-card .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 22px;
            }
            
            .stat-card .stat-info h3 {
                font-size: 24px;
            }
            
            .stat-card .stat-info p {
                font-size: 14px;
            }
            
            .cards {
                grid-template-columns: 1fr;
            }
            
            .card .image {
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">ESCHOOL</div>
        <div class="profile">
            <h4 style="color: rgb(56, 146, 206); font-family: 'Poppins', sans-serif; font-weight: 500;"><?php echo htmlspecialchars($_SESSION['email']); ?></h4>
            <i class="fa-solid fa-circle-user" style="font-size: 28px; color: #555;"></i>
            <a href="logout.php" class="logout-link">
                <i class="fa-solid fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </header>

    <div class="container">
        <aside>
            <a href="dashboard.php" class="active"><i class="fa-solid fa-home"></i> Dashboard</a>
            <a href="lecture_videos.php"><i class="fa-solid fa-video"></i> Lecture Videos</a>
            <a href="download_history.php"><i class="fa-solid fa-download"></i> Downloads</a>
            <a href="materials.php"><i class="fa-solid fa-book"></i> Materials</a>
            <a href="profile.php"><i class="fa-solid fa-circle-info"></i> Profile</a>
            <?php if ($uploadCount > 0): ?>
            <a href="track_my_growth.php"><i class="fa-solid fa-chart-line"></i> My Growth</a>
            <?php endif; ?>
        </aside>

        <main>
            <div class="tabs">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="notes.php">Notes</a>
                <a href="tests.php">Tests</a>
                <a href="sessional.php">Sessional</a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-upload"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $uploadCount; ?></h3>
                        <p>Files Uploaded</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-download"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $downloadCount; ?></h3>
                        <p>Files Downloaded</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-info">
                        <h3>Welcome</h3>
                        <p><?php echo htmlspecialchars($_SESSION['firstName']); ?></p>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Quick Actions -->
            <div class="cards">
                <div class="card">
                    <div class="image"></div>
                    <div class="text">
                        <a href="uploads.php">
                            <i class="fa-solid fa-cloud-upload-alt"></i> Upload New Material
                        </a>
                    </div>
                </div>
                <div class="card">
                    <div class="image"></div>
                    <div class="text">
                        <a href="materials.php">
                            <i class="fa-solid fa-book-open"></i> Browse Materials
                        </a>
                    </div>
                </div>
            </div>

            <?php if ($recentDownloads && $recentDownloads->num_rows > 0): ?>
            <h3>Recent Downloads</h3>
            <div class="recent-list">
                <?php while ($row = $recentDownloads->fetch_assoc()): ?>
                <div class="recent-item">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span><?php echo htmlspecialchars($row['title']); ?></span>
                    <small><?php echo date('M d, Y', strtotime($row['downloaded_at'])); ?></small>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <footer>
        <div class="logo">ESCHOOL</div>
        <div class="footer-links">
            <a href="#">Terms & Conditions</a>
            <a href="#">Customer Support</a>
            <a href="#">Privacy Policy</a>
        </div>
    </footer>
</body>
</html>