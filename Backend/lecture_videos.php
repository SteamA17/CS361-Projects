<?php
    session_start();

    if(!isset($_SESSION['email'])){
        header("Location: index.php");
        exit();
    }

    require_once 'connect.php';

    $videos = [];
    $loadError = null;

    $result = $conn->query("SELECT id, youtube_link FROM lecture_videos ORDER BY id DESC");
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
    <title>ESCHOOL: Lecture Videos</title>
    <link rel="stylesheet" href="../Frontend/style.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

    <!-- Header -->
    <header>
        <div class="logo">ESCHOOL</div>

        <div class="profile">
            <h4 style="color: rgb(56, 146, 206); font-size: 15px;"><?php echo $_SESSION['email']; ?></h4>
            <i class="fa-solid fa-circle-user"></i>
        </div>
    </header>

    <div class="container">

        <!-- Sidebar -->
         <aside>
            <a href="lecture_videos.php"><i class="fa-solid fa-video"></i> Lecture Videos</a>
            <a href="download_history.php"><i class="fa-solid fa-download"></i> Downloads</a>
            <a href="#"><i class="fa-solid fa-comments"></i> Group Chat</a>
            <a href="materials.php"><i class="fa-solid fa-book"></i>Materials</a>
            <a href="profile.php"><i class="fa-solid fa-circle-info"></i> Info</a>
        </aside>

        <!-- Main -->
        <main>

            <!-- Navigation Tabs -->
            <div class="tabs">
                <a href="dashboard.php">Dashboard</a>
                <a href="notes.html">Notes</a>
                <a href="tests.html">Tests</a>
                <a href="sessional.html">Sessional</a>
            </div>

            <h3>Lecture Videos</h3>

            <?php if ($loadError): ?>
                <p style="color: #b71c1c;"><?php echo htmlspecialchars($loadError); ?></p>
            <?php elseif (empty($videos)): ?>
                <p>No lecture videos have been added yet.</p>
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
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>

    </div>

    <!-- Footer -->
    <footer>
        <div class="logo">
            <a href="./dashboard.php" style="text-decoration: none; color: rgb(56, 146, 206)">
                ESCHOOL
            </a>
        </div>

        <div class="footer-links">
            <a href="#">Terms & Conditions</a>
            <a href="#">Customer Support</a>
        </div>
    </footer>

</body>
</html>