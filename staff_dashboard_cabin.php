    <?php
    session_start();
    // Generate random csrf token
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    // Only allow logged-in users
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: admin/login");
        exit;
    }
    // Include connection to database
    include 'database_connect.php';
    // Load cabins from database for display
    $cabins = [];
    $result = $connect->query("SELECT * FROM cabin");
    // Get each data pair of rows
    while ($row = $result->fetch_assoc()) {
        $cabins[] = $row;
    }
    // Load inclusion from database for display
    $inclusions = [];
    $result2 = $connect->query("SELECT * FROM inclusion");
    // Get each data pair rows
    while ($row2 = $result2->fetch_assoc()) {
        $inclusions[] = $row2;
    }
    // Load cabin inclusion from database for display
    $cabinInclusions = [];
    $result3 = $connect->query("SELECT * FROM cabin_inclusion");
    // Get each data pair rows
    while ($row3 = $result3->fetch_assoc()) {
        $cabinInclusions[] = $row3;
    }
    // Declare empty message variable
    $messageCabin = "";
    $messageCabinSuccess = "";
    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $messageErr = "Security validation failed. Please try again.";
        } else {
            // INSERT new cabin
            if (!isset($_POST['cabinID']) && isset($_POST['cabinType'])) {
                $cabinType = $_POST['cabinType'];
                $cabinDescription = $_POST['cabinDescription'];
                $pricePerNight = (float)$_POST['pricePerNight'];
                $pricePerWeek = (float)$_POST['pricePerWeek'];
                if ($pricePerNight < 0 || $pricePerWeek < 0) {
                    $messageCabin = "Add new cabin unsuccessful!<br>Prices must not be negative.";
                } elseif (fmod($pricePerNight, 1) != 0 || fmod($pricePerWeek, 1) != 0) {
                    $messageCabin = "Add new cabin unsuccessful!<br>Prices must be a whole number.";
                } elseif ($pricePerWeek > $pricePerNight * 5) {
                    $messageCabin = "Add new cabin unsuccessful!<br>Price per week cannot be more than 5 times the price per night.";
                }
                if (isset($_FILES['photo']['name']) && $_FILES['photo']['error'] == 0) {
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    $fileType = $_FILES['photo']['type'];
                    $fileSize = $_FILES['photo']['size'];

                    if (!in_array($fileType, $allowedTypes)) {
                        $messageCabin = "Add new cabin unsuccessful!<br>Photo must be JPG, JPEG, or PNG.";
                    } elseif ($fileSize > 2 * 1024 * 1024) { // 2MB
                        $messageCabin = "Add new cabin unsuccessful!<br>Photo size must be less than 2MB.";
                    } else {
                        $uploadPhoto = "images/";
                        $photo = basename($_FILES['photo']['name']);
                        move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPhoto . $photo); // move_uploaded_file: move the photo to file. tmp_name: temporary name of the uploaded file given by PHP. If not use this temporary file, and just rely on the original filename (name), two or more users uploading files with the same name could overwrite each other’s files.
                    }
                } else {
                    $photo = 'testCabin.jpg'; // default/fallback
                }
                if (empty($messageCabin)) {
                    // Connect to database and prepare statement 
                    $cabinsTable = $connect->prepare(
                        "INSERT INTO cabin (cabinType, cabinDescription, pricePerNight, pricePerWeek, photo) VALUES (?, ?, ?, ?, ?)"
                    );
                    // Declare variable types
                    $cabinsTable->bind_param("ssdds", $cabinType, $cabinDescription, $pricePerNight, $pricePerWeek, $photo);
                    // Execute getting cabins data
                    $cabinsTable->execute();
                    // Get new id for the new cabin
                    $newCabinID = $connect->insert_id;
                    // Insert inclusions list for the new cabin
                    if (!empty($_POST['incID'])) {
                        // Use foreach as each cabin can have more than one inclusion
                        foreach ($_POST['incID'] as $incID) {
                            $incID = (int)$incID;
                            $cabinInclusion = $connect->prepare(
                                "INSERT INTO cabin_inclusion (cabinID, incID) VALUES ( ?, ?)"
                            );
                            $cabinInclusion->bind_param("ii", $newCabinID, $incID);
                            $cabinInclusion->execute();
                            $cabinInclusion->close();
                        }
                    }
                    // Close getting cabins data 
                    $cabinsTable->close();
                    // Successful updating message
                    $messageCabinSuccess = "New cabin inserted successfully!";
                }
            }
        }
    }
    // UPDATE cabins
    if (isset($_POST['cabinID'])) {
        foreach ($_POST['cabinID'] as $index => $id) {
            $cabinID = (int)$id;
            $cabinType = $_POST['cabinType'][$index];
            $cabinDescription = $_POST['cabinDescription'][$index];
            $pricePerNight = (float)$_POST['pricePerNight'][$index];
            $pricePerWeek = (float)$_POST['pricePerWeek'][$index];
            if ($pricePerNight < 0 || $pricePerWeek < 0) {
                $messageCabin = "Update cabin unsuccessful!<br>Prices must not be negative.";
            } elseif (fmod($pricePerNight, 1) != 0 || fmod($pricePerWeek, 1) != 0) {
                $messageCabin = "Add new cabin unsuccessful!<br>Prices must be a whole number.";
            } elseif ($pricePerWeek > $pricePerNight * 5) {
                $messageCabin = "Update cabin unsuccessful!<br>Price per week cannot be more than 5 times the price per night.";
            }
            if (isset($_FILES['photo']['name'][$index]) && $_FILES['photo']['error'][$index] == 0) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                $fileType = $_FILES['photo']['type'][$index];
                $fileSize = $_FILES['photo']['size'][$index];

                if (!in_array($fileType, $allowedTypes)) {
                    $messageCabin = "Update cabin unsuccessful!<br>Photo must be JPG, JPEG, or PNG.";
                    $photo = $_POST['photo_existing'][$index]; // keep old photo
                } elseif ($fileSize > 2 * 1024 * 1024) { // 2MB in bytes
                    $messageCabin = "Update cabin unsuccessful!<br>Photo size must be less than 2MB.";
                    $photo = $_POST['photo_existing'][$index]; // keep old photo
                } else {
                    $uploadPhoto = "images/";
                    $photo = basename($_FILES['photo']['name'][$index]);
                    move_uploaded_file($_FILES['photo']['tmp_name'][$index], $uploadPhoto . $photo);
                }
            } else {
                $photo = $_POST['photo_existing'][$index]; // keep old photo
            }
            if (empty($messageCabin)) {
                // Ensure there is at least one cabin
                if ($id > 0) {
                    // Connect to database and prepare statement 
                    $cabinsTable = $connect->prepare(
                        "UPDATE cabin 
            SET cabinType=?, cabinDescription=?, pricePerNight=?, pricePerWeek=?, photo=? 
            WHERE cabinID=?"
                    );
                    // To update cabin_inclusion table, because it is a bridge between cabin table  and inclusion table, so the best way is to delete all existing inclusions then insert new ones
                    $deleteCabinInclusion = $connect->prepare("DELETE FROM cabin_inclusion WHERE cabinID=?");
                    $deleteCabinInclusion->bind_param("i", $cabinID);
                    $deleteCabinInclusion->execute();
                    $deleteCabinInclusion->close();
                    if (!empty($_POST['incID'][$cabinID])) {
                        // Use foreach as each cabin can have more than one inclusion
                        foreach ($_POST['incID'][$cabinID] as $incID) {
                            $incID = (int)$incID;
                            $cabinInclusion = $connect->prepare("INSERT INTO cabin_inclusion (cabinID, incID) VALUES (?, ?)");
                            $cabinInclusion->bind_param("ii", $cabinID, $incID);
                            $cabinInclusion->execute();
                            $cabinInclusion->close();
                        }
                    }
                    // Declare variable types
                    $cabinsTable->bind_param("ssddsi", $cabinType, $cabinDescription, $pricePerNight, $pricePerWeek, $photo, $cabinID);
                    // Execute getting cabins data
                    $cabinsTable->execute();
                    // Close getting cabins data 
                    $cabinsTable->close();
                    // Successful updating message
                    $messageCabinSuccess = "Cabin updated successfully!";
                }
            }
        }
    }

    // Reload cabins from database after updating
    $cabins = [];
    $result = $connect->query("SELECT * FROM cabin");
    // Get each data pair of rows
    while ($row = $result->fetch_assoc()) {
        $cabins[] = $row;
    }
    // Reload inclusion from database for display
    $inclusions = [];
    $result2 = $connect->query("SELECT * FROM inclusion");
    // Get each data pair rows
    while ($row2 = $result2->fetch_assoc()) {
        $inclusions[] = $row2;
    }
    // Reload cabin inclusion from database for display
    $cabinInclusions = [];
    $result3 = $connect->query("SELECT * FROM cabin_inclusion");
    // Get each data pair rows
    while ($row3 = $result3->fetch_assoc()) {
        $cabinInclusions[] = $row3;
    }



    // Handle form submission - delete cabin
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
        $deleteId = (int)$_POST['delete_id'];
        $deleteCabinInclusion = $connect->prepare("DELETE FROM cabin_inclusion WHERE cabinID=?");
        $deleteCabinInclusion->bind_param("i", $deleteId);
        $deleteCabinInclusion->execute();
        $deleteCabinInclusion->close();
        $deleteCabin = $connect->prepare("DELETE FROM cabin WHERE cabinID=?");
        $deleteCabin->bind_param("i", $deleteId);
        if ($deleteCabin->execute()) {
            $messageCabinSuccess = "Selected cabin deleted successfully!";
        } else {
            $messageCabin = "Error deleting cabin";
            $deleteCabin->error;
        }
        $deleteCabin->close();
        // Reload cabins from database after updating
        $cabins = [];
        $result = $connect->query("SELECT * FROM cabin");
        // Get each data pair of rows
        while ($row = $result->fetch_assoc()) {
            $cabins[] = $row;
        }
    }
    // Close connection
    $connect->close();
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sunny Spot Holidays - Staff Dashboard - Cabin</title>
        <link href="styles.css?v=<?php echo time(); ?>" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Arima:wght@100..700&family=Dancing+Script:wght@400..700&display=swap"
            rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link
            href="https://fonts.googleapis.com/css2?family=Arima:wght@100..700&family=Dancing+Script:wght@400..700&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
            rel="stylesheet">
        <style>
        .cabin-details {
            width: 100%;
            max-width: 1400px;
            height: 100%;
            padding: 2rem;
            margin: 7rem auto 3rem auto;
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            background-color: white;
            font-family: roboto, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .cabin-header {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .cabin-selection {
            width: 350px;
            height: 50px;
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 6px;
            font-size: 1rem;
            padding: 0 0.5rem;
            margin-bottom: 1rem;
            color: black;
        }

        .cabin-display {
            width: 100%;
            max-width: 900px;
            height: 100%;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-left: -2rem;
        }

        .add-new-cabin {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-left: -2rem;
        }

        h2 {
            text-align: center;
            margin-left: 1rem;
        }

        label {
            width: 30%;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            height: 100%;
            font-size: 1.1rem;
            border: none;
            text-align: center;
        }

        textarea {
            padding: 0.5rem 0.3rem;
            text-align: center;
        }

        button:hover {
            background-color: rgba(255, 115, 0, 0.9);
        }

        .message {
            color: red;
            text-align: center;
            font-weight: bold;
            margin: 1rem;
            margin-bottom: 2rem;
        }

        .messageSuccess {
            color: green;
            text-align: center;
            font-weight: bold;
            margin: 1rem;
            margin-bottom: 2rem;
        }

        .form-divider {
            display: flex;
            align-items: center;
            width: 900px;
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 6px;
            padding: 0.7rem;

        }

        .cabin-top {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 1rem;
            gap: 1rem;
        }

        .form-divider select {
            border: 1px solid rgba(0, 0, 0, 0.15);
            font-weight: 600;
            padding: 0.5rem;
            border-radius: 6px;
        }

        .form-divider select option {
            font-weight: 600;
        }

        .form-divider:last-child {
            display: flex;
            align-items: flex-start;
            flex-direction: column;
        }

        .photo-container {
            display: flex;
            flex-direction: column;
            margin: 0.5rem auto;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .form-description {
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin: 1.5rem 0 1rem 2rem;
            width: 900px;
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 6px;
            padding: 1rem;

        }

        .inclusion {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 300px;
            margin: 2rem auto 1rem auto;
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 6px;
        }

        .inclusion-wrapper {
            margin-left: 2rem;
        }

        .inclusion table {
            border-collapse: collapse;
        }

        .inclusion table th,
        .inclusion table td {
            width: 100%;
            border: 1px solid #ccc;
            padding: 1rem;
        }

        .inclusion table th:first-child {
            border-top: none;
            border-left: none;
        }

        .inclusion table th:last-child {
            border-top: none;
            border-right: none;
        }

        .inclusion table tr:last-child td {
            border-bottom: none;
        }

        .inclusion table tr td:first-child {
            border-left: none;
        }

        .inclusion table tr td:last-child {
            border-right: none;
        }

        .logout {
            display: block;
            width: 100px;
            padding: 0.5rem 1rem;
            margin: 1rem;
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: white;
            background-color: rgba(95, 62, 4, 1);
        }

        .logout:hover {
            background-color: rgba(70, 45, 3, 1);
        }

        .add-cabin,
        .update-button,
        .delete-button,
        .back {
            display: flex;
            width: 140px;
            height: 40px;
            padding: 0 0.5rem;
            margin: 1rem;
            margin-left: 3rem;
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            text-align: center;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            color: white;
            background-color: rgba(255, 115, 0, 0.77);
            cursor: pointer;
            font-size: 1rem;
        }

        .button-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .add-cabin {
            background-color: rgba(37, 170, 4, 0.77);
            width: 165px;
            min-height: 40px;
            padding: 0 0.5rem;
            margin-left: 1rem;
        }

        .add-cabin:hover {
            background-color: rgba(24, 110, 3, 0.77);
        }

        #update-button:hover {
            background-color: rgba(255, 115, 0, 1);
        }

        .back {
            width: 100px;
            background-color: rgba(37, 170, 4, 0.77);
        }

        .back:hover {
            background-color: rgba(24, 110, 3, 0.77);
        }

        .delete-button {
            background-color: rgba(0, 0, 0, 0.7);
        }

        .delete-button:hover {
            background-color: rgba(0, 0, 0, 1);
        }

        @media (max-width: 900px) {

            .form-description,
            .form-divider {
                width: 700px;
            }
        }

        @media (max-width: 768px) {

            .form-description,
            .form-divider {
                width: 500px;
            }
        }

        @media (max-width: 568px) {

            .form-description,
            .form-divider {
                width: 350px;
            }

            .photo-container img {
                width: 100px;
                height: 80px;
            }

            .form-description {
                height: 180px;
            }

            .inclusion table td:nth-child(2) {
                text-align: center;
                vertical-align: middle;
            }

            .inclusion table input[type="checkbox"] {
                width: 20px;
                height: 20px;
            }
        }
        </style>
        <script src="script.js" defer></script>
    </head>

    <body>
        <header>
            <div class="header-divider">
                <a href="home"><img src="images/sun.gif" alt="Sunny-logo" class="sunny-logo"></a>
                <div class="title-divider">
                    <a href="home" class="title">
                        <h1>Sunny Spot Holidays</h1>
                    </a>
                    <h3>This is a mock website only!</h3>
                </div>
            </div>
            <nav>
                <ul>
                    <li class="nav-booking"><a href="staff_dashboard_booking">Booking</a></li>
                    <li class="nav-availability"><a href="staff_dashboard_availability">Availability</a>
                    </li>
                    <li class="nav-contact"><a href="staff_dashboard_contact">Contact</a></li>
                    <li class="nav-cabin"><a href="staff_dashboard_cabin" class="active">Cabin</a></li>
                    <li class="nav-inclusion"><a href="staff_dashboard_inclusion">Inclusion</a></li>
                </ul>
                <div class="hamburger-menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </header>
        <main>
            <div class="cabin-details">
                <h2>Cabins Management</h2>
                <div class="cabin-header">
                    <select id="cabin-selection" class="cabin-selection" name="cabin-selection"
                        aria-label="Cabin Selection">
                        <option value="" disabled selected>Select a cabin type</option>
                        <?php foreach ($cabins as $cabin): ?>
                        <option value="<?php echo htmlspecialchars($cabin['cabinID']); ?>">
                            <?php echo htmlspecialchars($cabin['cabinType']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!empty($messageCabin)): ?>
                <p class="message"><?php echo htmlspecialchars($messageCabin); ?></p>
                <?php endif; ?>
                <?php if (!empty($messageCabinSuccess)): ?>
                <p class="messageSuccess"><?php echo htmlspecialchars($messageCabinSuccess); ?></p>
                <?php endif; ?>
                <!-- ADD NEW CABIN -->
                <div class="add-new-cabin">
                    <form method="POST" enctype="multipart/form-data"
                        onsubmit="return confirm('Are you sure about the change?');">
                        <!-- Use enctype="multipart/form-data" in case there is a file uploading -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="cabin-top">
                            <div class="form-divider">
                                <label>Cabin Type: </label>
                                <input type="text" name="cabinType" required>
                            </div>
                            <div class="form-divider">
                                <label>Price Per Night: </label>
                                <input type="number" id="pricePerNight" name="pricePerNight" step="1"
                                    class="pricePerNight" required>
                                <select class="newAdjustNight">
                                    <option value="" disabled selected>Auto Adjust</option>
                                    <option value="2">200%</option>
                                    <option value="1.75">175%</option>
                                    <option value="1.5">150%</option>
                                    <option value="1.25">125%</option>
                                    <option value="1">100%</option>
                                    <option value="0.75">75%</option>
                                    <option value="0.5">50%</option>
                                </select>
                            </div>
                            <div class="form-divider">
                                <label>Price Per Week: </label>
                                <input type="number" id="pricePerWeek" name="pricePerWeek" step="1" class="pricePerWeek"
                                    required>
                                <select class="newAdjustWeek">
                                    <option value="" disabled selected>Auto Adjust</option>
                                    <option value="2">200%</option>
                                    <option value="1.75">175%</option>
                                    <option value="1.5">150%</option>
                                    <option value="1.25">125%</option>
                                    <option value="1">100%</option>
                                    <option value="0.75">75%</option>
                                    <option value="0.5">50%</option>
                                </select>
                            </div>
                            <div class="form-divider">
                                <label>Photo: </label>
                                <div class="photo-container">
                                    <input type="file" name="photo">
                                </div>
                            </div>
                        </div>
                        <div class="form-description">
                            <label>Description: </label>
                            <textarea name="cabinDescription" required></textarea>
                        </div>
                        <div class="inclusion-wrapper">
                            <div class="inclusion">
                                <table>
                                    <tr>
                                        <th>Inclusion</th>
                                        <th>Available</th>
                                    </tr>
                                    <?php foreach ($inclusions as $inclusion) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($inclusion['incName']) ?></td>
                                        <td><input type="checkbox" name="incID[]"
                                                value="<?php echo htmlspecialchars($inclusion['incID']); ?>">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                </div>
                <button type="submit" class="add-cabin">Add New Cabin</button>
                </form>
                <!-- EXISTING CABINS -->
                <?php foreach ($cabins as $cabin): ?>
                <!-- data-id is an HTML attribute that can hold a dynamic id for a loop, while, for to use in Javascript-->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="cabin-display" data-id="<?php echo $cabin['cabinID']; ?>">
                    <form method="POST" enctype="multipart/form-data"
                        onsubmit="return confirm('Are you sure about the change?');">
                        <!-- Use enctype="multipart/form-data" in case there is a file uploading -->
                        <div class="cabin-top">
                            <input type="hidden" name="cabinID[]"
                                value="<?php echo htmlspecialchars($cabin['cabinID']); ?>">
                            <div class="form-divider">
                                <label>Cabin Type: </label>
                                <input type="text" name="cabinType[]"
                                    value="<?php echo htmlspecialchars($cabin['cabinType']); ?>" required>
                            </div>
                            <div class="form-divider">
                                <label>Price Per Night: </label>
                                <input type="number" id="oldPricePerNight" name="pricePerNight[]"
                                    value="<?php echo htmlspecialchars($cabin['pricePerNight']); ?>" step="1" required>
                                <select class="oldAdjustNight">
                                    <option value="" disabled selected>Auto Adjust</option>
                                    <option value="2">200%</option>
                                    <option value="1.75">175%</option>
                                    <option value="1.5">150%</option>
                                    <option value="1.25">125%</option>
                                    <option value="1">100%</option>
                                    <option value="0.75">75%</option>
                                    <option value="0.5">50%</option>
                                </select>
                            </div>
                            <div class="form-divider">
                                <label>Price Per Week: </label>
                                <input type="number" id="oldPricePerWeek" name="pricePerWeek[]"
                                    value="<?php echo htmlspecialchars($cabin['pricePerWeek']); ?>" step="1" required>
                                <select class="oldAdjustWeek">
                                    <option value="" disabled selected>Auto Adjust</option>
                                    <option value="2">200%</option>
                                    <option value="1.75">175%</option>
                                    <option value="1.5">150%</option>
                                    <option value="1.25">125%</option>
                                    <option value="1">100%</option>
                                    <option value="0.75">75%</option>
                                    <option value="0.5">50%</option>
                                </select>
                            </div>
                            <div class="form-divider">
                                <label>Photo: </label>
                                <div class="photo-container">
                                    <img src="images/<?php echo htmlspecialchars($cabin['photo']); ?>" alt="Cabin Photo"
                                        width="400px" height="300px">
                                    <input type="file" name="photo[]">
                                    <input type="hidden" name="photo_existing[]"
                                        value="<?php echo htmlspecialchars($cabin['photo']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-description">
                            <label>Description: </label>
                            <textarea name="cabinDescription[]"
                                required><?php echo htmlspecialchars($cabin['cabinDescription']); ?></textarea>
                        </div>
                        <div class="inclusion-wrapper">
                            <div class="inclusion">
                                <table>
                                    <tr>
                                        <th>Inclusion</th>
                                        <th>Available</th>
                                    </tr>
                                    <?php foreach ($inclusions as $inclusion) :
                                            // Check if this inclusion belongs to the current cabin
                                            $checked = '';
                                            foreach ($cabinInclusions as $cabinInclusion) {
                                                if ($cabinInclusion['cabinID'] == $cabin['cabinID'] && $cabinInclusion['incID'] == $inclusion['incID']) {
                                                    $checked = 'checked';
                                                    break;
                                                }
                                            }
                                        ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($inclusion['incName']); ?></td>
                                        <td>
                                            <!--name="incID[<?php echo $cabin['cabinID']; ?>][]" means getting the id of cabin in cabins to insert inclusion, [] at the end to create an array so each cabin can have more than one inclusion-->
                                            <input type="checkbox"
                                                name="incID[<?php echo htmlspecialchars($cabin['cabinID']); ?>][]"
                                                value="<?php echo htmlspecialchars($inclusion['incID']); ?>"
                                                <?php echo htmlspecialchars($checked); ?>>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                        <div class="button-wrapper"><button type="submit" class="update-button"
                                value="<?php echo htmlspecialchars($cabin['cabinID']); ?>">Update
                                Cabin</button></div>
                    </form>
                    <a class="back" href="admin_dashboard_cabin">Back</a>
                    <form method="POST" onsubmit="return confirm('Are you sure about deleting this cabin?');">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="delete_id"
                            value="<?php echo htmlspecialchars($cabin['cabinID']); ?>">
                        <div class="button-wrapper"><button type="submit" class="delete-button"
                                value="<?php echo $cabin['cabinID']; ?>">Delete Cabin</button></div>
                    </form>
                </div>
                <?php endforeach; ?>
                <a class="logout" href="admin/logout">Log Out</a>
            </div>
        </main>
        <footer>
            <p>
                <a href="https://www.google.com/maps?q=50+Melaleuca+Cres,+Tascott+NSW+2250" target="_blank">
                    50 Melaleuca Cres, Tascott NSW 2250
                </a>
            </p>
            <p>© 2025 Copyright Sunny Spot Holidays</p>
            <a id="login" href="admin/login">Admin</a>
            <img src="images/author.png" alt="author" class="author">
        </footer>
    </body>

    </html>
