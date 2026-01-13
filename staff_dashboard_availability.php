<?php
session_start();
// Generate random csrf token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
// Only allow logged-in users
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// Include connection to database
include 'database_connect.php';

// Get filter parameters
$filterDay = $_GET['filter_day'] ?? '';
$filterMonth = $_GET['filter_month'] ?? '';

// Build date filter condition
$dateCondition = "";
if (!empty($filterDay)) {
    $dateCondition = " AND DATE(arrival) = '" . $connect->real_escape_string($filterDay) . "'";
} elseif (!empty($filterMonth)) {
    $dateCondition = " AND DATE_FORMAT(arrival, '%Y-%m') = '" . $connect->real_escape_string($filterMonth) . "'";
}

// Load bookings from database for display
$bookings = [];
$result = $connect->query("SELECT cabinType, status FROM booking WHERE 1=1" . $dateCondition);
// Get each data pair of rows
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
// Load cabins from database for display
$cabins = [];
$result2 = $connect->query("SELECT cabinType, quantity FROM cabin");
// Get each data pair of rows
if ($result2) {
    while ($row2 = $result2->fetch_assoc()) {
        $cabins[] = $row2;
    }
}
// Load total new cabins from database for display
$newCabins = [];
$result3 = $connect->query("SELECT cabinType, COUNT(*) AS total FROM booking WHERE status = 'new'" . $dateCondition . " GROUP BY cabinType");
// Get each data pair of rows
while ($row3 = $result3->fetch_assoc()) {
    $confirmedCabins[$row3['cabinType']] = (int)$row3['total'];
}
// Load total confirmed cabins from database for display
$confirmedCabins = [];
$result4 = $connect->query("SELECT cabinType, COUNT(*) AS total FROM booking WHERE status = 'confirmed'" . $dateCondition . " GROUP BY cabinType");
// Get each data pair of rows
while ($row4 = $result4->fetch_assoc()) {
    $confirmedCabins[$row4['cabinType']] = (int)$row4['total'];
}
// Load total checked in cabins from database for display
$checkedInCabins = [];
$result5 = $connect->query("SELECT cabinType, COUNT(*) AS total FROM booking WHERE status = 'checkedIn'" . $dateCondition . " GROUP BY cabinType");
// Get each data pair of rows
while ($row5 = $result5->fetch_assoc()) {
    $checkedInCabins[$row5['cabinType']] = (int)$row5['total'];
}
// Load total checked out cabins from database for display
$checkedOutCabins = [];
$result6 = $connect->query("SELECT cabinType, COUNT(*) AS total FROM booking WHERE status = 'checkedOut'" . $dateCondition . " GROUP BY cabinType");
// Get each data pair of rows
while ($row6 = $result6->fetch_assoc()) {
    $checkedOutCabins[$row6['cabinType']] = (int)$row6['total'];
}
// Load total cancelled cabins from database for display
$cancelledCabins = [];
$result7 = $connect->query("SELECT cabinType, COUNT(*) AS total FROM booking WHERE status = 'cancelled'" . $dateCondition . " GROUP BY cabinType");
// Get each data pair of rows
while ($row7 = $result7->fetch_assoc()) {
    $cancelledCabins[$row7['cabinType']] = (int)$row7['total'];
}
// Load total archived cabins from database for display
$archivedCabins = [];
$result8 = $connect->query("SELECT cabinType, COUNT(*) AS total FROM booking WHERE status = 'archived'" . $dateCondition . " GROUP BY cabinType");
// Get each data pair of rows
while ($row8 = $result8->fetch_assoc()) {
    $archivedCabins[$row8['cabinType']] = (int)$row8['total'];
}

// Close connection
$connect->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunny Spot Holidays- Staff Dashboard - Availability</title>
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
        .availability-details {
            width: 100%;
            height: 80vh;
            padding: 1rem;
            margin: 8rem auto;
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            background-color: white;
            font-family: roboto, sans-serif;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            position: relative;
            flex: 1;
        }

        h2 {
            text-align: center;
        }

        table {
            border: 1px solid #ccc;
            width: 100%;
            max-width: 1700px;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        th,
        td {
            border: 1px solid #ccc;
            text-align: center;
            background-color: white;
        }

        td {
            padding-block: 0.5rem;
        }

        table tbody tr:nth-child(even) td {
            background-color: rgba(95, 62, 4, 0.12);
        }

        th {
            background-color: rgba(95, 62, 4, 1);
            color: white;
            height: 50px;
            text-align: center;
        }

        th:nth-child(2),
        th:nth-child(3),
        th:nth-child(4),
        th:nth-child(5),
        th:nth-child(6),
        th:nth-child(7),
        th:nth-child(8),
        th:nth-child(9) {
            width: 160px;
        }

        .filter-form {
            width: 100%;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: rgba(95, 62, 4, 0.05);
            border-radius: 10px;
            border: 1px solid #ccc;
        }

        .filter-container {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .filter-group label {
            font-size: 0.9rem;
        }

        .filter-group input {
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            font-family: roboto, sans-serif;
            width: 200px;
            text-align: center;
        }

        .filter-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .filter-button {
            all: unset;
            padding: 0.5rem 1rem;
            background-color: rgba(255, 115, 0, 0.77);
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-family: roboto, sans-serif;
            text-align: center;
        }

        .filter-button:hover {
            background-color: rgba(255, 115, 0, 0.9);
        }

        .clear-filter {
            padding: 0.5rem 1rem;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-family: roboto, sans-serif;
            text-align: center;
            display: inline-block;
        }

        .clear-filter:hover {
            background-color: rgba(0, 0, 0, 1);
        }

        .logout {
            display: block;
            width: 100px;
            padding: 0.5rem 1rem;
            margin: 1rem auto;
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

        @media (max-width:1600px) {
            .table-container {
                width: 100%;
                overflow-x: auto;
            }

            .availability-details table {
                border-collapse: collapse;
                min-width: 1700px;
                table-layout: auto;
            }

            th,
            td {
                border: 1px solid #ccc;
                text-align: center;
            }
        }


        @media (max-width:768px) {
            th:first-child {
                width: 300px;
            }

            .filter-container input {
                height: 30px;
            }
        }
    </style>
    <script src="script.js" defer></script>
</head>

<body>
    <header>
        <div class="header-divider">
            <a href="index.php"><img src="images/sun.gif" alt="Sunny-logo" class="sunny-logo"></a>
            <div class="title-divider">
                <a href="index.php" class="title">
                    <h1>Sunny Spot Holidays</h1>
                </a>
                <h3>This is a mock website only!</h3>
            </div>
        </div>
        <nav>
            <ul>
                <li class="nav-booking"><a href="staff_dashboard_booking.php">Booking</a></li>
                <li class="nav-availability"><a href="staff_dashboard_availability.php" class="active">Availability</a>
                </li>
                <li class="nav-contact"><a href="staff_dashboard_contact.php">Contact</a></li>
                <li class="nav-cabin"><a href="staff_dashboard_cabin.php">Cabin</a></li>
                <li class="nav-inclusion"><a href="staff_dashboard_inclusion.php">Inclusion</a></li>
            </ul>
            <div class="hamburger-menu"><span></span><span></span><span></span></div>
        </nav>
    </header>
    <main>
        <div class="availability-details">
            <h2>Cabin Availability Summary</h2>

            <form method="GET" action="" class="filter-form">
                <div class="filter-container">
                    <div class="filter-group">
                        <label for="filter_day">Filter by Day:</label>
                        <input type="date" id="filter_day" name="filter_day"
                            value="<?php echo htmlspecialchars($filterDay); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="filter_month">Filter by Month:</label>
                        <input type="month" id="filter_month" name="filter_month"
                            value="<?php echo htmlspecialchars($filterMonth); ?>">
                    </div>
                    <div class="filter-buttons">
                        <button type="submit" class="filter-button">Apply Filter</button>
                        <a href="admin_dashboard_availability.php" class="clear-filter">Clear Filter</a>
                    </div>
                </div>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Cabin Type</th>
                            <th>Total Cabins</th>
                            <th>New</th>
                            <th>Confirmed</th>
                            <th>Checked In</th>
                            <th>Checked Out</th>
                            <th>Cancelled</th>
                            <th>Available</th>
                            <th>Archived</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cabins as $i => $cabin) : ?>
                            <?php
                            $totalCabins = (int)($cabin['quantity'] ?? 0);
                            $new = $newCabins[$cabin['cabinType']] ?? 0;
                            $confirmed = $confirmedCabins[$cabin['cabinType']] ?? 0;
                            $checkedIn = $checkedInCabins[$cabin['cabinType']] ?? 0;
                            $checkedOut = $checkedOutCabins[$cabin['cabinType']] ?? 0;
                            $cancelled = $cancelledCabins[$cabin['cabinType']] ?? 0;
                            $available = $totalCabins - $confirmed - $checkedIn - $checkedOut - $cancelled;
                            $archived = $archivedCabins[$cabin['cabinType']] ?? 0;
                            ?>
                            <tr>
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"
                                    readonly>
                                <td><?php echo htmlspecialchars($cabin['cabinType']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($totalCabins); ?>
                                </td>
                                <td><?php echo htmlspecialchars($new); ?></td>
                                <td><?php echo htmlspecialchars($confirmed); ?></td>
                                <td><?php echo htmlspecialchars($checkedIn); ?></td>
                                <td><?php echo htmlspecialchars($checkedOut); ?></td>
                                <td><?php echo htmlspecialchars($cancelled); ?></td>
                                <td><?php echo htmlspecialchars($available); ?></td>
                                <td><?php echo htmlspecialchars($archived); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <a class="logout" href="logout.php">Log Out</a>
        </div>
    </main>

    <footer>
        <p>
            <a href="https://www.google.com/maps?q=50+Melaleuca+Cres,+Tascott+NSW+2250" target="_blank">
                50 Melaleuca Cres, Tascott NSW 2250
            </a>
        </p>
        <p>© 2025 Copyright Sunny Spot Holidays</p>
        <li id="login"><a href="login.php">Admin</a></li>
        <img src="images/author.png" alt="author" class="author">
    </footer>
</body>

</html>
