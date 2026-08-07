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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCHOOL - Notes</title>
    <link rel="stylesheet" href="../Frontend/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        
        .section-title {
            font-size: 22px;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
        }
        
        .year-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .year-tabs button {
            padding: 8px 22px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            background: white;
            color: #555;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        
        .year-tabs button:hover {
            border-color: #3892ce;
            color: #3892ce;
            transform: translateY(-2px);
        }
        
        .year-tabs button.active-year {
            background: #3892ce;
            color: white;
            border-color: #3892ce;
        }
        
        .card-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            align-items: stretch;
        }
        
        .card-row .card {
            flex: 0 0 180px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .card-row .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .card-row .card .image {
            height: 120px;
            background: linear-gradient(135deg, #3892ce, #2d7bb3);
        }
        
        .card-row .card .text {
            padding: 15px;
            text-align: center;
            font-weight: 600;
            color: #1a1a2e;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
        }
        
        .card-row .arrow {
            display: flex;
            align-items: center;
            font-size: 30px;
            color: #3892ce;
            padding: 0 10px;
        }
        
        .business-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 25px 0 20px 0;
        }
        
        .business-header h3 {
            color: #1a1a2e;
            font-weight: 600;
            font-size: 18px;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }
        
        .add-btn {
            padding: 8px 20px;
            background: #3892ce;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .add-btn:hover {
            background: #2d7bb3;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(56, 146, 206, 0.3);
        }
        
        .add-btn i {
            margin-right: 6px;
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
        
        hr {
            border: none;
            border-top: 2px solid #e9ecef;
            margin: 25px 0;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .card-row .card {
                flex: 0 0 160px;
            }
        }
        
        @media (max-width: 768px) {
            .card-row {
                justify-content: center;
            }
            
            .card-row .card {
                flex: 0 0 140px;
            }
            
            .card-row .arrow {
                display: none;
            }
            
            .year-tabs {
                justify-content: center;
            }
            
            .business-header {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }
            
            .add-btn {
                width: 100%;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .card-row .card {
                flex: 0 0 100%;
                max-width: 200px;
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
            <a href="lecture_videos.php"><i class="fa-solid fa-video"></i> Lecture Videos</a>
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
                <a href="notes.php" class="active">Notes</a>
                <a href="tests.php">Tests</a>
                <a href="sessional.php">Sessional</a>
            </div>

            <!-- Materials Section -->
            <div class="section-title">
                <i class="fa-solid fa-book-open" style="color: #3892ce; margin-right: 10px;"></i>
                Materials
            </div>

            <div class="year-tabs">
                <button class="active-year">Year 1</button>
                <button>Year 2</button>
                <button>Year 3</button>
                <button>Year 4</button>
                <button>Year 5</button>
            </div>

            <div class="card-row">
                <div class="card">
                    <div class="image"></div>
                    <div class="text">MA110</div>
                </div>
                <div class="card">
                    <div class="image"></div>
                    <div class="text">CS110</div>
                </div>
                <div class="card">
                    <div class="image"></div>
                    <div class="text">PH110</div>
                </div>
                <div class="card">
                    <div class="image"></div>
                    <div class="text">CH110</div>
                </div>
                <div class="arrow">
                    <i class="fa-solid fa-angle-right"></i>
                </div>
            </div>

            <hr>

            <!-- My Business Section -->
            <div class="business-header">
                <h3><i class="fa-solid fa-chart-simple" style="color: #3892ce; margin-right: 8px;"></i>My Business</h3>
                <button class="add-btn"><i class="fa-solid fa-plus"></i> Add</button>
            </div>

            <div class="year-tabs">
                <button class="active-year">Year 1</button>
                <button>Year 2</button>
                <button>Year 3</button>
                <button>Year 4</button>
                <button>Year 5</button>
            </div>

            <div class="card-row">
                <div class="card">
                    <div class="image"></div>
                    <div class="text">CS150</div>
                </div>
                <div class="card">
                    <div class="image"></div>
                    <div class="text">CS120</div>
                </div>
                <div class="card">
                    <div class="image"></div>
                    <div class="text">CS130</div>
                </div>
                <div class="card">
                    <div class="image"></div>
                    <div class="text">MA320</div>
                </div>
                <div class="arrow">
                    <i class="fa-solid fa-angle-right"></i>
                </div>
            </div>
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