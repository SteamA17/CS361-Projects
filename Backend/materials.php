<?php
    session_start();

    include 'connect.php';
    

    //Fetching Materials
    $sql= "SELECT * FROM downloads";
    $result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL | Materials</title>

    <link rel="stylesheet" href="../Frontend/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        table {
            width: 70%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        a.download-btn {
            display: inline-block;
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a.download-btn:hover {
            background-color: #45a049;
        }

        .modal{
            display:none;
            position:fixed;
            z-index:1000;
            left:0;
            top:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,0.7);
        }

        .modal-content{
            background:white;
            width:80%;
            height:80%;
            margin:5% auto;
            padding:20px;
            border-radius:15px;
            overflow:hidden;
        }

        .close{
            float:right;
            font-size:30px;
            cursor:pointer;
            color:#3892ce;
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
            <h2 style="margin-bottom: 25px; color: #3892ce; text-align: center;">Available Materials</h2>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                </tr>
                <?php
                    if($result->num_rows>0){
                        while($row = $result->fetch_assoc()){
                            echo"<tr>
                                    <td>".htmlspecialchars($row['title'])."</td>
                                    <td>".htmlspecialchars($row['description'])."</td>
                                    <td>
                                        <button class='download-btn'
                                        onclick=\"openDocument('".$row['file_path']."')\">
                                        View
                                        </button>
                                    </td>
                                </tr>";
                        }
                    }else{
                        echo"<tr><td colspan='3'>No Materials Available</td></tr>";
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

    <!-- Document Viewer -->
    <div id="documentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDocument()">
                &times;
            </span>

            <iframe 
                id="documentViewer"
                src=""
                width="100%"
                height="90%">
            </iframe>
        </div>
    </div>

    <script>
        function openDocument(file){
            document.getElementById("documentModal").style.display = "block";
            document.getElementById("documentViewer").src = file;
        }

        function closeDocument(){
            document.getElementById("documentModal").style.display = "none";
            document.getElementById("documentViewer").src = "";
        }

    </script>
</body>
</html>

<?php $conn->close() ?>