<?php
    session_start();

    if(!isset($_SESSION['email'])){
        header("Location: index.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL: Dashboard</title>
    <link rel="stylesheet" href="../Frontend/style.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body data-page="dashboard">

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

        <!-- Main -->
        <main>

            <!-- Navigation Tabs -->
            <div class="tabs">
                <a href="dashboard.html" class="active">Dashboard</a>
                <a href="notes.html">Notes</a>
                <a href="tests.html">Tests</a>
                <a href="sessional.html">Sessional</a>
            </div>

            <!-- Top Cards -->
            <div class="cards">

                <div class="card">
                    <div class="image"></div>
                    <div class="text">
                        Plan your study schedule
                    </div>
                </div>

                <div class="card">
                    <div class="image"></div>
                    <div class="text">
                        Plan your group meetings
                    </div>
                </div>

            </div>

            <hr>

            <h3>My Business</h3>

            <!-- Bottom Cards -->
            <div class="cards" id="businessCards">

                <div class="card">
                    <div class="image"></div>
                    <div class="text">
                        <a href="uploads.php" style="text-decoration: none; color: inherit;">Upload New Material</a>
                    </div>
                </div>

                <div class="card">
                    <div class="image"></div>
                    <div class="text">
                        <a href="track_my_growth.php" style="text-decoration: none; color: inherit;">Track My Growth</a>
                    </div>
                </div>

                <div class="card" id="addCategoryCard">
                    <div class="image"></div>
                    <div class="text">
                        Add New Category
                    </div>
                </div>

            </div>

            <!-- Add Category Modal -->
            <div class="modal-overlay" id="categoryModalOverlay">
                <div class="modal-box">
                    <div class="modal-header">
                        <h3>Add New Category</h3>
                        <button type="button" class="modal-close" id="categoryModalClose" aria-label="Close">&times;</button>
                    </div>
                    <form id="categoryForm">
                        <div class="input-group">
                            <input type="text" id="categoryCode" placeholder="Course Code (e.g. CS210)" required maxlength="10">
                        </div>
                        <div class="input-group">
                            <input type="text" id="categoryName" placeholder="Course Name (optional)" maxlength="40">
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn-secondary" id="categoryCancelBtn">Cancel</button>
                            <button type="submit" class="btn">Add Category</button>
                        </div>
                    </form>
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

    <script src="../Frontend/script.js"></script>

</body>
</html>