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
    $dateCondition = " AND DATE(submitted_at) = '" . $connect->real_escape_string($filterDay) . "'";
} elseif (!empty($filterMonth)) {
    $dateCondition = " AND DATE_FORMAT(submitted_at, '%Y-%m') = '" . $connect->real_escape_string($filterMonth) . "'";
}

// Load contacts from database
$contacts = [];
$result = $connect->query("SELECT * FROM contact WHERE 1=1" . $dateCondition . " ORDER BY submitted_at DESC");
while ($row = $result->fetch_assoc()) $contacts[] = $row;
// Declare empty variable
$message = "";
$messageErr = "";
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $messageErr = "Security validation failed. Please try again.";
    } else {
        $id = (int)$_POST['id'];
        $action = $_POST['action'];
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $msg = $_POST['message'];
        $status = $_POST['status'];
        // Update form
        if ($action === 'update') {
            if ($id > 0) {
                $contactTable = $connect->prepare("UPDATE contact SET name=?, phone=?, email=?, message=?, status=? WHERE id=?");
                // Declare variable types
                $contactTable->bind_param("sssssi", $name, $phone, $email, $msg, $status, $id);
                // Execute getting contacts data
                $contactTable->execute();
                // Close getting contacts data 
                $contactTable->close();
                // Successful updating message
                $message = "Contact updated successfully!";
            }
        }
        // Delete form
        elseif ($action === 'delete') {
            $contactTable = $connect->prepare("DELETE FROM contact WHERE id=?");
            // Declare variable types
            $contactTable->bind_param("i", $id);
            // Execute getting contacts data
            $contactTable->execute();
            // Close getting contacts data 
            $contactTable->close();
            // Successful deleting message
            $message = "Contact deleted successfully!";
        }

        // Reload contacts
        $contacts = [];
        $result = $connect->query("SELECT * FROM contact WHERE 1=1" . $dateCondition . " ORDER BY submitted_at DESC");
        while ($row = $result->fetch_assoc()) {
            $contacts[] = $row;
        }
    }
}
$connect->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunny Spot Holidays - Staff Dashboard - Contact</title>
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
        .contact-details {
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

        th:first-child {
            width: 200px;
        }

        th:nth-child(2) {
            width: 70px;
        }

        th:last-child,
        th:nth-child(7) {
            width: 40px;
        }

        th:nth-child(3) {
            width: 200px;
        }

        th:nth-child(4) {
            width: 280px;
        }

        th:nth-child(5) {
            width: 110px;
        }

        th:nth-child(6) {
            width: 60px;
        }

        input,
        button {
            width: 100%;
            height: 100%;
            padding: 0.2rem;
            font-size: 1rem;
            border: none;
            text-align: center;
        }

        textarea {
            border: none;
            width: 100%;
            text-align: center;
        }

        .status-selection option[value="new"] {
            color: red;
        }

        .status-selection option[value="processing"] {
            color: orange;
            ;
        }

        .status-selection option[value="finalised"] {
            color: green;
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
            margin: -1rem 0 1rem 0;
        }

        .messageErr {
            color: red;
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

        @media (max-width:1600px) {
            .table-container {
                width: 100%;
                overflow-x: auto;
            }

            .contact-details table {
                border-collapse: collapse;
                min-width: 1700px;
                table-layout: auto;
            }

            th,
            td {
                border: 1px solid #ccc;
                text-align: center;
            }

            input,
            textarea,
            button {
                height: 50px;
                padding: 0.2rem;
                font-size: 1rem;
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

        // Set colors for status
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(".status-selection").forEach(select => {
                const updateColor = () => {
                    if (select.value === "new") select.style.color = "red";
                    else if (select.value === "processing") select.style.color = "orange";
                    else if (select.value === "finalised") select.style.color = "green";
                    else select.style.color = "black";
                };
                updateColor(); // updating color
                select.addEventListener("change", updateColor);
            });
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
                <li class="nav-booking"><a href="staff_dashboard_booking.php">Booking</a></li>
                <li class="nav-availability"><a href="staff_dashboard_availability.php">Availability</a>
                </li>
                <li class="nav-contact"><a href="staff_dashboard_contact.php" class="active">Contact</a></li>
                <li class="nav-cabin"><a href="staff_dashboard_cabin.php">Cabin</a></li>
                <li class="nav-inclusion"><a href="staff_dashboard_inclusion.php">Inclusion</a></li>
            </ul>
            <div class="hamburger-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    <main>
        <div class="contact-details">
            <h2>Contact Details</h2>
            <?php if (!empty($message)): ?>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
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
                    <div class="filter-buttons">
                        <button type="submit" class="filter-button">Apply Filter</button>
                        <a href="staff_dashboard_contact.php" class="clear-filter">Clear Filter</a>
                    </div>
                </div>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                            <th>Action</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $i => $contact): ?>
                            <tr>
                                <form method="POST" onsubmit="return confirm('Are you sure about the change?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($contact['id']); ?>">
                                    <td><input type="text" name="name"
                                            value="<?php echo htmlspecialchars($contact['name']); ?>"
                                            placeholder="Full Name">
                                    </td>
                                    <td><input type="tel" name="phone"
                                            value="<?php echo htmlspecialchars($contact['phone']); ?>" placeholder="Phone"
                                            style="font-family: arial, sans-serif;" pattern="\d{9,11}" required
                                            oninvalid="this.setCustomValidity('Please enter a valid phone number')"
                                            oninput="this.setCustomValidity('')">
                                    </td>
                                    <td><input type="email" name="email"
                                            value="<?php echo htmlspecialchars($contact['email']); ?>" placeholder="Email"
                                            oninvalid="this.setCustomValidity('Please enter a valid email')"
                                            oninput="this.setCustomValidity('')">
                                    </td>
                                    <td>
                                        <textarea name="message" placeholder="Message"
                                            style="font-size: 1rem;"><?php echo htmlspecialchars($contact['message']); ?></textarea>
                                    </td>
                                    <td>
                                        <?php $date = date("H:m:s \\o\\n d-m-Y", strtotime($contact['submitted_at'])); ?>
                                        <input name="submitted_at[]" value="<?php echo htmlspecialchars($date); ?>"
                                            style="font-family: arial, sans-serif;" readonly>
                                    </td>
                                    <td>
                                        <select name="status"
                                            style="border:none; display: flex; justify-content:center; width: 100%; margin: 0; text-align: center; font-size: 1rem; padding: 0.3rem 0;"
                                            class="status-selection">
                                            <option class="new" value="new"
                                                <?php echo ($contact['status'] == "new") ? "selected" : "" ?>>New
                                            </option>
                                            <option class="processing" value="processing"
                                                <?php echo ($contact['status'] == "processing") ? "selected" : "" ?>>
                                                Processing</option>
                                            <option class="finalised" value="finalised"
                                                <?php echo ($contact['status'] == "finalised") ? "selected" : "" ?>>
                                                Finalised</option>
                                        </select>
                                    </td>
                                    <td><button type="submit" name="action" value="update"
                                            style="padding:1rem 0.2rem;">Update</button></td>
                                    <td>
                                        <button type="submit" name="action" value="delete" class="delete-button"
                                            style="padding:1rem 0.2rem;">Delete</button>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="pageSlider"></div>
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
