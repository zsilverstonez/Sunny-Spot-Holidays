<?php
session_start();

// Add security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Generate random csrf token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize login attempt tracking
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_time'] = 0;
}

// Include connection to database
include '../database_connect.php';

// Declare empty error message
$message = "";
$passResetMessage = "";

// Declare current timezone
date_default_timezone_set('Australia/Sydney');

// Check if account is locked out
$lockout_duration = 900; // 15 minutes in seconds
$current_time = time();

if ($_SESSION['lockout_time'] > 0 && ($current_time - $_SESSION['lockout_time']) < $lockout_duration) {
    $remaining_time = $lockout_duration - ($current_time - $_SESSION['lockout_time']);
    $minutes = ceil($remaining_time / 60);
    $message = "Too many failed login attempts. Please try again in {$minutes} minute(s).";
} elseif ($_SESSION['lockout_time'] > 0 && ($current_time - $_SESSION['lockout_time']) >= $lockout_duration) {
    // Reset after lockout period expires
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_time'] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['lockout_time'] == 0) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Security validation failed. Please try again.";
    } else {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        
        // Connect to database and prepare statement to check username and password
        $loginDetails = $connect->prepare("SELECT staffID, userName, password FROM account WHERE userName = ?");
        // Define types of variables
        $loginDetails->bind_param("s", $username);
        // Execute getting login data
        $loginDetails->execute();
        $result = $loginDetails->get_result();
        
        // Check validation and link to correct php file
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user["password"])) {
                // Successful login - reset attempts
                $_SESSION['login_attempts'] = 0;
                $_SESSION['lockout_time'] = 0;
                
                session_regenerate_id(true);
                // Store variables
                $_SESSION["logged_in"] = true;
                $_SESSION["username"] = $user["userName"];
                $_SESSION["staffID"] = $user["staffID"];
                $staffID = $user["staffID"];
                $log_in = date("Y-m-d H:i:s");
                $log_out = NULL;
                
                // Connect to database and insert to log
                $logsTable = $connect->prepare("INSERT INTO log (staffID, username, loginDateTime, logoutDateTime) VALUES (?, ?, ?, ?)");
                // Declare variable types
                $logsTable->bind_param("isss", $staffID, $_SESSION["username"], $log_in, $log_out);
                // Execute getting logs data
                $logsTable->execute();
                // Close getting logs data 
                $logsTable->close();
                
                // Set user role
                $_SESSION["role"] = ($user["staffID"] == 1) ? "admin" : "staff";
                
                // Head user to correct dashboard based on their position
                $redirect = ($_SESSION["role"] === "admin") 
                    ? "../admin_dashboard_booking.php"
                    : "../staff_dashboard_booking.php";
                header("Location: $redirect");
                exit;
            } else {
                // Failed login - increment attempts
                $_SESSION['login_attempts']++;
                
                if ($_SESSION['login_attempts'] >= 3) {
                    $_SESSION['lockout_time'] = time();
                    $message = "Too many failed login attempts. Your account has been locked for 15 minutes.";
                } else {
                    $remaining_attempts = 3 - $_SESSION['login_attempts'];
                    $message = "Incorrect Username or Password. {$remaining_attempts} attempt(s) remaining.";
                    $passResetMessage = "Forgot password";
                }
            }
        } else {
            // Failed login - increment attempts
            $_SESSION['login_attempts']++;
            
            if ($_SESSION['login_attempts'] >= 3) {
                $_SESSION['lockout_time'] = time();
                $message = "Too many failed login attempts. Your account has been locked for 15 minutes.";
            } else {
                $remaining_attempts = 3 - $_SESSION['login_attempts'];
                $message = "Incorrect Username or Password. {$remaining_attempts} attempt(s) remaining.";
                $passResetMessage = "Forgot password";
            }
        }
        
        // Close getting login data
        $loginDetails->close();
    }
}

// Close connection to database
$connect->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunny Spot Holidays - Log In</title>
    <link href="../styles.css?v=<?php echo time(); ?>" rel="stylesheet">
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
    .main-admin {
        padding-bottom: 4rem;
        padding-top: 2rem;
    }

    article {
        width: 100%;
        margin-top: 4rem;
        max-width: 550px;
        padding: 1rem;
    }

    .main-admin h2 {
        display: flex;
        justify-content: center;
        margin-top: 0;
        margin-bottom: -0.2rem;
    }

    fieldset {
        display: flex;
        width: 100%;
        flex-direction: column;
        justify-content: center;
        gap: 0.2rem;
        border: none;
    }

    input,
    button {
        padding: 0.5rem;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
        margin: 0.25rem;
    }

    button {
        background-color: rgba(219, 103, 8, 0.842);
        color: white;
        cursor: pointer;
    }

    button:hover {
        background-color: rgba(219, 103, 8, 1);
    }

    button:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    .error {
        text-align: center;
        font-style: italic;
        padding: 0;
        margin: 0;
        color: rgba(219, 103, 8, 0.842);
    }

    .reset-password {
        font-size: 12px;
        font-family: roboto, sans-serif;
        font-style: italic;
        text-decoration: underline;
        color: blue;
        margin-left: 0.5rem;
        margin-bottom: 0.5rem;
    }
    </style>
</head>

<body>
    <header>
        <div class="header-divider">
            <a href="../index.php"><img src="../images/sun.gif" alt="Sunny-logo" class="sunny-logo"></a>
            <div class="title-divider">
                <a href="../index.php" class="title">
                    <h1>Sunny Spot Holidays</h1>
                </a>
                <h3>This is a mock website only!</h3>
            </div>
        </div>
    </header>
    <main class="main-admin">
        <article>
            <h2>Login</h2>
            <?php if (!empty($message)): ?>
            <p class="error" style="font-size: 14px;"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <form method="post" action="">
                <fieldset class="login">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="text" name="username" id="username" placeholder="Enter Username" required
                        <?php echo ($_SESSION['lockout_time'] > 0) ? 'disabled' : ''; ?>>
                    <input type="password" name="password" id="password" placeholder="Enter Password" required
                        <?php echo ($_SESSION['lockout_time'] > 0) ? 'disabled' : ''; ?>>
                    <?php if (!empty($passResetMessage)): ?>
                    <a class="reset-password" href="email.php"><?php echo $passResetMessage; ?></a>
                    <?php endif; ?>
                    <button type="submit"
                        <?php echo ($_SESSION['lockout_time'] > 0) ? 'disabled' : ''; ?>>Login</button>
                </fieldset>
            </form>
        </article>
    </main>
    <footer>
        <p>
            <a href="https://www.google.com/maps?q=50+Melaleuca+Cres,+Tascott+NSW+2250" target="_blank">
                50 Melaleuca Cres, Tascott NSW 2250
            </a>
        </p>
        <p>© 2025 Copyright Sunny Spot Holidays</p>
        <img src="../images/author.png" alt="author" class="author">
    </footer>
    <script src="../script.js"></script>
</body>

</html>