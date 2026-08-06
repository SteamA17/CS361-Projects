<?php 
    session_start();

    if(!isset($_SESSION['id'])){
        header("Location: index.php");
        exit();
    }

    include 'connect.php';
    $userId= $_SESSION['id'];

    $stmt = $conn->prepare("
        SELECT
        downloads.title,
        downloads.description,
        downloads.file_type,
        user_downloads.downloaded_at
    FROM user_downloads
    INNER JOIN downloads
        ON user_downloads.download_id = downloads.id
    WHERE user_downloads.user_id = ?
    ORDER BY user_downloads.downloaded_at DESC
    ");

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL | Download History</title>

    <link rel="stylesheet" href="../Frontend/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        table{
            width:100%;
            margin:20px auto;
            border-radius: 15px;
            box-shadow:0 5px 15px rgba(0,0,0,0.1);
            border-collapse:collapse;
        }

        th,td{
            border:1px solid #ccc;
            padding:12px;
            text-align:center;
        }

        th{
            background:#4CAF50;
            color:white;
        }

    </style>
    
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">
            <a href="./dashboard.php" style="text-decoration: none; color: rgb(56, 146, 206)">
                ESCHOOL
            </a>
        </div>

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
            <!--Download History-->
            <h2 style="margin-bottom: 25px; color: #3892ce; text-align: center;">My Downloads</h2>    
            <table>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>File Type</th>
                    <th>Date</th>
                </tr>

                <?php

                if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                        echo "
                        <tr>
                            <td>".htmlspecialchars($row['title'])."</td>
                            <td>".htmlspecialchars($row['description'])."</td>
                            <td>".htmlspecialchars($row['file_type'])."</td>
                            <td>".$row['downloaded_at']."</td>
                        </tr>
                        ";
                    }
                }else{
                    echo "
                    <tr>
                        <td colspan='4'>
                            You haven't downloaded any materials yet.
                        </td>
                    </tr>
                    ";
                }
                ?>
            </table>
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

<?php $stmt->close(); $conn->close(); ?>