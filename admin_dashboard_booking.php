<?php
session_start();
// Generate random csrf token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin/login.php");
    exit;
}
// Only allow logged-in users
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: admin/login.php");
    exit;
}
// Include connection to database
include 'database_connect.php';

// Get filter parameters
$filterDay = $_GET['filter_day'] ?? '';
$filterMonth = $_GET['filter_month'] ?? '';
$filterStatus = $_GET['filter_status'] ?? '';

// Build query based on filters
$query = "SELECT * FROM booking";
$conditions = [];

if (!empty($filterDay)) {
    $conditions[] = "DATE(arrival) = '" . $connect->real_escape_string($filterDay) . "'";
}

if (!empty($filterMonth)) {
    $conditions[] = "DATE_FORMAT(arrival, '%Y-%m') = '" . $connect->real_escape_string($filterMonth) . "'";
}

if (!empty($filterStatus)) {
    $conditions[] = "status = '" . $connect->real_escape_string($filterStatus) . "'";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$query .= " ORDER BY bookingAt DESC";

// Load bookings from database for display
$bookings = [];
$result = $connect->query($query);
// Get each data pair of rows
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
// Load cabin types from database for display
$cabinTypes = [];
$result2 = $connect->query("SELECT cabinType FROM cabin");
// Get each data pair of rows
while ($row2 = $result2->fetch_assoc()) {
    $cabinTypes[] = $row2['cabinType'];
}
// Declare empty message variable
$mainMessage = "";
$messageErr = "";
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $messageErr = "Security validation failed. Please try again.";
    } else {
        $id = (int)$_POST['id'];
        $action = $_POST['action'] ?? '';

        if ($action === 'update') {
            $firstName = $_POST['firstName'] ?? '';
            $lastName = $_POST['lastName'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            $cabinType = $_POST['cabinType'] ?? '';
            $arrival = $_POST['arrival'] ?? '';
            $departure = $_POST['departure'] ?? '';
            $numberOfGuest = $_POST['numberOfGuest'] ?? 0;
            $message = $_POST['message'] ?? '';
            $status = $_POST['status'] ?? 'new';

            $bookingsTable = $connect->prepare(
                "UPDATE booking
        SET firstName=?, lastName=?,phone=?, email=?, cabinType=?, arrival=?, departure=?, numberOfGuest=?, message=?, status=?
        WHERE id=?"
            );
            // Declare variable types
            $bookingsTable->bind_param("sssssssissi", $firstName, $lastName, $phone, $email, $cabinType, $arrival, $departure, $numberOfGuest, $message, $status, $id);
            // Execute getting bookings data
            $bookingsTable->execute();
            // Close getting bookings data 
            $bookingsTable->close();
            // Successful updating booking
            $mainMessage = "Booking updated successfully!";
        } elseif ($action === 'delete') {
            $bookingsTable = $connect->prepare("DELETE FROM booking WHERE id=?");
            $bookingsTable->bind_param("i", $id);
            // Execute delete bookings data
            $bookingsTable->execute();
            // Close getting bookings data 
            $bookingsTable->close();
            // Successful deleting booking
            $mainMessage = "Booking deleted successfully!";
        }
        // Reload cabins from database after updating
        $bookings = [];
        $result = $connect->query("SELECT * FROM booking ORDER BY bookingAt DESC");
        // Get each data pair of rows
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
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
    <title>Sunny Spot Holidays- Admin Dashboard - Booking</title>
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
    .booking-details {
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
        table-layout: fixed;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ccc;
        text-align: center;
        background-color: white;
    }

    th {
        background-color: rgba(95, 62, 4, 1);
        color: white;
        height: 50px;
        text-align: center;
    }

    th:first-child,
    th:nth-child(2) {
        width: 160px;
    }

    th:last-child,
    th:nth-child(11) {
        width: 70px;
    }

    th:nth-child(3) {
        width: 110px;
    }

    th:nth-child(4) {
        width: 280px;
    }

    th:nth-child(5) {
        width: 300px;
    }

    th:nth-child(6),
    th:nth-child(7) {
        width: 110px;
    }

    th:nth-child(8) {
        width: 80px;
    }

    th:nth-child(10) {
        width: 130px;
    }

    input,
    select,
    button {
        width: 100%;
        height: 100%;
        padding: 0.6rem 0;
        font-size: 1rem;
        border: none;
        text-align: center;
        text-align-last: center;
    }

    textarea {
        border: none;
        width: 100%;
        text-align: center;
    }

    select {
        text-align: center;
        text-align-last: center;
    }

    select option {
        text-align: center;
    }

    .status-selection option[value="new"] {
        color: red;
    }

    .status-selection option[value="confirmed"] {
        color: green;
    }

    .status-selection option[value="checkedIn"] {
        color: purple;
    }

    .status-selection option[value="checkedOut"] {
        color: blue;
    }

    .status-selection option[value="cancelled"] {
        color: red;
    }

    .status-selection option[value="archived"] {
        color: black;
    }

    #filter_status option[value="new"] {
        color: red;
    }

    #filter_status option[value="confirmed"] {
        color: green;
    }

    #filter_status option[value="checkedIn"] {
        color: purple;
    }

    #filter_status option[value="checkedOut"] {
        color: blue;
    }

    #filter_status option[value="cancelled"] {
        color: red;
    }

    #filter_status option[value="archived"] {
        color: black;
    }

    #pageSlider {
        text-align: center;
        margin-top: 20px;
    }

    .page-button {
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .page-button:hover {
        background-color: rgba(70, 45, 3, 1);
        color: white;
    }

    button {
        background-color: rgba(255, 115, 0, 0.77);
        color: white;
        cursor: pointer;
    }

    button:hover {
        background-color: rgba(255, 115, 0, 0.9);
    }


    .delete-button {
        background-color: rgba(0, 0, 0, 0.7);
    }

    .delete-button:hover {
        background-color: rgba(0, 0, 0, 1);
    }

    .message,
    .messageErr {
        color: green;
        text-align: center;
        font-weight: bold;
        margin-top: -1rem;
        margin-bottom: 1rem;
    }

    .messageErr {
        color: red;
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
        font-weight: bold;
        font-size: 0.9rem;
    }

    .filter-group input {
        padding: 0.5rem;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
        font-family: roboto, sans-serif;
        width: 200px;
    }

    .filter-group select {
        padding: 0.5rem;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
        font-family: roboto, sans-serif;
        width: 200px;
        cursor: pointer;
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

        .booking-details table {
            border-collapse: collapse;
            min-width: 1800px;
            table-layout: auto;
        }

        th,
        td {
            border: 1px solid #ccc;
            text-align: center;
        }

        input,
        textarea,
        select,
        button {
            height: 50px;
            padding: 0.2rem;
            font-size: 1rem;
        }

        .filter-container input,
        .filter-container select {
            height: 30px;
        }
    }
    </style>
    <script src="script.js" defer></script>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        let currentPage = 1;
        const rowPerPage = 5; // rows per page
        const maxPageButtons = 3; // maximum number of page numbers to display
        const rows = document.querySelectorAll("table tbody tr");
        const totalPages = Math.ceil(rows.length / rowPerPage);
        const pageSlider = document.getElementById("pageSlider");

        function showPage(page) {
            rows.forEach((row, index) => {

                // Show rows of each page accordingly
                row.style.display = (index >= (page - 1) * rowPerPage && index < page *
                        rowPerPage) ?
                    "table-row" : "none";

            });
            renderButtons(); // update buttons after showing page
        }

        function renderButtons() {
            pageSlider.innerHTML = ""; // clear previous buttons

            // Jump to beginning
            const first = document.createElement("button");
            first.textContent = "<<";
            first.className = "page-button";
            first.disabled = (currentPage === 1);
            first.addEventListener("click", () => {
                currentPage = 1;
                showPage(currentPage);
            });
            pageSlider.appendChild(first);

            // Previous arrow
            const prev = document.createElement("button");
            prev.textContent = "<";
            prev.className = "page-button";
            prev.disabled = (currentPage === 1); // disable if first page
            prev.addEventListener("click", () => {
                if (currentPage > 1) {
                    currentPage--;
                    showPage(currentPage);
                }
            });
            pageSlider.appendChild(prev);

            // Calculate start and end page for numbered buttons
            let startPage = Math.max(1, currentPage - Math.floor(maxPageButtons / 2));
            let endPage = startPage + maxPageButtons - 1;
            if (endPage > totalPages) {
                endPage = totalPages;
                startPage = Math.max(1, endPage - maxPageButtons + 1);
            }

            // Page number buttons
            for (let i = startPage; i <= endPage; i++) {
                const button = document.createElement("button");
                button.textContent = i;
                button.className = "page-button";
                if (i === currentPage) {
                    button.style.backgroundColor = "rgba(95, 62, 4, 1)";
                    button.style.color = "white";
                }
                button.addEventListener("click", () => {
                    currentPage = i;
                    showPage(currentPage);
                });
                pageSlider.appendChild(button);
            }

            // Next arrow
            const next = document.createElement("button");
            next.textContent = ">";
            next.className = "page-button";
            next.disabled = (currentPage === totalPages); // disable if last page
            next.addEventListener("click", () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    showPage(currentPage);
                }
            });
            pageSlider.appendChild(next);

            // Jump to end
            const last = document.createElement("button");
            last.textContent = ">>";
            last.className = "page-button";
            last.disabled = (currentPage === totalPages);
            last.addEventListener("click", () => {
                currentPage = totalPages;
                showPage(currentPage);
            });
            pageSlider.appendChild(last);
        }

        // Initialize
        showPage(currentPage);
    });

    // Set colors for status
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll(".status-selection").forEach(select => {
            const updateColor = () => {
                if (select.value === "new") select.style.color = "red";
                else if (select.value === "confirmed") select.style.color = "green";
                else if (select.value === "checkedIn") select.style.color = "purple";
                else if (select.value === "checkedOut") select.style.color = "blue";
                else if (select.value === "cancelled") select.style.color = "red";
                else select.style.color = "black";
            };
            updateColor();
            select.addEventListener("change", updateColor);
        });
    });

    // Set colors for filter status dropdown
    document.addEventListener("DOMContentLoaded", () => {
        const filterStatus = document.getElementById("filter_status");
        if (filterStatus) {
            const updateFilterColor = () => {
                if (filterStatus.value === "new") filterStatus.style.color = "red";
                else if (filterStatus.value === "confirmed") filterStatus.style.color = "green";
                else if (filterStatus.value === "checkedIn") filterStatus.style.color = "purple";
                else if (filterStatus.value === "checkedOut") filterStatus.style.color = "blue";
                else if (filterStatus.value === "cancelled") filterStatus.style.color = "red";
                else if (filterStatus.value === "archived") filterStatus.style.color = "black";
                else filterStatus.style.color = "black";
            };
            updateFilterColor();
            filterStatus.addEventListener("change", updateFilterColor);
        }
    });
    </script>
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
                <li class="nav-booking"><a href="admin_dashboard_booking.php" class="active">Booking</a></li>
                <li class="nav-availability"><a href="admin_dashboard_availability.php">Availability</a>
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
        <div class="booking-details">
            <h2>Booking Details</h2>
            <?php if (!empty($mainMessage)): ?>
            <p class="message"><?php echo htmlspecialchars($mainMessage); ?></p>
            <?php endif; ?>
            <?php if (!empty($messageErr)): ?>
            <p class="messageErr"><?php echo htmlspecialchars($messageErr); ?></p>
            <?php endif; ?>

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
                    <div class="filter-group">
                        <label for="filter_status">Filter by Status:</label>
                        <select id="filter_status" name="filter_status"
                            style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; font-family: roboto, sans-serif; width: 200px;">
                            <option value="">All Statuses</option>
                            <option value="new" <?php echo ($filterStatus === 'new') ? 'selected' : ''; ?>>New</option>
                            <option value="confirmed" <?php echo ($filterStatus === 'confirmed') ? 'selected' : ''; ?>>
                                Confirmed</option>
                            <option value="checkedIn" <?php echo ($filterStatus === 'checkedIn') ? 'selected' : ''; ?>>
                                Checked In</option>
                            <option value="checkedOut"
                                <?php echo ($filterStatus === 'checkedOut') ? 'selected' : ''; ?>>Checked Out</option>
                            <option value="cancelled" <?php echo ($filterStatus === 'cancelled') ? 'selected' : ''; ?>>
                                Cancelled</option>
                            <option value="archived" <?php echo ($filterStatus === 'archived') ? 'selected' : ''; ?>>
                                Archived</option>
                        </select>
                    </div>
                    <div class="filter-buttons">
                        <button type="submit" class="filter-button">Apply Filter</button>
                        <a href="admin_dashboard_booking.php" class="clear-filter">Clear Filter</a>
                    </div>
                </div>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Cabin Type</th>
                            <th>Arrival</th>
                            <th>Departure</th>
                            <th>Number of Guest</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Action</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $i => $booking): ?>
                        <tr>
                            <form method="POST" onsubmit="return confirm('Are you sure about the change?');">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($booking['id']); ?>">
                                <td><input type="text" name="firstName"
                                        value="<?php echo htmlspecialchars($booking['firstName']); ?>"
                                        placeholder="First Name">
                                </td>
                                <td><input type="text" name="lastName"
                                        value="<?php echo htmlspecialchars($booking['lastName']); ?>"
                                        placeholder="Last Name">
                                </td>
                                <td><input type="tel" name="phone"
                                        value="<?php echo htmlspecialchars($booking['phone']); ?>" placeholder="Phone"
                                        style="font-family: arial, sans-serif;" pattern="\d{9,11}" required
                                        oninvalid="this.setCustomValidity('Please enter a valid phone number')"
                                        oninput="this.setCustomValidity('')">
                                </td>
                                <td><input type="email" name="email"
                                        value="<?php echo htmlspecialchars($booking['email']); ?>" placeholder="Email"
                                        oninvalid="this.setCustomValidity('Please enter a valid email')"
                                        oninput="this.setCustomValidity('')">
                                </td>
                                <td>
                                    <select class="cabin-type" name="cabinType"
                                        style="border: none; font-size: 1rem; padding: 0 0.5rem; width: 100%;">
                                        <?php foreach ($cabinTypes as $cabinTypeOption): ?>
                                        <option value="<?php echo htmlspecialchars($cabinTypeOption); ?>"
                                            <?php echo (trim($cabinTypeOption) === trim($booking['cabinType'])) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cabinTypeOption); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <?php
                                    $DisplayArrival = !empty($booking['arrival']) ? date("Y-m-d", strtotime($booking['arrival'])) : '';
                                    $DisplayDeparture = !empty($booking['departure']) ? date("Y-m-d", strtotime($booking['departure'])) : '';
                                    ?>
                                <td><input type="date" name="arrival"
                                        value="<?php echo htmlspecialchars($DisplayArrival); ?>" placeholder="Arrival"
                                        style="font-family: arial, sans-serif;">
                                </td>
                                <td><input type="date" name="departure"
                                        value="<?php echo htmlspecialchars($DisplayDeparture); ?>"
                                        placeholder="Departure" style="font-family: arial, sans-serif;">
                                </td>
                                <td><input type="number" name="numberOfGuest"
                                        value="<?php echo htmlspecialchars($booking['numberOfGuest']); ?>"
                                        placeholder="Guests" style="font-family: arial, sans-serif;">
                                </td>
                                <td>
                                    <textarea name="message"
                                        placeholder="Message"><?php echo htmlspecialchars($booking['message']); ?></textarea>
                                </td>
                                <td>
                                    <select name="status" id="status" class="status-selection">
                                        <option class="new" value="new"
                                            <?php echo ($booking['status'] == "new") ? "selected" : "" ?>>New
                                        </option>
                                        <option class="confirmed" value="confirmed"
                                            <?php echo ($booking['status'] == "confirmed") ? "selected" : "" ?>>
                                            Confirmed
                                        </option>
                                        <option class="checked-in" value="checkedIn"
                                            <?php echo ($booking['status'] == "checkedIn") ? "selected" : "" ?>>
                                            Checked In</option>
                                        <option class="checked-out" value="checkedOut"
                                            <?php echo ($booking['status'] == "checkedOut") ? "selected" : "" ?>>
                                            Checked Out</option>
                                        <option class="cancelled" value="cancelled"
                                            <?php echo ($booking['status'] == "cancelled") ? "selected" : "" ?>>
                                            Cancelled</option>
                                        <option class="archived" value="archived"
                                            <?php echo ($booking['status'] == "archived") ? "selected" : "" ?>>
                                            Archived</option>
                                    </select>
                                </td>
                                <td><button type="submit" name="action" value="update">Update</button></td>
                                <td>
                                    <button type="submit" name="action" value="delete"
                                        class="delete-button">Delete</button>
                                </td>
                            </form>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="pageSlider"></div>
            <a class="logout" href="admin/logout.php">Log Out</a>
        </div>
    </main>

    <footer>
        <p>
            <a href="https://www.google.com/maps?q=50+Melaleuca+Cres,+Tascott+NSW+2250" target="_blank">
                50 Melaleuca Cres, Tascott NSW 2250
            </a>
        </p>
        <p>© 2025 Copyright Sunny Spot Holidays</p>
        <a id="login" href="admin/login.php">Admin</a>
        <img src="images/author.png" alt="author" class="author">
    </footer>
</body>

</html>
