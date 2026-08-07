<?php 
    session_start();

    if(!isset($_SESSION['email'])){
        header("Location: index.php");
        exit();
    }

    include "connect.php";

    $userId = $_SESSION['id'];

    $sql = "SELECT downloads.*
            FROM user_downloads
            INNER JOIN downloads
            ON downloads.id = user_downloads.download_id
            WHERE user_downloads.user_id = ?
            AND downloads.document_type= ?";

    $type = "sessional";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is",$userId,$type);
    $stmt->execute();

    $result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ESCHOOL Sessional</title>

    <link rel="stylesheet" href="../Frontend/style.css">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .materials-container{
            display:flex;
            flex-wrap:wrap;
            gap:20px;
        }

        .material-card{
            width:140px;
            border-radius:10px;
            overflow:hidden;
            background:#eceaea;
            cursor:pointer;
        }

        .material-image{
            height:120px;
            background:#b7b7b7;
            display:flex;
            justify-content:center;
            align-items:center;
            font-size:45px;
            color:white;
        }

        .material-title{
            padding:10px;
            font-size:13px;
        }
    </style>

</head>
<body>
    <header>
        <div class="logo">
            ESCHOOL
        </div>

        <div class="profile">
            <h4 style="color: rgb(56, 146, 206); font-size: 15px;"><?php echo $_SESSION['email']; ?></h4>
            <i class="fa-solid fa-circle-user"></i>
        </div>
    </header>

    <div class="container">
        <!-- Sidebar -->
        <aside>
            <a href="#"><i class="fa-solid fa-video"></i>Lecture Videos</a>
            <a href="download_history.php"><i class="fa-solid fa-download"></i>Downloads</a>
            <a href="#"><i class="fa-solid fa-comments"></i>Group Chat</a>
            <a href="materials.php"><i class="fa-solid fa-book"></i>Materials</a>
            <a href="profile.php"><i class="fa-solid fa-circle-info"></i>Info</a>
        </aside>


        <!-- Main -->
        <main>
            <!-- Tabs -->
            <div class="tabs">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="notes.php">Notes</a>
                    <a href="tests.php">Tests</a>
                    <a href="sessional.php" class="active">Sessional</a>
            </div>

            <h2 class="growth-title" style="margin-bottom: 25px; color: #3892ce;">Sessional Papers</h2>

            <div class="materials-container">
                <?php
                    if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                ?>
                        <div class="material-card">
                            <a href="<?php echo $row['file_path']; ?>" target="_blank" class="material-card">
                                <div class="material-image">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </div>

                                <div class="material-title">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </div>
                            </a>
                        </div>
                <?php
                    }
                }else {
                ?>

                    <div class="no-notes">
                        <i class="fa-solid fa-folder-open"></i>
                        <h3>You don't have any Sessional Papers yet</h3>
                        <p>Your downloaded sessional papers will appear here.</p>
                    </div>

                <?php
                }
                ?>
            </div>

            <hr>

                <h3>My Business</h3>

                <!-- Bottom Cards -->
                <div class="cards">
                    <div class="card">
                        <div class="image"></div>
                        <div class="text">
                            <a href="uploads.php" style="text-decoration: none; color: inherit;">Upload New Material</a>
                        </div>
                    </div>
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