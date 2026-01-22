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

// Build date filter condition
$dateCondition = "";
if (!empty($filterDay)) {
    $dateCondition = " WHERE DATE(loginDateTime) = '" . $connect->real_escape_string($filterDay) . "'";
} elseif (!empty($filterMonth)) {
    $dateCondition = " WHERE DATE_FORMAT(loginDateTime, '%Y-%m') = '" . $connect->real_escape_string($filterMonth) . "'";
}

// Load cabins from database for display
$logs = [];
$result = $connect->query("SELECT * FROM log" . $dateCondition . " ORDER BY loginDateTime DESC");
// Get each data pair of rows
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}
// Close connection
$connect->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunny Spot Holidays- Admin Dashboard - Log</title>
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
    .log-details {
        width: 100%;
        height: 80vh;
        padding: 1rem;
        margin: 6.5rem 2rem 2rem 2rem;
        border: 1px solid #ccc;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        background-color: white;
        font-family: roboto, sans-serif;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
        position: relative;
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

    th:first-child {
        width: 80px;
    }

    input {
        width: 100%;
        height: 100%;
        padding: 0.6rem 0;
        font-size: 1rem;
        border: none;
        text-align: center;
        font-family: roboto, sans-serif;
    }

    .message {
        color: green;
        text-align: center;
        font-weight: bold;
        margin: 1rem;
    }

    #pageSlider {
        text-align: center;
        margin-top: 20px;
        position: absolute;
        bottom: 110px;
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
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
        position: absolute;
        bottom: 15px;
    }

    .logout:hover {
        background-color: rgba(70, 45, 3, 1);
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

    @media (max-width: 1100px) {

        .log-details {
            padding: 1rem;
            margin: 5.5rem 2rem 1rem 2rem;
            height: auto;
            min-height: 80vh;
            padding-bottom: 150px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        .log-details table {
            border-collapse: collapse;
            min-width: 800px;
            table-layout: auto;
        }

        th,
        td {
            border: 1px solid #ccc;
            text-align: center;
        }

        input {
            width: 100%;
            height: 50px;
            padding: 0.2rem;
            font-size: 1rem;
            box-sizing: border-box;
            text-align: center;
        }

        #pageSlider {
            bottom: 90px;
        }

        .logout {
            bottom: 5px;
        }

        .filter-container input {
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
                <li class="nav-booking"><a href="admin_dashboard_booking.php">Booking</a></li>
                <li class="nav-availability"><a href="admin_dashboard_availability.php">Availability</a>
                </li>
                <li class="nav-contact"><a href="admin_dashboard_contact.php">Contact</a></li>
                <li class="nav-cabin"><a href="admin_dashboard_cabin.php">Cabin</a></li>
                <li class="nav-inclusion"><a href="admin_dashboard_inclusion.php">Inclusion</a></li>
                <li class="nav-account"><a href="admin_dashboard_account.php">Account</a></li>
                <li class="nav-log"><a href="admin_dashboard_log.php" class="active">Log</a></li>
            </ul>
            <div class="hamburger-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    <main>
        <div class="log-details">
            <h2>Log Details</h2>
            <?php if (!empty($mainMessage)): ?>
            <p class="message"><?php echo htmlspecialchars($mainMessage); ?></p>
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
                    <div class="filter-buttons">
                        <button type="submit" class="filter-button">Apply Filter</button>
                        <a href="admin_dashboard_log.php" class="clear-filter">Clear Filter</a>
                    </div>
                </div>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>Username</th>
                            <th>Log In</th>
                            <th>Log Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $i => $log): ?>
                        <tr>
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="logID" value="<?php echo htmlspecialchars($log['logID']); ?>">
                            <td><input type="text" name="staffID"
                                    value="<?php echo htmlspecialchars($log['staffID']); ?>" readonly>
                            </td>
                            <td><input type="text" name="username"
                                    value="<?php echo htmlspecialchars($log['username']); ?>" readonly></td>
                            <!-- Convert dateTime -->
                            <?php
                                $login_time = date("H:i:s \\o\\n d-m-Y", strtotime($log['loginDateTime']));
                                // Display online if the user is online
                                // Use date to reformat the display of date, strtotime to convert a string to a timestamp
                                $logout_time = (!empty($log['logoutDateTime']) && $log['logoutDateTime'] !== "0000-00-00 00:00:00") ? date("H:i:s \\o\\n d-m-Y", strtotime($log['logoutDateTime'])) : 'Online';
                                ?>
                            <td><input type="text" name="loginDateTime"
                                    value="<?php echo htmlspecialchars($login_time); ?>" readonly>
                            </td>
                            <td><input type="text" name="logoutDateTime"
                                    value="<?php echo htmlspecialchars($logout_time); ?>" readonly>
                            </td>
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
