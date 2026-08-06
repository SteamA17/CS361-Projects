<?php 
    session_start();

    if(!isset($_SESSION['id'])){
        header("Location: index.php");
        exit();
    }

    include 'connect.php';

    $user_id = $_SESSION['id'];

    // Fetch user data
    $smt = $conn->prepare("SELECT * FROM users1 WHERE id= ?");
    $smt->bind_param("i", $user_id);
    $smt->execute();

    $result = $smt->get_result();
    $user = $result->fetch_assoc();

    // Save changes when form is submitted
    if(isset($_POST['update'])){
        $firstName = $_POST['firstName'];
        $surName = $_POST['surName'];
        $email = $_POST['email'];

        $update_smt = $conn->prepare("UPDATE users1 SET firstName=?, surName=?, email=? WHERE id=?");
        $update_smt->bind_param("sssii", $firstName, $surName, $email, $user_id);
        $update_smt->execute();
        $update_smt->close();

        // Redirect to avoid resubmission
        header("Location: profile.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL | User-Profile</title>

    <link rel="stylesheet" href="../Frontend/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #3892ce;
            box-shadow: 0 0 5px rgba(56,146,206,0.3);
        }

        .update-btn {
            width: 100%;
            padding: 12px;
            background-color: #3892ce;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .update-btn:hover {
            background-color: #287bb3;
            text-decoration: none;
            color: white;
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

        <!-- Main -->
         <main>
            <h2 style="margin-bottom: 25px; color: #3892ce;">My Profile</h2>
            <form method="POST" >
                <input
                    type="text"
                    name="firstName"
                    value="<?php echo htmlspecialchars($user['firstName']); ?>">
                <br><br>

                <input
                    type="text"
                    name="surName"
                    value="<?php echo htmlspecialchars($user['surName']); ?>">
                <br><br>

                <input
                    type="email"
                    name="email"
                    value="<?php echo htmlspecialchars($user['email']); ?>">
                <br><br>

                <button type="submit" name="update" class="update-btn">
                    Save Changes
                </button>

            </form>
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