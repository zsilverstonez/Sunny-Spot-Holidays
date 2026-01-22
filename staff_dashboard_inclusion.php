<?php
session_start();
// Generate random csrf token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include("database_connect.php");

$inclusions = [];
$result = $connect->query("SELECT * FROM inclusion");
while ($row = $result->fetch_assoc()) {
    $inclusions[] = $row;
};

$message = "";
$messageErr = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $messageErr = "Security validation failed. Please try again.";
    } else {
        $action = $_POST['action'] ?? '';
        $id = $_POST['incID'] ?? null;
        $name = $_POST['incName'] ?? '';
        $detail = $_POST['incDetails'] ?? '';
        $action = $_POST['action'] ?? '';
        // Update form
        if ($action === 'update' && $id > 0) {
            $incTable = $connect->prepare("UPDATE inclusion SET incName=?, incDetails=? WHERE incID=?");
            $incTable->bind_param("ssi", $name, $detail, $id);
            $incTable->execute();
            $incTable->close();
            $message = "Inclusion updated successfully!";
        }
        // Delete form
        elseif ($action === 'delete' && $id > 0) {
            $incTable = $connect->prepare("DELETE FROM inclusion WHERE incID=?");
            $incTable->bind_param("i", $id);
            $incTable->execute();
            $incTable->close();
            $message = "Inclusion deleted successfully!";
        }
        // Add new inclusion
        elseif ($action === "insert") {
            $name = trim($_POST['incName'] ?? '');
            $detail = trim($_POST['incDetails'] ?? '');

            if (empty($name)) {
                $messageErr = "Inclusion name is required!";
            } else {
                // Check if a record with the same name already exists
                $incCheck = $connect->prepare("SELECT COUNT(*) FROM inclusion WHERE incName = ?");
                $incCheck->bind_param("s", $name);
                $incCheck->execute();
                $incCheck->bind_result($count);
                $incCheck->fetch();
                $incCheck->close();

                if ($count > 0) {
                    $messageErr = "Inclusion already exists!";
                } else {
                    // Insert new inclusion
                    $incTable = $connect->prepare("INSERT INTO inclusion (incName, incDetails) VALUES (?, ?)");
                    $incTable->bind_param("ss", $name, $detail);
                    $incTable->execute();
                    $incTable->close();
                    $message = "New inclusion added successfully!";
                }
            }
        }
    }
}
// Reload inclusion
$inclusions = [];
$result = $connect->query("SELECT * FROM inclusion");
while ($row = $result->fetch_assoc()) {
    $inclusions[] = $row;
}

$connect->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunny Spot Holidays- Staff Dashboard - Inclusion</title>
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
    .inclusion-details {
        width: 100%;
        height: 100vh;
        padding: 1rem;
        margin: 8rem auto;
        border: 1px solid #ccc;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        background-color: white;
        font-family: roboto, sans-serif;
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
        width: 50px;
    }

    th:nth-child(4),
    th:last-child {
        width: 80px;
    }

    th:nth-child(2) {
        width: 400px;
    }

    input,
    button {
        width: 100%;
        height: auto;
        padding: 0.6rem 0;
        font-size: 1rem;
        border: none;
        text-align: center;
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

    .message,
    .messageErr {
        color: green;
        text-align: center;
        font-weight: bold;
        margin-top: -1rem;
    }

    .messageErr {
        color: red;
    }

    .new-inclusion {
        width: 600px;
        margin: 0 auto;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .wrapper {
        width: 100%;
        border: 1px solid #ccc;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        margin: 0.5rem;
        padding: 0.5rem;
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
    }

    .wrapper label {
        font-weight: bold;
    }

    .wrapper textarea {
        border: none;
        width: 100%;
        text-align: center;
        font-size: 1rem;
    }

    .insert-button {
        display: block;
        width: 100px;
        margin: 0 auto;
        border-radius: 10px;
    }

    #pageSlider {
        display: block;
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

    @media (max-width:1100px) {
        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        .inclusion-details table {
            border-collapse: collapse;
            min-width: 1100px;
            table-layout: auto;
        }

        .wrapper {
            width: 360px;
        }

        th,
        td {
            border: 1px solid #ccc;
            text-align: center;
        }

        input,
        textarea,
        button {
            height: 40px;
            padding: 0.2rem;
            font-size: 1rem;
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
                <li class="nav-cabin"><a href="staff_dashboard_cabin">Cabin</a></li>
                <li class="nav-inclusion"><a href="staff_dashboard_inclusion" class="active">Inclusion</a></li>
            </ul>
            <div class="hamburger-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    <main>
        <div class="inclusion-details">
            <h2>Inclusion Details</h2>
            <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <?php if (!empty($messageErr)): ?>
            <p class="messageErr"><?php echo htmlspecialchars($messageErr); ?></p>
            <?php endif; ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Details</th>
                            <th>Action</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inclusions as $i => $inclusion): ?>
                        <tr>
                            <form method="POST" onsubmit="return confirm('Are you sure about the change?');">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <td>
                                    <input type="hidden" name="incID"
                                        value="<?php echo htmlspecialchars($inclusion['incID']); ?>" readonly>
                                    <?php echo htmlspecialchars($inclusion['incID']); ?>
                                </td>
                                <td>
                                    <input type="text" name="incName"
                                        value="<?php echo htmlspecialchars($inclusion['incName']); ?>">
                                </td>
                                <td>
                                    <input type="text" name="incDetails"
                                        value="<?php echo htmlspecialchars($inclusion['incDetails']); ?>">
                                </td>
                                <td>
                                    <button type="submit" name="action" value="update">Update</button>
                                </td>
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
            <br>
            <h3 style="width: 100%; text-align: center; color: black; margin-bottom: -0.5rem;">Add New Inclusion</h3>
            <form method="POST" onsubmit="return confirm('Are you sure about the change?');">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="new-inclusion">
                    <div class="wrapper">
                        <label>Name:</label>
                        <input type="text" name="incName" style="padding: 0 0.2rem;" required>
                    </div>
                    <div class="wrapper">
                        <label>Details:</label>
                        <textarea name="incDetails" rows="3"></textarea>
                    </div>
                </div>
                <button type="submit" name="action" value="insert" class="insert-button">Insert</button>
            </form>
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
