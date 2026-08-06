<?php
 session_start();

 if(!isset($_SESSION['id'])){
    header("Location: index.php");
    exit();
 }

 include "connect.php";

 if(isset($_POST['upload'])){

    //Get From Data
    $uploadedBy = $_SESSION['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    //File Information
    $fileName = $_FILES['file']['name'];
    $fileType = $_FILES['file']['type'];
    $tmpName = $_FILES['file']['tmp_name'];


    //Upload Location
    $filePath = "uploads/".$fileName;

    //Move Files To Upload Folder
    if(move_uploaded_file($tmpName,$filePath)){
        $sql = "INSERT INTO downloads(title,description,file_name,file_path,file_type,uploaded_by) 
        VALUES('$title',
        '$description',
        '$fileName',
        '$filePath',
        '$fileType',
        '$uploadedBy'
        )";

        if(mysqli_query($conn,$sql)){
            echo "File Uploaded Successfully";
        }else{
            echo "Database error: ".mysqli_error($conn);
        }
    }else{
        echo "File Upload Failed";
    }
 }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL | Upload files</title>

    <link rel="stylesheet" href="../Frontend/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .uploads{
            width: 90%;
            margin: 30px auto;
            background: #fff;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,.1);
        }

        .uploads form{
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .uploads input[type="text"],
        .uploads textarea,
        .uploads input[type="file"]{
            width: 100%;
            padding: 14px;
            font-size: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            transition: .3s;
        }

        .uploads textarea{
            resize: vertical;
            min-height: 130px;
        }

        .uploads input:focus,
        .uploads textarea:focus{
            outline: none;
            border-color: #3892ce;
            box-shadow: 0 0 8px rgba(56,146,206,.2);
        }

        .uploads input[type="file"]{
            background: #f8f9fa;
            cursor: pointer;
        }

        .uploads button{
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #3892ce;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: .3s;
        }

        .uploads button:hover{
            background: #287bb3;
            transform: translateY(-2px);
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
            <a href="lecture_videos.php"><i class="fa-solid fa-video"></i> Lecture Videos</a>
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

            <h2 class="growth-title" style="margin-bottom: 25px; color: #3892ce;">Upload File</h2>

            <div class="uploads">
                <form action="uploads.php" method="POST" enctype="multipart/form-data">
                    <input type="text" name="title" placeholder="Enter file title" required>

                    <textarea name="description" id="Description" placeholder="Enter file description" required></textarea>

                    <input type="file" name="file" placeholder="Select File" required>

                    <button type="submit" name="upload">Submit</button>
                </form>
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