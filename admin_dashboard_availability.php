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
// Load bookings from database for display
$bookings = [];
$result = $connect->query("SELECT cabinType, status FROM booking");
// Get each data pair of rows
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
// Load cabins from database for display
$cabins = [];
$result2 = $connect->query("SELECT cabinType, quantity FROM cabin");
// Get each data pair of rows
while ($row2 = $result2->fetch_assoc()) {
    $cabins[] = $row2;
}
// Load total confirmed cabins from database for display
$confirmedCabins = [];
$result3 = $connect->query("SELECT cabinType, COUNT(*) AS total FROM booking WHERE status = 'confirmed' GROUP BY cabinType");
// Get each data pair of rows
while ($row3 = $result3->fetch_assoc()) {
    $confirmedCabins[$row3['cabinType']] = (int)$row3['total'];
}
// Load total checked in cabins from database for display
$checkedInCabins = [];
$result4 = $connect->query("SELECT cabinType, COUNT(*) AS total FROM booking WHERE status = 'checkedIn' GROUP BY cabinType");
// Get each data pair of rows
while ($row4 = $result4->fetch_assoc()) {
    $checkedInCabins[$row4['cabinType']] = (int)$row4['total'];
}
// Load total checked out cabins from database for display
$checkedOutCabins = [];
$result5 = $connect->query("SELECT cabinType, COUNT(*) AS total FROM booking WHERE status = 'checkedOut' GROUP BY cabinType");
// Get each data pair of rows
while ($row5 = $result5->fetch_assoc()) {
    $checkedOutCabins[$row5['cabinType']] = (int)$row5['total'];
}
// Load total cancelled cabins from database for display
$cancelledCabins = [];
$result6 = $connect->query("SELECT cabinType, COUNT(*) AS total FROM booking WHERE status = 'cancelled' GROUP BY cabinType");
// Get each data pair of rows
while ($row6 = $result6->fetch_assoc()) {
    $cancelledCabins[$row6['cabinType']] = (int)$row6['total'];
}
// Load total archived cabins from database for display
$archivedCabins = [];
$result7 = $connect->query("SELECT cabinType, COUNT(*) AS total FROM booking WHERE status = 'archived' GROUP BY cabinType");
// Get each data pair of rows
while ($row7 = $result7->fetch_assoc()) {
    $archivedCabins[$row7['cabinType']] = (int)$row7['total'];
}

// Close connection
$connect->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunny Spot Holidays- Admin Dashboard - Availability</title>
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
            max-width: 1400px;
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
        th:nth-child(8) {
            width: 160px;
        }


        #pageSlider {
            text-align: center;
            margin-top: 20px;
        }

        .page-button {
            all: unset;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            margin: 0 5px;
            cursor: pointer;
            background-color: white;
            color: black;
            transition: all 0.2s;
            font-family: roboto, sans-serif;
            padding: 0;
            font-size: 12px;
        }

        .page-button:hover {
            background-color: rgba(70, 45, 3, 1);
            color: white;
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
                <li class="nav-booking"><a href="admin_dashboard_booking.php">Booking</a></li>
                <li class="nav-availability"><a href="admin_dashboard_availability.php" class="active">Availability</a>
                </li>
                <li class="nav-contact"><a href="admin_dashboard_contact.php">Contact</a></li>
                <li class="nav-cabin"><a href="admin_dashboard_cabin.php">Cabin</a></li>
                <li class="nav-inclusion"><a href="admin_dashboard_inclusion.php">Inclusion</a></li>
                <li class="nav-account"><a href="admin_dashboard_account.php">Account</a></li>
                <li class="nav-log"><a href="admin_dashboard_log.php">Log</a></li>
            </ul>
            <div class="hamburger-menu"><span></span><span></span><span></span></div>
        </nav>
    </header>
    <main>
        <div class="availability-details">
            <h2>Cabin Availability Summary</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Cabin Type</th>
                            <th>Total Cabins</th>
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
                            $confirmed = $confirmedCabins[$cabin['cabinType']] ?? 0;
                            $checkedIn = $checkedInCabins[$cabin['cabinType']] ?? 0;
                            $checkedOut = $checkedOutCabins[$cabin['cabinType']] ?? 0;
                            $cancelled = $cancelledCabins[$cabin['cabinType']] ?? 0;
                            $available = $cabin['quantity'] - $confirmed - $checkedIn - $checkedOut - $cancelled;
                            $archived = $archivedCabins[$cabin['cabinType']] ?? 0;
                            ?>
                            <tr>
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"
                                    readonly>
                                <td><?php echo htmlspecialchars($cabin['cabinType']); ?>
                                </td>
                                <td><?php echo htmlspecialchars((int)$cabin['quantity']); ?>
                                </td>
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