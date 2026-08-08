<?php

session_start();

require_once 'connect.php';


// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}


// Session timeout
if (
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity'] > 1800)
) {
    session_destroy();
    header("Location: index.php?timeout=1");
    exit();
}

$_SESSION['last_activity'] = time();


// Logged-in student
$userId = $_SESSION['id'];


// Get user stats for sidebar
$stmt = $conn->prepare("SELECT COUNT(*) AS total
    FROM downloads
    WHERE uploaded_by = ?
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$uploadCount = $stmt->get_result()->fetch_assoc()['total'];

$stmt->close();


// --------------------------------------------------
// SELECTED YEAR
// --------------------------------------------------

$selectedYear = isset($_GET['year'])
    ? $_GET['year']
    : 'Year 1';


// Allowed years
$allowedYears = [
    'Year 1',
    'Year 2',
    'Year 3',
    'Year 4',
    'Year 5'
];


// Make sure selected year is valid
if (!in_array($selectedYear, $allowedYears, true)) {
    $selectedYear = 'Year 1';
}


// --------------------------------------------------
// SELECTED DOCUMENT TYPE
// --------------------------------------------------

$selectedType = isset($_GET['type'])
    ? strtolower(trim($_GET['type']))
    : 'notes';


// Allowed document types
$allowedTypes = [
    'notes',
    'test',
    'sessional'
];


// Make sure selected type is valid
if (!in_array($selectedType, $allowedTypes, true)) {
    $selectedType = 'notes';
}


// --------------------------------------------------
// PAGE TITLE
// --------------------------------------------------

$typeTitles = [
    'notes' => 'Notes',
    'test' => 'Tests',
    'sessional' => 'Sessional'
];

$pageTitle = $typeTitles[$selectedType];


// --------------------------------------------------
// GET ONLY DOCUMENTS DOWNLOADED BY THIS STUDENT
// FOR THE SELECTED YEAR AND DOCUMENT TYPE
// --------------------------------------------------

$stmt = $conn->prepare("SELECT
        d.id,
        d.title,
        d.description,
        d.year,
        d.document_type,
        d.file_name,
        d.file_path,
        d.file_type,
        d.upload_date,
        ud.downloaded_at

    FROM user_downloads ud

    INNER JOIN downloads d
        ON ud.download_id = d.id

    WHERE ud.user_id = ?
      AND d.year = ?
      AND LOWER(TRIM(d.document_type)) = ?

    ORDER BY ud.downloaded_at DESC
");


$stmt->bind_param(
    "iss",
    $userId,
    $selectedYear,
    $selectedType
);

$stmt->execute();

$result = $stmt->get_result();

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
        .materials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .material-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            position: relative;
        }
        
        .material-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .material-card .file-icon {
            font-size: 40px;
            color: #3892ce;
            margin-bottom: 10px;
        }
        
        .material-card a {
            color: #4a4e52;
            font-size: 20px;
            text-decoration: none;
            transition: color 0.2s ease;
            margin: 10px 0;
            line-height: 1.5;
            font-family: 'Poppins', sans-serif;
        }

        .material-card .material-title {
            color: #4a4e52;
            font-size: 15px;
            text-decoration: none;
            transition: color 0.2s ease;
            margin: 10px 0;
            line-height: 1.5;
            font-family: 'Poppins', sans-serif;
        }
        
        .material-card .meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 13px;
            color: #888;
            font-family: 'Poppins', sans-serif;
        }
        
        .material-card .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

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

        .year-tabs a {
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
            color: #555;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s ease;
        }

        .year-tabs a:hover {
            background: #e9ecef;
        }

        .year-tabs a.active-year {
            background: #3892ce;
            color: white;
            border-color: #3892ce;
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
            <!--  MAIN TABS  --> 
            <div class="tabs"> 
                <a href="dashboard.php"> Dashboard </a> 
                <a href="notes.php?year=<?php echo urlencode($selectedYear);?>&type=notes" class="<?php echo ($selectedType === 'notes') ? 'active' : ''; ?>" >
                     Notes 
                </a> 
                <a href="tests.php?year=<?php echo urlencode($selectedYear);?>&type=test" class="<?php echo ($selectedType === 'test') ? 'active' : ''; ?>" >
                     Tests 
                </a> 
                <a href="sessional.php?year=<?php echo urlencode($selectedYear); ?>&type=sessional" class="<?php echo ($selectedType === 'sessional') ? 'active' : ''; ?>" >
                     Sessional 
                </a> 
            </div> 
            
            <!--  TITLE --> 
            <div class="section-title"> 
                <i class="fa-solid fa-book-open" style="color: #3892ce; margin-right: 10px;" ></i>
                 My <?php echo htmlspecialchars($pageTitle); ?> 
            </div> 
              
            <!--  YEAR TABS  --> 
            <div class="year-tabs"> 
                <?php foreach ($allowedYears as $year): ?> 
                    <a href="?year=<?php echo urlencode($year); 
                        ?>&type=<?php echo urlencode($selectedType); ?>" class="<?php echo ($selectedYear === $year) ? 'active-year' : ''; ?>" >
                        
                        <?php echo htmlspecialchars($year); ?> 
                    </a> 
                <?php endforeach; ?> 
            </div> 
            
            <!--  SELECTED YEAR + TYPE  --> 
            <h3 style=" color: #3892ce; margin: 25px 0 15px; font-family: 'Poppins', sans-serif; " > 
                <?php echo htmlspecialchars($selectedYear); ?> 
                <?php echo htmlspecialchars($pageTitle); ?> 
            </h3> 
            
            <!--  DOCUMENTS  --> 
            <div class="materials-grid"> 
                <?php if ($result->num_rows > 0): ?> 
                    <?php while ($row = $result->fetch_assoc()): ?> 
                        <div class="material-card"> 
                            <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="material-link" >
                                <div class="file-icon"> 
                                    <?php $icon = 'fa-file'; 
                                        if ( strpos( strtolower($row['file_type']), 'pdf' ) !== false ) { $icon = 'fa-file-pdf'; 
                                        } elseif ( strpos( strtolower($row['file_type']), 'word' ) !== false ) 
                                        { $icon = 'fa-file-word'; 
                                        } elseif ( strpos( strtolower($row['file_type']), 'powerpoint' ) !== false ) 
                                        { $icon = 'fa-file-powerpoint'; 
                                        } elseif ( strpos( strtolower($row['file_type']), 'excel' ) !== false ) 
                                        { $icon = 'fa-file-excel'; 
                                        } elseif ( strpos( strtolower($row['file_type']), 'image' ) !== false ) 
                                        { $icon = 'fa-file-image'; } ?> <i class="fa-solid <?php echo $icon; ?>"></i> 
                                </div> 
                                
                                <div class="material-title"> 
                                    <?php echo htmlspecialchars($row['title']);
                                    echo " ";
                                    echo htmlspecialchars($row['description']); ?> 
                                </div> 
                            </a> 
                        </div> 
                    <?php endwhile; ?> 
                    
                    <?php else: ?> 
                        
                <!--  NO DOCUMENTS  --> 
                <div class="no-notes"> 
                    <i class="fa-solid fa-folder-open"></i> <h3> No 
                        <?php echo htmlspecialchars($pageTitle); ?> for 
                        <?php echo htmlspecialchars($selectedYear); ?> </h3> 
                        <p> Downloaded <?php echo strtolower(htmlspecialchars($pageTitle)); ?> for 
                            <?php echo htmlspecialchars($selectedYear); ?> will appear here. 
                        </p> 
                </div> 
                <?php endif; ?> 
            </div> 
            
            <hr> 
            
            <!--  MY BUSINESS --> 
             <h3>My Business</h3> 
             <div class="cards"> 
                <div class="card"> 
                    <div class="image">

                    </div> 
                    <div class="text"> 
                        <a href="uploads.php" style=" text-decoration: none; color: inherit; " >
                             Upload New Material 
                        </a> 
                    </div> 
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