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

// Get user stats for sidebar
$userId = $_SESSION['id'];
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM downloads WHERE uploaded_by = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$uploadCount = $stmt->get_result()->fetch_assoc()['total'];

// Get lecture videos
$videos = [];
$loadError = null;

$result = $conn->query("SELECT id, youtube_link, title, description FROM lecture_videos ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
} else {
    $loadError = "Could not load lecture videos right now.";
}

function youtube_embed_url($url) {
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]{11})/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL - Lecture Videos</title>
    <link rel="stylesheet" href="../Frontend/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
    
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 24px;
            margin-top: 20px;
        }
        
        .video-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .video-card .video-wrap {
            position: relative;
            width: 100%;
            padding-top: 56.25%; 
            background: #000;
        }
        
        .video-card .video-wrap iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        
        .video-card .video-info {
            padding: 15px 20px;
        }
        
        .video-card .video-info h4 {
            margin: 0 0 5px 0;
            color: #1a1a2e;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }
        
        .video-card .video-info p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }
        
        .no-videos {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            border: 2px dashed #e9ecef;
        }
        
        .no-videos i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .no-videos h3 {
            color: #666;
            margin: 0 0 10px 0;
        }
        
        .no-videos p {
            color: #999;
            margin: 0;
        }
        
        .video-error {
            padding: 15px 20px;
            background: #f8d7da;
            color: #721c24;
            border-radius: 10px;
            border: 1px solid #f5c6cb;
            font-family: 'Poppins', sans-serif;
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .video-grid {
                grid-template-columns: 1fr;
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
            <a href="lecture_videos.php" class="active"><i class="fa-solid fa-video"></i> Lecture Videos</a>
            <a href="download_history.php"><i class="fa-solid fa-download"></i> Downloads</a>
            <a href="materials.php"><i class="fa-solid fa-book"></i> Materials</a>
            <a href="profile.php"><i class="fa-solid fa-circle-info"></i> Profile</a>
            <?php if ($uploadCount > 0): ?>
            <a href="track_my_growth.php"><i class="fa-solid fa-chart-line"></i> My Growth</a>
            <?php endif; ?>
        </aside>

        <main>
            <!-- Navigation Tabs -->
            <div class="tabs">
                <a href="dashboard.php">Dashboard</a>
                <a href="notes.php">Notes</a>
                <a href="tests.php">Tests</a>
                <a href="sessional.php">Sessional</a>
            </div>

            <h2 style="color: #3892ce; margin-bottom: 10px; font-family: 'Poppins', sans-serif;">
                <i class="fa-solid fa-video"></i> Lecture Videos
            </h2>
            <p style="color: #6c757d; margin-bottom: 20px; font-family: 'Poppins', sans-serif;">
                Watch recorded lectures and tutorials
            </p>

            <?php if ($loadError): ?>
                <div class="video-error">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($loadError); ?>
                </div>
            <?php elseif (empty($videos)): ?>
                <div class="no-videos">
                    <i class="fa-solid fa-video-slash"></i>
                    <h3>No lecture videos available</h3>
                    <p>Check back later for new video content.</p>
                </div>
            <?php else: ?>
                <div class="video-grid">
                    <?php foreach ($videos as $video): ?>
                        <?php $embedUrl = youtube_embed_url($video['youtube_link']); ?>
                        <?php if ($embedUrl): ?>
                            <div class="video-card">
                                <div class="video-wrap">
                                    <iframe
                                        src="<?php echo htmlspecialchars($embedUrl); ?>"
                                        title="Lecture video <?php echo (int) $video['id']; ?>"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                    </iframe>
                                </div>
                                <?php if (!empty($video['title']) || !empty($video['description'])): ?>
                                <div class="video-info">
                                    <?php if (!empty($video['title'])): ?>
                                    <h4><?php echo htmlspecialchars($video['title']); ?></h4>
                                    <?php endif; ?>
                                    <?php if (!empty($video['description'])): ?>
                                    <p><?php echo htmlspecialchars($video['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
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