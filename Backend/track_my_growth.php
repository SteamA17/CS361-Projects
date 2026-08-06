<?php 
    session_start();
    
    if(!isset($_SESSION['id'])){
        header("Location: index.php");
        exit();
    }

    include 'connect.php';

    $userId = $_SESSION['id'];

    // Getting total Uploads
    $stmt = $conn->prepare("SELECT COUNT(*) AS totalUploads FROM downloads WHERE uploaded_by = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $totalUploads = $stmt->get_result()->fetch_assoc()['totalUploads'];

    if($totalUploads == 0){
        $hasUploads = false;
    }else{
        $hasUploads = true;
    }

    // Getting total Views
    $stmt = $conn->prepare("SELECT COUNT(*) AS totalViews FROM document_views dv 
    INNER JOIN downloads d ON dv.document_id = d.id WHERE d.uploaded_by = ?");

    $stmt->bind_param("i",$userId);
    $stmt->execute();

    $totalViews = $stmt->get_result()->fetch_assoc()['totalViews'];

    // Getting total Downloads
    $stmt = $conn->prepare("SELECT COUNT(*) AS totalDownloads FROM user_downloads ud
    INNER JOIN downloads d ON ud.download_id = d.id WHERE d.uploaded_by = ?");

    $stmt->bind_param("i",$userId);
    $stmt->execute();

    $totalDownloads = $stmt->get_result()->fetch_assoc()['totalDownloads'];

    // Calculating Total Earnings
    $ratePerDownload = 0.50;
    $totalEarnings = $totalDownloads * $ratePerDownload;

    // Analytics for each upload
    $stmt = $conn->prepare("SELECT d.id, d.title, COUNT(DISTINCT dv.id) AS views, COUNT(DISTINCT ud.id) AS downloads
    FROM downloads d LEFT JOIN document_views dv ON d.id = dv.document_id LEFT JOIN user_downloads ud
    ON d.id = ud.download_id WHERE d.uploaded_by = ? GROUP BY d.id ORDER BY downloads DESC");

    $stmt->bind_param("i",$userId);
    $stmt->execute();

    $documents = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL: My-Growth</title>

    <link rel="stylesheet" href="../Frontend/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* Analytics Table */
        .analytics-table {
            width:100%;
            background:white;
            border-collapse:collapse;
            border-radius:15px;
            overflow:hidden;
            box-shadow:0 5px 15px rgba(0,0,0,0.1);
        }

        .analytics-table th {
            background:#3892ce;
            color:white;
            padding:15px;
        }

        .analytics-table td {
            padding:15px;
            text-align:center;
            border-bottom:1px solid #eee;
        }

        .analytics-table tr:hover {
            background:#f5f9fc;
        }

        .growth-container {
            padding: 30px;
        }

        /* No Uploads */
        .no-upload {
            width: 60%;
            margin: auto;
            background: white;
            padding: 40px;
            text-align: center;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .no-upload h2 {
            color:#3892ce;
        }

        .no-upload p {
            color:#666;
            margin:20px 0;
        }

        .no-upload a {
            display:inline-block;
            background:#3892ce;
            color:white;
            padding:12px 25px;
            border-radius:8px;
            text-decoration:none;
        }

        .no-upload a:hover {
            background:#287bb5;
        }
    </style>
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
            <a href="#"><i class="fa-solid fa-video"></i> Lecture Videos</a>
            <a href="download_history.php"><i class="fa-solid fa-download"></i> Downloads</a>
            <a href="#"><i class="fa-solid fa-comments"></i> Group Chat</a>
            <a href="materials.php"><i class="fa-solid fa-book"></i>Materials</a>
            <a href="profile.php"><i class="fa-solid fa-circle-info"></i> Info</a>
        </aside>

        <main>
            <!-- Navigation Tabs -->
            <div class="tabs">
                <a href="./dashboard.php">Dashboard</a>
                <a href="notes.html">Notes</a>
                <a href="tests.html">Tests</a>
                <a href="sessional.html">Sessional</a>
            </div>

            <h2 class="growth-title" style="margin-bottom: 25px; color: #3892ce;">My Business Growth</h2>

            <div class="growth-container">
                <?php if (!$hasUploads): ?>
                    <div class="no-upload">
                        <h2>No Documents Uploaded Yet</h2>

                        <p>
                            You haven't shared any learning materials with other students.
                            Upload your first document and start tracking your growth.
                        </p>

                        <a href="uploads.php" style="text-decoration: none; color: white;
                        background-color: #3892ce; padding: 10px 20px; border-radius: 5px;">
                            Upload Document
                        </a>

                    </div>

                <?php else: ?>
                    <table class="analytics-table">
                        <tr>
                        <th>Document</th>
                        <th>Views</th>
                        <th>Downloads</th>
                        <th>Earnings</th>
                        </tr>

                        <?php
                            while($row = $documents->fetch_assoc()){
                                $earnings = $row['downloads'] * $ratePerDownload;
                                echo "
                                    <tr>
                                        <td>{$row['title']}</td>
                                        <td>{$row['views']}</td>
                                        <td>{$row['downloads']}</td>
                                        <td>K".number_format($earnings,2)."</td>
                                    </tr>
                                ";
                            }
                        ?>
                    </table>
                <?php endif; ?>
            </div>
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