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
    <title>ESCHOOL Dashboard</title>
    <link rel="stylesheet" href="../Fronted/style.css">

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

            <a href="#"><i class="fa-solid fa-video"></i> Lecture Videos</a>

            <a href="#"><i class="fa-solid fa-download"></i> Downloads</a>

            <a href="#"><i class="fa-solid fa-comments"></i> Group Chat</a>

            <a href="#"><i class="fa-solid fa-bookmark"></i> Bookmark</a>

            <a href="#"><i class="fa-solid fa-star"></i> Favourites</a>

            <a href="#"><i class="fa-solid fa-circle-info"></i> Info</a>

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
            <div class="cards">

                <div class="card">
                    <div class="image"></div>
                    <div class="text">
                        Make a new category
                    </div>
                </div>

                <div class="card">
                    <div class="image"></div>
                    <div class="text">
                        Track my growth
                    </div>
                </div>

            </div>

        </main>

    </div>

    <!-- Footer -->
    <footer>

        <div>ESCHOOL</div>

        <div class="footer-links">
            <a href="#">Terms & Conditions</a>
            <a href="#">Customer Support</a>
        </div>

    </footer>

</body>
</html>