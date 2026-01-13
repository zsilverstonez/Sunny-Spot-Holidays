<?php
session_start();
// Generate random csrf token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// Include connection to database
include 'database_connect.php';
// Load users from database for display
$users = [];
$result = $connect->query("SELECT * FROM account");
// Load each row of users
while ($row = $result->fetch_assoc()) $users[] = $row;
// Declare empty variable
$message = "";
$messageErr = "";
// Declare upload photo correctly
$uploadPhoto = true;
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $messageErr = "Security validation failed. Please try again.";
    } else {
        if (isset($_POST['staffID'])) {
            $valid = true;
            foreach ($_POST['staffID'] as $index => $staffID) {
                $staffID = (int)$staffID;
                $action = $_POST['action'][$index] ?? '';
                $username = $_POST['userName'][$index] ?? '';
                $password = $_POST['password'][$index] ?? '';
                $first_name = $_POST['firstName'][$index] ?? '';
                $last_name = $_POST['lastName'][$index] ?? '';
                $address = $_POST['address'][$index] ?? '';
                $phone = $_POST['phone'][$index] ?? '';
                $mobile = $_POST['mobile'][$index] ?? '';
                // Phone and mobile validation
                if (strlen($phone) != 8 || strlen($mobile) != 8) {
                    $messageErr = "Phone and mobile number must be 8 digits.";
                    $valid = false;
                    break;
                }
                if ($valid) {
                    if ($action === 'insert') {
                        // Handle photo upload
                        if (isset($_FILES['userPhoto']) && $_FILES['userPhoto']['error'] === 0) {
                            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                            $fileType = $_FILES['userPhoto']['type'];
                            $fileSize = $_FILES['userPhoto']['size'];

                            if (!in_array($fileType, $allowedTypes)) {
                                $messageErr = "Add new user unsuccessful!<br>Photo must be JPG, JPEG, or PNG.";
                                $uploadPhoto = false;
                            } elseif ($fileSize > 2 * 1024 * 1024) { // 2MB
                                $messageErr = "Add new user unsuccessful!<br>Photo size must be less than 2MB.";
                                $uploadPhoto = false;
                            } else {
                                $photo = basename($_FILES['userPhoto']['name']);
                                move_uploaded_file($_FILES['userPhoto']['tmp_name'], "staffPhoto/" . $photo);
                            }
                        } else {
                            $photo = $_POST['userPhoto_existing'][$index] ?? '';
                        }
                        if ($uploadPhoto) {
                            // Hash the password before storing
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            // Connect to database and prepare statement 
                            $accountTable = $connect->prepare(
                                "INSERT INTO account (userName, password, firstName, lastName, address, phone, mobile, userPhoto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                            );
                            // Declare variable types
                            $accountTable->bind_param("ssssssss", $username, $password, $first_name, $last_name, $address, $phone, $mobile, $photo);
                            // Execute getting accounts data
                            $accountTable->execute();
                            // Close getting accounts data 
                            $accountTable->close();
                            // Successful adding accounts
                            $message = "New user added successfully!";
                        }
                    } elseif ($action === 'update') {
                        // Handle photo upload
                        if (isset($_FILES['userPhoto']['name'][$index]) && $_FILES['userPhoto']['error'][$index] === 0) {
                            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                            $fileType = $_FILES['userPhoto']['type'][$index];
                            $fileSize = $_FILES['userPhoto']['size'][$index];

                            if (!in_array($fileType, $allowedTypes)) {
                                $messageErr = "Update user unsuccessful!<br>Photo must be JPG, JPEG, or PNG.";
                                $uploadPhoto = false;
                            } elseif ($fileSize > 2 * 1024 * 1024) { // 2MB
                                $messageErr = "Update user unsuccessful!<br>Photo size must be less than 2MB.";
                                $uploadPhoto = false;
                            } else {
                                $photo = basename($_FILES['userPhoto']['name'][$index]);
                                move_uploaded_file($_FILES['userPhoto']['tmp_name'][$index], "staffPhoto/" . $photo);
                            }
                        } else {
                            $photo = $_POST['userPhoto_existing'][$index] ?? '';
                        }
                        if ($uploadPhoto) {
                            if (!empty($password)) {
                                // Hash the password before storing
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $accountTable = $connect->prepare(
                                    // Connect to database and prepare statement 
                                    "UPDATE account SET  userName=?, password=?, firstName=?, lastName=?, address=?, phone=?, mobile=?, userPhoto=? WHERE staffID=?"
                                );
                                // Declare variable types
                                $accountTable->bind_param("ssssssssi", $username, $hashed_password, $first_name, $last_name, $address, $phone, $mobile, $photo, $staffID);
                                // Execute getting accounts data
                                $accountTable->execute();
                                // Close getting accounts data 
                                $accountTable->close();
                                // Successful updating accounts
                                $message = "User password reset successfully!";
                            } else {
                                // Hash the password before storing
                                $accountTable = $connect->prepare(
                                    // Connect to database and prepare statement 
                                    "UPDATE account SET  userName=?, firstName=?, lastName=?, address=?, phone=?, mobile=?, userPhoto=? WHERE staffID=?"
                                );
                                // Declare variable types
                                $accountTable->bind_param("sssssssi", $username, $first_name, $last_name, $address, $phone, $mobile, $photo, $staffID);
                                // Execute getting accounts data
                                $accountTable->execute();
                                // Close getting accounts data 
                                $accountTable->close();
                                // Successful updating accounts
                                $message = "User updated successfully!";
                            }
                        } elseif ($action === 'delete') {
                            // Connect to database and prepare statement 
                            $accountTable = $connect->prepare("DELETE FROM account WHERE staffID=?");
                            // Declare variable types
                            $accountTable->bind_param("i", $staffID);
                            // Execute getting cabins data
                            $accountTable->execute();
                            // Close getting accounts data 
                            $accountTable->close();
                            // Successful deleting accounts
                            $message = "User deleted successfully!";
                        }
                    }
                }
                // Reload users
                $users = [];
                $result = $connect->query("SELECT * FROM account");
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
            }
        }
    }
}


$lastUserId = !empty($users) ? end($users)['staffID'] : 0;
$connect->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunny Spot Holidays - Admin Dashboard - Account</title>
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
        .user-header {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .user-selection {
            width: 400px;
            height: 50px;
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 6px;
            font-size: 1rem;
            padding: 0 0.5rem;
        }

        .user-details {
            width: 100%;
            max-width: 1400px;
            padding: 1rem;
            margin: 7rem 1rem 3rem 1rem;
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            background-color: white;
            font-family: roboto, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        h2 {
            text-align: center;
        }

        .user-display,
        .new-user {
            width: 100%;
            max-width: 900px;
            margin: 1rem 0;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .user-display {
            display: none;
        }

        .form-divider {
            display: flex;
            margin: 1rem 0;
            width: 100%;
            border: 1px solid #ccc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 6px;
            padding: 0.4rem 1rem;
            justify-content: center;
            align-items: center;
        }

        .form-divider label {
            font-weight: bold;
            width: 30%;
        }

        .form-divider input {
            width: 100%;
            font-size: 1.1rem;
            border: none;
            text-align: center;
            padding: 0.3rem 0;
        }

        .form-divider .photo-container {
            width: 70%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
        }

        textarea {
            width: 100%;
            padding: 0.5rem 0.3rem;
            text-align: center;
        }

        .button-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin: 1rem 0;
        }

        button {
            display: flex;
            width: 140px;
            height: 40px;
            padding: 0 0.5rem;
            border-radius: 10px;
            border: none;
            text-align: center;
            justify-content: center;
            align-items: center;
            background-color: rgba(255, 115, 0, 0.77);
            color: white;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background-color: rgba(255, 115, 0, 1);
        }

        .delete-user-button {
            background-color: rgba(0, 0, 0, 0.7);
        }

        .delete-user-button:hover {
            background-color: rgba(0, 0, 0, 1);
        }

        .add-user-button {
            background-color: rgba(37, 170, 4, 0.77);
            width: 165px;
            min-height: 40px;
            margin-top: 1rem;
        }

        .add-user-button:hover {
            background-color: rgba(24, 110, 3, 0.77);
        }

        .update-user-button {
            margin-top: 0.5rem;
        }

        a.add-user {
            display: flex;
            width: 100%;
            max-width: 80px;
            height: 40px;
            padding: 0 0.5rem;
            margin: auto;
            border-radius: 10px;
            border: none;
            text-align: center;
            justify-content: center;
            align-items: center;
            background-color: rgba(37, 170, 4, 0.77);
            color: white;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
        }

        a.add-user:hover {
            background-color: rgba(24, 110, 3, 0.77);
        }

        .message {
            color: green;
            text-align: center;
            font-weight: bold;
            margin: 1rem;
            margin-bottom: -1rem;
        }

        .messageErr {
            color: red;
            text-align: center;
            font-weight: bold;
            margin: 1rem;
            margin-bottom: -1rem;
        }

        .logout {
            display: block;
            width: 100px;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: white;
            background-color: rgba(95, 62, 4, 1);
        }

        .logout:hover {
            background-color: rgba(70, 45, 3, 1);
        }

        @media (max-width: 568px) {

            .message,
            .messageErr {
                margin-top: 1rem;
            }



            .user-selection,
            .form-divider {
                width: 350px;
                color: black;
                margin-bottom: -1rem;
            }


            .form-divider {
                width: 350px;
                margin: 1rem;
                margin-left: -1.3rem;
            }
        }
    </style>
    <script src="script.js" defer></script>
    <script>
        let lastUserId = <?php echo $lastUserId ?>
        document.addEventListener('DOMContentLoaded', () => {
            const usernameInput = document.querySelectorAll(".username-input");
            const resetPasswordInput = document.querySelectorAll(".reset-password-input");
            const resetPasswordButton = document.querySelectorAll(".reset-password-button");
            resetPasswordButton.forEach((button, index) => {
                button.addEventListener('click', () => {
                    resetPasswordInput[index].value = usernameInput[index].value;
                });
            });
        })
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
                <li class="nav-account"><a href="admin_dashboard_account.php" class="active">Account</a></li>
                <li class="nav-log"><a href="admin_dashboard_log.php">Log</a></li>
            </ul>
            <div class="hamburger-menu"><span></span><span></span><span></span></div>
        </nav>
    </header>

    <main>
        <div class="user-details">
            <h2>Account Management</h2>
            <div class="user-header">
                <select id="user-selection" class="user-selection" name="user-selection" aria-label="User Selection">
                    <option value="" disabled selected>Select a user</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['staffID']; ?>">
                            <?php echo htmlspecialchars($user['userName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
            <?php if (!empty($messageErr)) echo "<p class='messageErr'>$messageErr</p>"; ?>
            <!-- Add New User -->
            <div class="new-user">
                <form method="POST" enctype="multipart/form-data"
                    onsubmit="return confirm('Are you sure about adding this user?');">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action[]" value="insert">
                    <input type="hidden" name="staffID[]" readonly>
                    <div class="form-divider"><label>Username:</label><input type="text" name="userName[]" required>
                    </div>
                    <div class="form-divider"><label>Password:</label><input type="password" name="password[]" required>
                    </div>
                    <div class="form-divider"><label>First Name:</label><input type="text" name="firstName[]" required>
                    </div>
                    <div class="form-divider"><label>Last Name:</label><input type="text" name="lastName[]" required>
                    </div>
                    <div class="form-divider"><label>Address:</label><input type="text" name="address[]"></div>
                    <div class="form-divider"><label>Phone:</label><input type="text" name="phone[]" required></div>
                    <div class="form-divider"><label>Mobile:</label><input type="text" name="mobile[]">
                    </div>
                    <div class="form-divider"><label>Photo:</label>
                        <div class="photo-container">
                            <input type="file" name="userPhoto">
                        </div>
                    </div>
                    <div class="button-wrapper"><button type="submit" class="add-user-button">Add New User</button>
                    </div>
                </form>
            </div>
            <!-- Existing Users -->
            <?php foreach ($users as $index => $user): ?>
                <div class="user-display" data-id="<?php echo $user['staffID']; ?>">
                    <form method="POST" onsubmit="return confirm('Are you sure about updating this user?');"
                        enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action[]" value="update">
                        <input type="hidden" name="staffID[]" value="<?php echo htmlspecialchars($user['staffID']); ?>"
                            readonly>
                        <div class="form-divider"><label>Username: </label>
                            <input type="text" name="username[]" value="<?php echo htmlspecialchars($user['username']); ?>"
                                class="username-input">
                        </div>
                        <div class="form-divider"><label>First Name: </label>
                            <input type="text" name="firstName[]"
                                value="<?php echo htmlspecialchars($user['firstName']); ?>">
                        </div>
                        <div class="form-divider"><label>Password: </label>
                            <input type="hidden" name="password[]" value="" class="reset-password-input">
                            <button type="button" class="reset-password-button" style="  width: 100%;
            font-size: 1.1rem;
            border: none;
            text-align: center;
            padding: 0.3rem 0;">Reset Password</button>
                        </div>
                        <div class="form-divider"><label>Last Name: </label>
                            <input type="text" name="lastName[]" value="<?php echo htmlspecialchars($user['lastName']); ?>">
                        </div>
                        <div class="form-divider"><label>Address: </label>
                            <input type="text" name="address[]" value="<?php echo htmlspecialchars($user['address']); ?>">
                        </div>
                        <div class="form-divider"><label>Phone: </label>
                            <input type="text" name="phone[]" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="form-divider"><label>Mobile: </label>
                            <input type="text" name="mobile[]" value="<?php echo htmlspecialchars($user['mobile']); ?>">
                        </div>
                        <div class="form-divider"><label>Photo: </label>
                            <div class="photo-container">
                                <?php if (!empty($user['userPhoto'])): ?>
                                    <img src="staffPhoto/<?php echo htmlspecialchars($user['userPhoto']); ?>" alt="Staff Photo"
                                        width="150px" height="100px">
                                <?php endif; ?>
                                <input type="file" name="userPhoto[]">
                                <input type="hidden" name="userPhoto_existing[]"
                                    value="<?php echo htmlspecialchars($user['userPhoto']); ?>">
                            </div>
                        </div>
                        <div class="button-wrapper">
                            <button type="submit" class="update-user-button">Update User</button>
                        </div>
                    </form>
                    <a class="add-user" href="admin_dashboard_account.php">Back</a>
                    <form method="POST" onsubmit="return confirm('Are you sure about deleting this user?');">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="staffID[]" value="<?php echo htmlspecialchars($user['staffID']); ?>">
                        <input type="hidden" name="action[]" value="delete">
                        <div class="button-wrapper">
                            <button type="submit" class="delete-user-button">Delete User</button>
                        </div>
                    </form>

                </div>
            <?php endforeach; ?>

            <a class="logout" href="logout.php">Log Out</a>
        </div>
    </main>

    <footer>
        <p><a href="https://www.google.com/maps?q=50+Melaleuca+Cres,+Tascott+NSW+2250" target="_blank">50 Melaleuca
                Cres, Tascott NSW 2250</a></p>
        <p>© 2025 Copyright Sunny Spot Holidays</p>
        <li id="login"><a href="login.php">Admin</a></li>
        <img src="images/author.png" alt="author" class="author">
    </footer>
</body>

</html>